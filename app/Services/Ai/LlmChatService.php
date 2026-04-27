<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LlmChatService
{
    public function classifyMessage(string $mode, string $message, array $history = []): array
    {
        $historyText = $this->stringifyHistory($history);

        $schemaInstruction = <<<'TEXT'
Return ONLY valid JSON with this shape:
{
  "intent": "inquiry|crud|unknown",
    "operation": "tasks_due_today|tasks_by_priority|count_completed|oldest_pending|tasks_by_category|list_tasks|count_categories|follow_up_filter|create_task|update_status|update_priority|update_due_date|delete_task|delete_completed_last_month|restore_task|unknown",
  "task_title": "string|null",
  "task_id": 0,
  "status": "Not Started|In Progress|Completed|null",
  "priority": "High|Medium|Low|null",
  "category": "string|null",
  "due_date": "YYYY-MM-DD|null",
  "description": "string|null"
}
TEXT;

        $systemPrompt = <<<PROMPT
You are a strict intent parser for a Laravel task manager assistant.
- Mode: {$mode}
- Determine if the user is asking about data inquiries or CRUD operations.
- If mode is inquiry, never output CRUD operations.
- If mode is crud, infer operation carefully from the text.
- task_id must be an integer, use 0 when absent.
{$schemaInstruction}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Recent context:\n{$historyText}\n\nUser message:\n{$message}"],
        ];

        $raw = $this->chat($messages, 0.1);
        $decoded = $this->extractJson($raw);

        if (! is_array($decoded)) {
            throw new RuntimeException('Unable to parse AI output.');
        }

        return [
            'intent' => in_array(Arr::get($decoded, 'intent'), ['inquiry', 'crud', 'unknown'], true)
                ? Arr::get($decoded, 'intent')
                : 'unknown',
            'operation' => (string) Arr::get($decoded, 'operation', 'unknown'),
            'task_title' => $this->nullableString(Arr::get($decoded, 'task_title')),
            'task_id' => (int) Arr::get($decoded, 'task_id', 0),
            'status' => $this->nullableString(Arr::get($decoded, 'status')),
            'priority' => $this->nullableString(Arr::get($decoded, 'priority')),
            'category' => $this->nullableString(Arr::get($decoded, 'category')),
            'due_date' => $this->nullableString(Arr::get($decoded, 'due_date')),
            'description' => $this->nullableString(Arr::get($decoded, 'description')),
        ];
    }

    public function composeReply(string $mode, string $message, array $history, array $result): string
    {
        $historyText = $this->stringifyHistory($history);
        $resultJson = json_encode($result, JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You are a concise and friendly assistant for a task manager app.
- Mode: {$mode}
- If an operation was executed successfully, mention what changed.
- If user query is unclear, provide short examples.
- Never invent data outside the provided operation result.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Recent context:\n{$historyText}\n\nUser message:\n{$message}\n\nOperation result:\n{$resultJson}"],
        ];

        return $this->chat($messages, 0.3);
    }

    private function chat(array $messages, float $temperature = 0.2): string
    {
        $config = config('services.ai_assistant');
        $apiKey = trim((string) ($config['api_key'] ?? ''));

        if ($apiKey === '') {
            throw new RuntimeException('Missing LLM API key.');
        }

        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $model = (string) ($config['model'] ?? 'gpt-4o-mini');
        $timeout = max(5, (int) ($config['timeout'] ?? 20));

        $response = $this->request($baseUrl, $apiKey, $timeout)
            ->post('/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('LLM request failed.');
        }

        return trim((string) data_get($response->json(), 'choices.0.message.content', ''));
    }

    private function request(string $baseUrl, string $apiKey, int $timeout): PendingRequest
    {
        return Http::asJson()
            ->baseUrl($baseUrl)
            ->timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson();
    }

    private function extractJson(string $content): ?array
    {
        $trimmed = trim($content);

        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```[a-zA-Z]*\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        $decoded = json_decode($trimmed, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        preg_match('/\{.*\}/s', $trimmed, $matches);
        if (! isset($matches[0])) {
            return null;
        }

        $decoded = json_decode($matches[0], true);

        return is_array($decoded) ? $decoded : null;
    }

    private function stringifyHistory(array $history): string
    {
        if ($history === []) {
            return 'No prior messages.';
        }

        $lines = [];
        foreach (array_slice($history, -10) as $item) {
            $role = (string) Arr::get($item, 'role', 'assistant');
            $content = (string) Arr::get($item, 'content', '');
            $lines[] = "{$role}: {$content}";
        }

        return implode("\n", $lines);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' || strtolower($string) === 'null' ? null : $string;
    }
}
