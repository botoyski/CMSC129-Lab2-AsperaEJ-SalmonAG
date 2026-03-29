<article
    x-data="{ showMenu: false }"
    :class="task.priority === 'High' ? 'bg-red-500/10 border-red-500/30' : (task.priority === 'Medium' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-emerald-500/10 border-emerald-500/30')"
    class="rounded-lg border p-6 shadow-sm transition-all hover:shadow-md hover:shadow-black/20"
>
    <div class="mb-4 flex items-start justify-between">
        <div class="flex-1">
            <h3 class="mb-1 text-lg font-semibold text-zinc-100" x-text="task.title"></h3>
            <p class="text-sm text-zinc-400" x-text="task.description"></p>
        </div>

        <div class="relative ml-4">
            <button type="button" @click="showMenu = !showMenu" class="rounded-lg p-1 transition-colors hover:bg-zinc-800">
                <svg class="h-4.5 w-4.5 text-zinc-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 5H12.01M12 12H12.01M12 19H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <div x-show="showMenu" @click.outside="showMenu = false" x-transition class="absolute right-0 top-full z-10 mt-1 w-36 rounded-lg border border-zinc-800 bg-zinc-900 shadow-lg shadow-black/30">
                <button
                    type="button"
                    @click="$store.tasksApp.editTask($store.tasksApp.resolveTaskId(task)); showMenu = false"
                    class="block w-full px-4 py-2 text-left text-sm text-zinc-300 transition hover:bg-zinc-800"
                >
                    Edit
                </button>
                <button
                    type="button"
                    @click="$store.tasksApp.archiveTask($store.tasksApp.resolveTaskId(task)); showMenu = false"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-zinc-300 transition hover:bg-zinc-800"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 7H20M5 7L6 19H18L19 7M9 11V15M15 11V15M10 7V5H14V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span x-text="$store.tasksApp.showArchived ? 'Restore' : 'Archive'"></span>
                </button>
                <button
                    type="button"
                    @click="$store.tasksApp.deleteTask($store.tasksApp.resolveTaskId(task)); showMenu = false"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-400 transition hover:bg-red-500/10"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 7H20M9 11V17M15 11V17M6 7L7 19H17L18 7M10 7V5H14V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-zinc-800/60 pt-4">
        <span
            :class="task.priority === 'High' ? 'bg-red-500/20 text-red-300' : (task.priority === 'Medium' ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300')"
            class="rounded-full px-3 py-1 text-xs font-medium"
            x-text="task.priority"
        ></span>

        <select
            x-model="task.status"
            @change="$store.tasksApp.changeStatus(task, $event.target.value)"
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
