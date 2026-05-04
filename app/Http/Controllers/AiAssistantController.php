<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\Ai\LlmChatService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    private const HISTORY_LIMIT = 20;

    public function chat(Request $request, LlmChatService $llm): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'mode' => ['required', 'string', 'in:inquiry,crud'],
        ]);

        $message = trim($validated['message']);
        $mode = $validated['mode'];
        $history = array_slice((array) $request->session()->get('ai_chat.history', []), -self::HISTORY_LIMIT);
        $context = (array) $request->session()->get('ai_chat.context', []);
        $pendingAction = (array) $request->session()->get('ai_chat.pending_action', []);

        if ($mode === 'crud' && $pendingAction !== []) {
            if ($this->isConfirmMessage($message)) {
                $result = $this->executePendingAction($request->user(), $pendingAction);
                $request->session()->forget('ai_chat.pending_action');
            } elseif ($this->isCancelMessage($message)) {
                $request->session()->forget('ai_chat.pending_action');
                $result = [
                    'operation' => 'cancel_pending_action',
                    'did_mutate' => false,
                    'fallback_reply' => 'Action cancelled. No changes were made.',
                    'skip_ai_reply' => true,
                ];
            } else {
                $summary = (string) ($pendingAction['summary'] ?? 'a pending destructive action');

                $result = [
                    'operation' => 'awaiting_confirmation',
                    'did_mutate' => false,
                    'needs_confirmation' => true,
                    'confirmation_summary' => $summary,
                    'fallback_reply' => "I still need confirmation before I proceed: {$summary}. Reply with Confirm or Cancel.",
                    'skip_ai_reply' => true,
                ];
            }

            if (isset($result['context_updates']) && is_array($result['context_updates'])) {
                $context = $this->mergeContext($context, $result['context_updates']);
                $request->session()->put('ai_chat.context', $context);
            }

            $reply = (string) $result['fallback_reply'];

            $updatedHistory = array_slice([
                ...$history,
                ['role' => 'user', 'content' => $message],
                ['role' => 'assistant', 'content' => $reply],
            ], -self::HISTORY_LIMIT);

            $request->session()->put('ai_chat.history', $updatedHistory);

            return response()->json([
                'reply' => $reply,
                'refreshTasks' => (bool) ($result['did_mutate'] ?? false),
                'operation' => $result['operation'] ?? 'unknown',
                'needsConfirmation' => (bool) ($result['needs_confirmation'] ?? false),
                'confirmationSummary' => $result['confirmation_summary'] ?? null,
            ]);
        }

        $intent = $this->fallbackIntent($mode, $message);

        try {
            $intent = $llm->classifyMessage($mode, $message, $history);
        } catch (\Throwable) {
            // Fall back to deterministic parsing for resilience.
        }

        $result = $mode === 'crud'
            ? $this->runCrudOperation($request->user(), $intent, $message, $context)
            : $this->runInquiryOperation($request->user(), $intent, $message, $context);

        if (isset($result['pending_action']) && is_array($result['pending_action'])) {
            $request->session()->put('ai_chat.pending_action', $result['pending_action']);
        }

        if (isset($result['context_updates']) && is_array($result['context_updates'])) {
            $context = $this->mergeContext($context, $result['context_updates']);
            $request->session()->put('ai_chat.context', $context);
        }

        $reply = $result['fallback_reply'];

        if (! ($result['skip_ai_reply'] ?? false)) {
            try {
                $reply = $llm->composeReply($mode, $message, $history, $result);
            } catch (\Throwable) {
                // Keep deterministic reply if the provider is unavailable.
            }
        }

        $updatedHistory = array_slice([
            ...$history,
            ['role' => 'user', 'content' => $message],
            ['role' => 'assistant', 'content' => $reply],
        ], -self::HISTORY_LIMIT);

        $request->session()->put('ai_chat.history', $updatedHistory);

        return response()->json([
            'reply' => $reply,
            'refreshTasks' => (bool) ($result['did_mutate'] ?? false),
            'operation' => $result['operation'] ?? 'unknown',
            'needsConfirmation' => (bool) ($result['needs_confirmation'] ?? false),
            'confirmationSummary' => $result['confirmation_summary'] ?? null,
        ]);
    }

    private function runInquiryOperation(User $user, array $intent, string $message, array $context): array
    {
        $operation = $intent['operation'] ?? 'unknown';
        $normalized = Str::lower($message);

        if ($operation === 'unknown') {
            $operation = $this->fallbackIntent('inquiry', $message)['operation'];
        }

        $followUpResult = $this->handleFollowUpInquiry($user, $message, $context);
        if ($followUpResult !== null) {
            return $followUpResult;
        }

        if ($operation === 'tasks_due_today') {
            $tasks = $this->activeTaskQuery($user)
                ->whereDate('due_date', now()->toDateString())
                ->orderBy('due_time')
                ->orderBy('title')
                ->limit(10)
                ->get();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'count' => $tasks->count(),
                'tasks' => $this->taskList($tasks),
                'fallback_reply' => $this->formatTaskListReply('Tasks due today', $tasks),
                'context_updates' => $this->buildTaskContext($tasks),
            ];
        }

        if ($operation === 'tasks_by_priority') {
            $priority = $this->normalizePriority($intent['priority'] ?? null)
                ?? (str_contains($normalized, 'high') ? 'High' : (str_contains($normalized, 'low') ? 'Low' : 'Medium'));

            $tasks = $this->activeTaskQuery($user)
                ->where('priority', $priority)
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'priority' => $priority,
                'count' => $tasks->count(),
                'tasks' => $this->taskList($tasks),
                'fallback_reply' => $this->formatTaskListReply("{$priority} priority tasks", $tasks),
                'context_updates' => [
                    ...$this->buildTaskContext($tasks),
                    'preferred_priority' => $priority,
                ],
            ];
        }

        if ($operation === 'count_completed') {
            $count = $this->activeTaskQuery($user)
                ->where('status', 'Completed')
                ->count();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'count' => $count,
                'fallback_reply' => "You currently have {$count} completed task(s).",
            ];
        }

        if ($operation === 'oldest_pending') {
            $task = $this->activeTaskQuery($user)
                ->whereIn('status', ['Not Started', 'In Progress'])
                ->orderBy('created_at')
                ->first();

            if (! $task) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'You have no pending tasks right now.',
                ];
            }

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'task' => $this->taskSummary($task),
                'fallback_reply' => "Your oldest pending task is \"{$task->title}\" ({$task->status}, due {$task->due_date->format('Y-m-d')}).",
                'context_updates' => $this->buildTaskContext(collect([$task])),
            ];
        }

        if ($operation === 'tasks_by_category') {
            $category = $this->resolveCategory($intent['category'] ?? null, $message);

            if (! $category) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not identify the category. Try: "List tasks in the Work category".',
                ];
            }

            $tasks = $this->activeTaskQuery($user)
                ->where('category_id', $category->id)
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'category' => $category->name,
                'count' => $tasks->count(),
                'tasks' => $this->taskList($tasks),
                'fallback_reply' => $this->formatTaskListReply("Tasks in {$category->name}", $tasks),
                'context_updates' => [
                    ...$this->buildTaskContext($tasks),
                    'last_category' => $category->name,
                ],
            ];
        }

        if ($operation === 'count_categories') {
            $count = Category::query()->count();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'count' => $count,
                'fallback_reply' => "There are {$count} categories available.",
            ];
        }

        if ($operation === 'list_tasks') {
            $tasks = $this->activeTaskQuery($user)
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            return [
                'operation' => $operation,
                'did_mutate' => false,
                'count' => $tasks->count(),
                'tasks' => $this->taskList($tasks),
                'fallback_reply' => $this->formatTaskListReply('Here are your latest tasks', $tasks),
                'context_updates' => $this->buildTaskContext($tasks),
            ];
        }

        return [
            'operation' => 'unknown',
            'did_mutate' => false,
            'fallback_reply' => 'I could not understand that inquiry yet. Try asking: "What tasks are due today?", "Show all high-priority tasks", "How many completed tasks do I have?", or "List tasks in the Work category".',
            'skip_ai_reply' => true,
        ];
    }

    private function runCrudOperation(User $user, array $intent, string $message, array $context): array
    {
        $operation = $intent['operation'] ?? 'unknown';

        if ($operation === 'unknown') {
            $operation = $this->fallbackIntent('crud', $message)['operation'];
        }

        if ($operation === 'update_due_date') {
            $titleHint = $intent['task_title'] ?? $this->extractTaskTitle(null, $message) ?? ($context['last_focus_task_title'] ?? null);
            $task = $this->findTask($user, $intent['task_id'] ?? 0, $titleHint, false);
            $dueDate = $this->normalizeDate($intent['due_date'] ?? null) ?? $this->extractDateFromText($message);

            if (! $task || ! $dueDate) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not update due date. Please specify a task and target date.',
                    'skip_ai_reply' => true,
                ];
            }

            return $this->pendingResult(
                [
                    'tool' => 'update_due_date',
                    'task_id' => $task->id,
                    'due_date' => $dueDate,
                    'summary' => "change due date of \"{$task->title}\" to {$dueDate}",
                ],
                "Please confirm: change due date of \"{$task->title}\" to {$dueDate}?"
            );
        }

        if ($operation === 'create_task') {
            $title = $this->extractTaskTitle($intent['task_title'] ?? null, $message);

            if (! $title) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I need a task title to create a task. Example: "Create task \"Prepare lab report\" due tomorrow".',
                ];
            }

            $dueDate = $this->normalizeDate($intent['due_date'] ?? null) ?? now()->addDay()->toDateString();
            $category = $this->resolveCategory($intent['category'] ?? null, $message) ?? $this->resolveDefaultCategory();
            $preferredPriority = $context['preferred_priority'] ?? null;

            $task = Task::query()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $title,
                'description' => $intent['description'] ?? null,
                'status' => $this->normalizeStatus($intent['status'] ?? null) ?? 'Not Started',
                'priority' => $this->normalizePriority($intent['priority'] ?? null) ?? $preferredPriority ?? 'Medium',
                'due_date' => $dueDate,
            ]);

            return [
                'operation' => $operation,
                'did_mutate' => true,
                'task' => $this->taskSummary($task->load('category')),
                'fallback_reply' => "Created task \"{$task->title}\" due on {$task->due_date->format('Y-m-d')}.",
                'context_updates' => $this->buildTaskContext(collect([$task])),
            ];
        }

        if ($operation === 'update_status') {
            $status = $this->normalizeStatus($intent['status'] ?? null) ?? $this->normalizeStatus($message);
            $titleHint = $intent['task_title'] ?? $this->extractTaskTitle(null, $message) ?? ($context['last_focus_task_title'] ?? null);
            $task = $this->findTask($user, $intent['task_id'] ?? 0, $titleHint, false);

            if (! $task || ! $status) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not update status. Please specify a task and one of: Not Started, In Progress, Completed.',
                    'skip_ai_reply' => true,
                ];
            }

            return $this->pendingResult(
                [
                    'tool' => 'update_status',
                    'task_id' => $task->id,
                    'status' => $status,
                    'summary' => "update status of \"{$task->title}\" to {$status}",
                ],
                "Please confirm: update status of \"{$task->title}\" to {$status}?"
            );
        }

        if ($operation === 'update_priority') {
            $priority = $this->normalizePriority($intent['priority'] ?? null) ?? $this->normalizePriority($message);
            $titleHint = $intent['task_title'] ?? $this->extractTaskTitle(null, $message) ?? ($context['last_focus_task_title'] ?? null);
            $task = $this->findTask($user, $intent['task_id'] ?? 0, $titleHint, false);

            if (! $task || ! $priority) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not update priority. Please specify a task and one of: High, Medium, Low.',
                    'skip_ai_reply' => true,
                ];
            }

            return $this->pendingResult(
                [
                    'tool' => 'update_priority',
                    'task_id' => $task->id,
                    'priority' => $priority,
                    'summary' => "set priority of \"{$task->title}\" to {$priority}",
                ],
                "Please confirm: set priority of \"{$task->title}\" to {$priority}?"
            );
        }

        if ($operation === 'delete_task') {
            $titleHint = $intent['task_title'] ?? $this->extractTaskTitle(null, $message) ?? ($context['last_focus_task_title'] ?? null);
            $task = $this->findTask($user, $intent['task_id'] ?? 0, $titleHint, false);

            if (! $task) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not find that task to archive.',
                    'skip_ai_reply' => true,
                ];
            }

            return $this->pendingResult(
                [
                    'tool' => 'delete_task',
                    'task_id' => $task->id,
                    'summary' => "archive \"{$task->title}\"",
                ],
                "Please confirm: archive \"{$task->title}\"?"
            );
        }

        if ($operation === 'delete_completed_last_month') {
            $count = Task::query()
                ->where('user_id', $user->id)
                ->withoutTrashed()
                ->where('status', 'Completed')
                ->whereBetween('due_date', [
                    now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                    now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
                ])
                ->count();

            if ($count === 0) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'There are no completed tasks from last month to delete.',
                    'skip_ai_reply' => true,
                ];
            }

            return $this->pendingResult(
                [
                    'tool' => 'delete_completed_last_month',
                    'summary' => "archive {$count} completed task(s) from last month",
                ],
                "Please confirm: archive {$count} completed task(s) from last month?"
            );
        }

        if ($operation === 'restore_task') {
            $titleHint = $intent['task_title'] ?? $this->extractTaskTitle(null, $message);
            $task = $this->findTask($user, $intent['task_id'] ?? 0, $titleHint, true, true);

            if (! $task) {
                return [
                    'operation' => $operation,
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not find that archived task to restore.',
                    'skip_ai_reply' => true,
                ];
            }

            $task->restore();

            return [
                'operation' => $operation,
                'did_mutate' => true,
                'fallback_reply' => "Restored task \"{$task->title}\".",
                'context_updates' => $this->buildTaskContext(collect([$task->fresh('category')])),
            ];
        }

        return [
            'operation' => 'unknown',
            'did_mutate' => false,
            'fallback_reply' => 'CRUD mode is enabled, but I need a clearer instruction. Try: "Create task Submit report due 2026-05-02", "Mark task Prepare slides as Completed", "Set priority of Project plan to High", or "Archive task Grocery list".',
            'skip_ai_reply' => true,
        ];
    }

    private function executePendingAction(User $user, array $pendingAction): array
    {
        $tool = (string) ($pendingAction['tool'] ?? '');

        if ($tool === 'update_status') {
            $task = $this->findTask($user, (int) ($pendingAction['task_id'] ?? 0));
            $status = $this->normalizeStatus((string) ($pendingAction['status'] ?? ''));

            if (! $task || ! $status) {
                return [
                    'operation' => 'update_status',
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not complete that status update.',
                    'skip_ai_reply' => true,
                ];
            }

            $task->update(['status' => $status]);

            return [
                'operation' => 'update_status',
                'did_mutate' => true,
                'task' => $this->taskSummary($task->fresh('category')),
                'fallback_reply' => "Updated \"{$task->title}\" to {$status}.",
                'context_updates' => $this->buildTaskContext(collect([$task->fresh('category')])),
                'skip_ai_reply' => true,
            ];
        }

        if ($tool === 'update_priority') {
            $task = $this->findTask($user, (int) ($pendingAction['task_id'] ?? 0));
            $priority = $this->normalizePriority((string) ($pendingAction['priority'] ?? ''));

            if (! $task || ! $priority) {
                return [
                    'operation' => 'update_priority',
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not complete that priority update.',
                    'skip_ai_reply' => true,
                ];
            }

            $task->update(['priority' => $priority]);

            return [
                'operation' => 'update_priority',
                'did_mutate' => true,
                'task' => $this->taskSummary($task->fresh('category')),
                'fallback_reply' => "Updated \"{$task->title}\" to {$priority} priority.",
                'context_updates' => [
                    ...$this->buildTaskContext(collect([$task->fresh('category')])),
                    'preferred_priority' => $priority,
                ],
                'skip_ai_reply' => true,
            ];
        }

        if ($tool === 'update_due_date') {
            $task = $this->findTask($user, (int) ($pendingAction['task_id'] ?? 0));
            $dueDate = $this->normalizeDate((string) ($pendingAction['due_date'] ?? ''));

            if (! $task || ! $dueDate) {
                return [
                    'operation' => 'update_due_date',
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not complete that due date update.',
                    'skip_ai_reply' => true,
                ];
            }

            $task->update(['due_date' => $dueDate]);

            return [
                'operation' => 'update_due_date',
                'did_mutate' => true,
                'task' => $this->taskSummary($task->fresh('category')),
                'fallback_reply' => "Changed due date of \"{$task->title}\" to {$dueDate}.",
                'context_updates' => $this->buildTaskContext(collect([$task->fresh('category')])),
                'skip_ai_reply' => true,
            ];
        }

        if ($tool === 'delete_task') {
            $task = $this->findTask($user, (int) ($pendingAction['task_id'] ?? 0));

            if (! $task) {
                return [
                    'operation' => 'delete_task',
                    'did_mutate' => false,
                    'fallback_reply' => 'I could not find the task to archive anymore.',
                    'skip_ai_reply' => true,
                ];
            }

            $title = $task->title;
            $task->delete();

            return [
                'operation' => 'delete_task',
                'did_mutate' => true,
                'fallback_reply' => "Archived task \"{$title}\".",
                'skip_ai_reply' => true,
            ];
        }

        if ($tool === 'delete_completed_last_month') {
            $tasks = Task::query()
                ->where('user_id', $user->id)
                ->withoutTrashed()
                ->where('status', 'Completed')
                ->whereBetween('due_date', [
                    now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                    now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
                ])
                ->get();

            if ($tasks->isEmpty()) {
                return [
                    'operation' => 'delete_completed_last_month',
                    'did_mutate' => false,
                    'fallback_reply' => 'No matching completed tasks remained to archive.',
                    'skip_ai_reply' => true,
                ];
            }

            $titles = $tasks->pluck('title')->values();
            Task::query()->whereIn('id', $tasks->pluck('id'))->delete();

            return [
                'operation' => 'delete_completed_last_month',
                'did_mutate' => true,
                'deleted_titles' => $titles,
                'fallback_reply' => "Archived {$titles->count()} completed task(s) from last month: ".implode(', ', $titles->all()),
                'skip_ai_reply' => true,
            ];
        }

        return [
            'operation' => 'unknown',
            'did_mutate' => false,
            'fallback_reply' => 'That pending action is no longer valid.',
            'skip_ai_reply' => true,
        ];
    }

    private function pendingResult(array $pendingAction, string $reply): array
    {
        return [
            'operation' => $pendingAction['tool'] ?? 'unknown',
            'did_mutate' => false,
            'needs_confirmation' => true,
            'confirmation_summary' => $pendingAction['summary'] ?? 'destructive action',
            'pending_action' => $pendingAction,
            'fallback_reply' => $reply.' Reply with Confirm or Cancel.',
            'skip_ai_reply' => true,
        ];
    }

    private function handleFollowUpInquiry(User $user, string $message, array $context): ?array
    {
        $normalized = Str::lower($message);
        $lastTaskIds = collect((array) ($context['last_task_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($lastTaskIds->isEmpty()) {
            if ($this->containsPronoun($normalized) && isset($context['last_focus_task_id'])) {
                $task = $this->activeTaskQuery($user)->whereKey((int) $context['last_focus_task_id'])->first();

                if ($task) {
                    return [
                        'operation' => 'follow_up_task_focus',
                        'did_mutate' => false,
                        'task' => $this->taskSummary($task),
                        'fallback_reply' => "Task details: {$task->title} is {$task->status}, {$task->priority} priority, due {$task->due_date->format('Y-m-d')}.",
                        'context_updates' => $this->buildTaskContext(collect([$task])),
                        'skip_ai_reply' => true,
                    ];
                }
            }

            return null;
        }

        $isFollowUp = str_contains($normalized, 'which ones')
            || str_contains($normalized, 'what about')
            || str_contains($normalized, 'those')
            || str_contains($normalized, 'them')
            || str_contains($normalized, 'now')
            || str_contains($normalized, 'remove non')
            || $this->containsPronoun($normalized);

        if (! $isFollowUp) {
            return null;
        }

        $tasks = $this->activeTaskQuery($user)
            ->whereIn('id', $lastTaskIds)
            ->orderBy('due_date')
            ->get();

        $filtersApplied = false;
        $priority = $this->normalizePriority($message);
        if ($priority) {
            $tasks = $tasks->where('priority', $priority)->values();
            $filtersApplied = true;
        }

        $status = $this->normalizeStatus($message);
        if ($status) {
            $tasks = $tasks->where('status', $status)->values();
            $filtersApplied = true;
        }

        if (str_contains($normalized, 'due this week')) {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $tasks = $tasks->filter(fn (Task $task) => $task->due_date !== null && Carbon::parse($task->due_date)->between($start, $end))->values();
            $filtersApplied = true;
        }

        if (str_contains($normalized, 'due today')) {
            $today = now()->toDateString();
            $tasks = $tasks->filter(fn (Task $task) => optional($task->due_date)->format('Y-m-d') === $today)->values();
            $filtersApplied = true;
        }

        $category = $this->resolveCategory(null, $message);
        if ($category) {
            $tasks = $tasks->where('category_id', $category->id)->values();
            $filtersApplied = true;
        }

        if (! $filtersApplied && $this->containsPronoun($normalized) && isset($context['last_focus_task_id'])) {
            $focusTask = $this->activeTaskQuery($user)->whereKey((int) $context['last_focus_task_id'])->first();

            if ($focusTask) {
                return [
                    'operation' => 'follow_up_task_focus',
                    'did_mutate' => false,
                    'task' => $this->taskSummary($focusTask),
                    'fallback_reply' => "Task details: {$focusTask->title} is {$focusTask->status}, {$focusTask->priority} priority, due {$focusTask->due_date->format('Y-m-d')}.",
                    'context_updates' => $this->buildTaskContext(collect([$focusTask])),
                    'skip_ai_reply' => true,
                ];
            }
        }

        if (! $filtersApplied) {
            return null;
        }

        return [
            'operation' => 'follow_up_filter',
            'did_mutate' => false,
            'count' => $tasks->count(),
            'tasks' => $this->taskList($tasks),
            'fallback_reply' => $this->formatTaskListReply('Filtered results from previous context', $tasks),
            'context_updates' => $this->buildTaskContext($tasks),
            'skip_ai_reply' => true,
        ];
    }

    private function buildTaskContext(Collection $tasks): array
    {
        if ($tasks->isEmpty()) {
            return [
                'last_task_ids' => [],
            ];
        }

        $first = $tasks->first();

        return [
            'last_task_ids' => $tasks->pluck('id')->take(30)->values()->all(),
            'last_focus_task_id' => $first?->id,
            'last_focus_task_title' => $first?->title,
        ];
    }

    private function mergeContext(array $base, array $updates): array
    {
        foreach ($updates as $key => $value) {
            $base[$key] = $value;
        }

        return $base;
    }

    private function containsPronoun(string $normalized): bool
    {
        return str_contains($normalized, ' it ')
            || str_contains($normalized, ' its ')
            || str_starts_with($normalized, 'it ')
            || str_starts_with($normalized, 'its ')
            || str_contains($normalized, ' this one ')
            || str_contains($normalized, ' that one ');
    }

    private function isConfirmMessage(string $message): bool
    {
        $normalized = Str::lower(trim($message));

        return in_array($normalized, ['confirm', '__confirm__', 'yes', 'y', 'proceed', 'go ahead'], true);
    }

    private function isCancelMessage(string $message): bool
    {
        $normalized = Str::lower(trim($message));

        return in_array($normalized, ['cancel', '__cancel__', 'no', 'n', 'stop'], true);
    }

    private function extractDateFromText(string $message): ?string
    {
        $normalized = Str::lower($message);

        if (str_contains($normalized, 'tomorrow')) {
            return now()->addDay()->toDateString();
        }

        if (str_contains($normalized, 'next friday')) {
            return now()->next(Carbon::FRIDAY)->toDateString();
        }

        return null;
    }

    private function activeTaskQuery(User $user): Builder
    {
        return Task::query()
            ->with('category')
            ->where('user_id', $user->id)
            ->withoutTrashed();
    }

    private function resolveCategory(?string $hint, string $message): ?Category
    {
        $candidate = trim((string) $hint);

        if ($candidate === '') {
            preg_match('/category\s+["\']?([a-zA-Z0-9\s\-_]+)["\']?/i', $message, $matches);
            if (isset($matches[1])) {
                $candidate = trim($matches[1]);
            }
        }

        if ($candidate === '') {
            $categories = Category::query()->get(['id', 'name']);
            $lowered = Str::lower($message);

            foreach ($categories as $category) {
                if (str_contains($lowered, Str::lower($category->name))) {
                    return $category;
                }
            }

            return null;
        }

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($candidate)])
            ->orWhereRaw('LOWER(slug) = ?', [Str::slug($candidate)])
            ->first();
    }

    private function resolveDefaultCategory(): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General']
        );
    }

    private function findTask(User $user, int $taskId = 0, ?string $title = null, bool $includeTrashed = false, bool $onlyTrashed = false): ?Task
    {
        $query = Task::query()->where('user_id', $user->id);

        if ($includeTrashed) {
            $query->withTrashed();
        }

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        if ($taskId > 0) {
            return $query->whereKey($taskId)->first();
        }

        $title = trim((string) $title);

        if ($title === '') {
            return null;
        }

        return $query
            ->whereRaw('LOWER(title) LIKE ?', ['%'.Str::lower($title).'%'])
            ->orderByRaw('CASE WHEN LOWER(title) = ? THEN 0 ELSE 1 END', [Str::lower($title)])
            ->latest('updated_at')
            ->first();
    }

    private function extractTaskTitle(?string $candidate, string $message): ?string
    {
        $candidate = trim((string) $candidate);

        if ($candidate !== '') {
            return $candidate;
        }

        if (preg_match('/["\']([^"\']+)["\']/', $message, $matches)) {
            return trim($matches[1]);
        }

        $title = preg_replace('/^(create|add|new)\s+(a\s+)?task\s*/i', '', $message) ?? '';
        $title = preg_replace('/\s+due\s+.+$/i', '', $title) ?? $title;

        $title = trim($title);

        return $title === '' ? null : Str::limit($title, 255, '');
    }

    private function taskSummary(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => optional($task->due_date)->format('Y-m-d'),
            'category' => $task->category?->name,
        ];
    }

    private function taskList($tasks): array
    {
        return $tasks->map(fn (Task $task) => $this->taskSummary($task))->values()->all();
    }

    private function formatTaskListReply(string $label, $tasks): string
    {
        if ($tasks->isEmpty()) {
            return "{$label}: no matching tasks found.";
        }

        $items = $tasks->map(function (Task $task) {
            $dueDate = optional($task->due_date)->format('Y-m-d') ?? 'no due date';
            return "- {$task->title} ({$task->status}, {$task->priority}, due {$dueDate})";
        })->implode("\n");

        return "{$label}:\n{$items}";
    }

    private function normalizeStatus(?string $status): ?string
    {
        $normalized = Str::lower(trim((string) $status));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'not started') || str_contains($normalized, 'todo') || str_contains($normalized, 'pending')) {
            return 'Not Started';
        }

        if (str_contains($normalized, 'in progress') || str_contains($normalized, 'ongoing') || str_contains($normalized, 'doing')) {
            return 'In Progress';
        }

        if (str_contains($normalized, 'completed') || str_contains($normalized, 'done') || str_contains($normalized, 'finished')) {
            return 'Completed';
        }

        return null;
    }

    private function normalizePriority(?string $priority): ?string
    {
        $normalized = Str::lower(trim((string) $priority));

        if ($normalized === '') {
            return null;
        }

        if (str_contains($normalized, 'high') || str_contains($normalized, 'urgent')) {
            return 'High';
        }

        if (str_contains($normalized, 'medium') || str_contains($normalized, 'normal')) {
            return 'Medium';
        }

        if (str_contains($normalized, 'low')) {
            return 'Low';
        }

        return null;
    }

    private function normalizeDate(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function fallbackIntent(string $mode, string $message): array
    {
        $normalized = Str::lower($message);

        $inquiryMap = [
            'tasks_due_today' => ['due today', 'today'],
            'tasks_by_priority' => ['high-priority', 'high priority', 'low priority', 'medium priority'],
            'count_completed' => ['how many completed', 'count completed', 'completed tasks do i have'],
            'oldest_pending' => ['oldest pending', 'oldest task'],
            'tasks_by_category' => ['category'],
            'count_categories' => ['how many categories', 'number of categories'],
            'list_tasks' => ['what tasks', 'show tasks', 'list tasks'],
        ];

        $crudMap = [
            'create_task' => ['create task', 'add task', 'new task'],
            'update_status' => ['mark', 'set status', 'change status'],
            'update_priority' => ['set priority', 'change priority'],
            'update_due_date' => ['change due date', 'update due date', 'set due date'],
            'delete_task' => ['delete task', 'remove task', 'archive task'],
            'delete_completed_last_month' => ['delete all completed tasks from last month', 'remove completed tasks from last month'],
            'restore_task' => ['restore task', 'unarchive task'],
        ];

        $operation = 'unknown';

        foreach (($mode === 'crud' ? $crudMap : $inquiryMap) as $key => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($normalized, $pattern)) {
                    $operation = $key;
                    break 2;
                }
            }
        }

        return [
            'intent' => $mode,
            'operation' => $operation,
            'task_title' => null,
            'task_id' => 0,
            'status' => null,
            'priority' => null,
            'category' => null,
            'due_date' => null,
            'description' => null,
        ];
    }
}
