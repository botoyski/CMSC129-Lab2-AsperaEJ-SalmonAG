@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div x-data="$store.tasksApp" x-init="init()" class="min-h-[calc(100vh-4rem)] bg-zinc-950 px-4 py-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="mb-2 text-3xl font-bold text-zinc-100">Dashboard</h1>
                    <p class="text-zinc-400">Manage your tasks and track progress.</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative w-72 max-w-full">
                        <svg class="absolute left-3 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-zinc-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M11 5C14.3 5 17 7.7 17 11C17 12.5 16.4 13.8 15.4 14.8L19 18.4L17.6 19.8L14 16.2C13 17.2 11.7 17.8 10.2 17.8C6.9 17.8 4.2 15.1 4.2 11.8C4.2 8.5 6.9 5.8 10.2 5.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input
                            type="text"
                            placeholder="Search tasks..."
                            x-model="searchValue"
                            class="w-full rounded-lg border border-zinc-800 bg-zinc-900 py-2 pl-10 pr-4 text-sm text-zinc-200 placeholder-zinc-500 outline-none transition focus:border-sky-500"
                        >
                    </div>

                    <button type="button" @click="addTask()" class="inline-flex items-center gap-2 rounded-lg bg-sky-500 px-4 py-2 text-sm font-medium text-zinc-950 transition hover:bg-sky-400">
                        <span class="text-lg font-bold leading-none">+</span>
                        <span>New Task</span>
                    </button>
                </div>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-summarycard icon="📊" label="Total Tasks" x-value="activeTasks.length" color="blue" :trend="2" />
                <x-summarycard icon="⭕" label="Not Started" x-value="countByStatus('Not Started')" color="gray" />
                <x-summarycard icon="⚡" label="In Progress" x-value="countByStatus('In Progress')" color="orange" />
                <x-summarycard icon="✅" label="Completed" x-value="countByStatus('Completed')" color="green" />
            </div>

            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-bold text-zinc-100">Tasks</h2>
                    <button type="button" @click="showArchived = !showArchived" class="rounded-lg px-3 py-1 text-sm font-medium transition" :class="showArchived ? 'bg-zinc-700 text-white' : 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700'" x-text="showArchived ? 'Archived' : 'Active'"></button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="viewMode = 'list'" class="rounded-lg p-2" :class="viewMode === 'list' ? 'bg-sky-500 text-zinc-950' : 'border border-zinc-800 bg-zinc-900 text-zinc-300 hover:bg-zinc-800'">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M5 7H19M5 12H19M5 17H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                    <button type="button" @click="viewMode = 'grid'" class="rounded-lg p-2" :class="viewMode === 'grid' ? 'bg-sky-500 text-zinc-950' : 'border border-zinc-800 bg-zinc-900 text-zinc-300 hover:bg-zinc-800'">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 4H10V10H4V4ZM14 4H20V10H14V4ZM4 14H10V20H4V14ZM14 14H20V20H14V14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mb-6 flex flex-wrap items-center gap-3">
                <div class="mr-2 flex items-center gap-2 text-sm font-medium text-zinc-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 6H20L14 13V18L10 20V13L4 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Filter:</span>
                </div>

                <template x-for="status in ['All', 'Not Started', 'In Progress', 'Completed']" :key="status">
                    <button type="button" @click="filterStatus = status" class="rounded-full px-3 py-1 text-sm font-medium transition" :class="filterStatus === status ? 'bg-sky-500 text-zinc-950' : 'border border-zinc-700 bg-zinc-900 text-zinc-300 hover:border-sky-500'" x-text="status"></button>
                </template>
            </div>

            <template x-if="displayTasks.length > 0">
                <div class="grid gap-6" :class="viewMode === 'grid' ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'">
                    <template x-for="task in displayTasks" :key="$store.tasksApp.resolveTaskId(task)">
                        <x-taskcard />
                    </template>
                </div>
            </template>

            <template x-if="displayTasks.length === 0">
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 py-12 text-center">
                    <div class="mb-4 text-4xl">📭</div>
                    <h3 class="mb-2 text-lg font-semibold text-zinc-100">No tasks found</h3>
                    <p class="text-zinc-400">Try adjusting your filters or create a new task to get started.</p>
                </div>
            </template>
        </div>

        <x-taskmodal />
    </div>
@endsection
