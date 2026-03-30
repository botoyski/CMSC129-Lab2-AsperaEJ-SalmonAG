@extends('layouts.app')

@section('title', 'Task Details')

@section('content')
    <div class="min-h-[calc(100vh-4rem)] bg-zinc-950 px-4 py-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-zinc-100">Task Details</h1>
                <a href="{{ route('dashboard') }}" class="rounded-md border border-zinc-700 px-4 py-2 text-sm text-zinc-200 transition hover:bg-zinc-800">Back to Dashboard</a>
            </div>

            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
                <h2 class="text-xl font-semibold text-zinc-100">{{ $task->title }}</h2>
                <p class="mt-3 whitespace-pre-wrap text-zinc-300">{{ $task->description ?: 'No description provided.' }}</p>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Status</p>
                        <p class="mt-1 text-zinc-200">{{ $task->status }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Priority</p>
                        <p class="mt-1 text-zinc-200">{{ $task->priority }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Category</p>
                        <p class="mt-1 text-zinc-200">{{ $task->category?->name ?? 'General' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-zinc-500">Due Date</p>
                        <p class="mt-1 text-zinc-200">{{ optional($task->due_date)->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
