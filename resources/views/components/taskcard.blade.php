<article
    :class="task.priority === 'High' ? 'bg-red-500/10 border-red-500/30' : (task.priority === 'Medium' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-emerald-500/10 border-emerald-500/30')"
    class="rounded-lg border p-6 shadow-sm transition-all hover:shadow-md hover:shadow-black/20"
>
    <div class="mb-4 flex items-start justify-between">
        <div class="flex-1">
            <h3 class="mb-1 text-lg font-semibold text-zinc-100" x-text="task.title"></h3>
            <p class="text-sm text-zinc-400" x-text="task.description"></p>
        </div>

        <div class="ml-4 flex items-center gap-1">
            <button
                type="button"
                @click="$store.tasksApp.editTask($store.tasksApp.resolveTaskId(task))"
                class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-zinc-100"
                title="Edit"
                aria-label="Edit task"
            >
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 20H8L18.5 9.5C19.3 8.7 19.3 7.3 18.5 6.5L17.5 5.5C16.7 4.7 15.3 4.7 14.5 5.5L4 16V20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <button
                type="button"
                @click="$store.tasksApp.requestArchiveTask($store.tasksApp.resolveTaskId(task))"
                class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-800 hover:text-zinc-100"
                :title="task.archived ? 'Restore' : 'Archive'"
                :aria-label="task.archived ? 'Restore task' : 'Archive task'"
            >
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 7H20M5 7L6 19H18L19 7M9 11V15M15 11V15M10 7V5H14V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <button
                type="button"
                @click="$store.tasksApp.requestDeleteTask($store.tasksApp.resolveTaskId(task))"
                class="rounded-lg p-1.5 text-red-400 transition-colors hover:bg-red-500/10 hover:text-red-300"
                title="Delete"
                aria-label="Delete task"
            >
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 7H20M9 11V17M15 11V17M6 7L7 19H17L18 7M10 7V5H14V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-zinc-800/60 pt-4">
        <span
            :class="task.priority === 'High' ? 'bg-red-500/20 text-red-300' : (task.priority === 'Medium' ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300')"
            class="rounded-full px-3 py-1 text-xs font-medium"
            x-text="task.priority"
        ></span>

        <select
            x-init="$nextTick(() => { $el.value = task.status; })"
            x-effect="$el.value = task.status"
            @change="$store.tasksApp.changeStatus($store.tasksApp.resolveTaskId(task), $event.target.value)"
            class="cursor-pointer rounded-full border border-zinc-700 bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-200 outline-none"
        >
            <template x-for="status in $store.tasksApp.statuses" :key="status">
                <option :value="status" x-text="status"></option>
            </template>
        </select>

        <div class="ml-auto flex items-center gap-1 text-xs text-zinc-400">
            <span>📅</span>
            <span x-text="task.dueDate"></span>
            <template x-if="task.dueTime">
                <span x-text="'at ' + task.dueTime"></span>
            </template>
        </div>
    </div>
</article>
