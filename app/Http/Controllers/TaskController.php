<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:All,Not Started,In Progress,Completed'],
            'priority' => ['nullable', 'string', 'in:All,High,Medium,Low'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'view' => ['nullable', 'string', 'in:active,trash'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $view = $request->string('view', 'active')->toString();
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'All');
        $priority = $request->input('priority', 'All');
        $categoryId = $request->input('category_id');
        $perPage = (int) $request->input('per_page', 9);

        $query = Task::query()
            ->with('category')
            ->where('user_id', $user->id)
            ->when($view === 'trash', fn (Builder $builder) => $builder->onlyTrashed(), fn (Builder $builder) => $builder->withoutTrashed())
            ->when($search !== '', function (Builder $builder) use ($search) {
                $searchTerm = mb_strtolower($search);

                $builder->where(function (Builder $nested) use ($searchTerm) {
                    $nested->whereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]));
                });
            })
            ->when($status !== 'All', fn (Builder $builder) => $builder->where('status', $status))
            ->when($priority !== 'All', fn (Builder $builder) => $builder->where('priority', $priority))
            ->when($categoryId, fn (Builder $builder) => $builder->where('category_id', $categoryId))
            ->latest();

        $paginated = $query->paginate($perPage)->withQueryString();

        $tasks = $paginated->getCollection()->map(fn (Task $task) => $this->serializeTask($task));

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $tasks,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'counts' => $this->buildCounts($user->id),
                'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            ]);
        }

        return redirect()->route('dashboard');
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $task = Task::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => $validated['status'] ?? 'Not Started',
            'category_id' => $validated['category_id'] ?? $this->resolveDefaultCategory()->id,
        ])->load('category');

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $this->serializeTask($task),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->load('category');

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $this->serializeTask($task),
            ]);
        }

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $validated = $request->validated();

        $task->update([
            ...$validated,
            'category_id' => $validated['category_id'] ?? $this->resolveDefaultCategory()->id,
        ]);

        $task->load('category');

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $this->serializeTask($task),
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $task->delete();

        return response()->json([
            'message' => 'Task moved to trash.',
        ]);
    }

    public function restore(Request $request, int $taskId): JsonResponse
    {
        $task = Task::onlyTrashed()->whereKey($taskId)->firstOrFail();
        $this->authorizeTask($request, $task);

        $task->restore();
        $task->load('category');

        return response()->json([
            'message' => 'Task restored successfully.',
            'data' => $this->serializeTask($task),
        ]);
    }

    public function forceDestroy(Request $request, int $taskId): JsonResponse
    {
        $task = Task::onlyTrashed()->whereKey($taskId)->firstOrFail();
        $this->authorizeTask($request, $task);

        $task->forceDelete();

        return response()->json([
            'message' => 'Task permanently deleted.',
        ]);
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_if($task->user_id !== $request->user()->id, Response::HTTP_FORBIDDEN);
    }

    private function resolveDefaultCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General']
        );
    }

    private function serializeTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description ?? '',
            'status' => $task->status,
            'priority' => $task->priority,
            'dueDate' => optional($task->due_date)->format('Y-m-d'),
            'dueTime' => $task->due_time ? substr((string) $task->due_time, 0, 5) : '',
            'archived' => $task->trashed(),
            'createdAt' => optional($task->created_at)?->toISOString(),
            'deletedAt' => optional($task->deleted_at)?->toISOString(),
            'categoryId' => $task->category_id,
            'categoryName' => $task->category?->name,
        ];
    }

    private function buildCounts(int $userId): array
    {
        $activeBase = Task::query()->where('user_id', $userId)->withoutTrashed();

        return [
            'all' => (clone $activeBase)->count(),
            'not_started' => (clone $activeBase)->where('status', 'Not Started')->count(),
            'in_progress' => (clone $activeBase)->where('status', 'In Progress')->count(),
            'completed' => (clone $activeBase)->where('status', 'Completed')->count(),
            'archived' => Task::query()->where('user_id', $userId)->onlyTrashed()->count(),
            'added_today' => (clone $activeBase)->whereDate('created_at', now()->toDateString())->count(),
        ];
    }
}
