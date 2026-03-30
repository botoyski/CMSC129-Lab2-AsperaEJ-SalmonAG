<article
    x-data="{ isDescriptionExpanded: false }"
    :class="[
        task.priority === 'High' ? 'bg-red-500/10 border-red-500/30' : (task.priority === 'Medium' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-emerald-500/10 border-emerald-500/30'),
        $store.tasksApp.viewMode === 'grid' ? 'h-[320px]' : ''
    ]"
    class="flex h-full flex-col overflow-hidden rounded-lg border p-6 shadow-sm transition-all hover:shadow-md hover:shadow-black/20"
>
    <div class="mb-4 flex flex-1 items-start justify-between">
        <div class="min-w-0 flex-1">
            <div class="mb-1 flex items-center gap-2">
                <h3 class="break-words text-lg font-semibold text-zinc-100" x-text="task.title"></h3>
                <span x-show="task.categoryName" class="rounded-full border border-zinc-700 px-2 py-0.5 text-[10px] uppercase tracking-wide text-zinc-300" x-text="task.categoryName"></span>
            </div>
            <p
                class="max-w-full text-sm text-zinc-400 break-all"
                :class="$store.tasksApp.viewMode === 'grid' && isDescriptionExpanded ? 'max-h-24 overflow-y-auto pr-1' : ''"
                x-text="$store.tasksApp.viewMode === 'grid' && !isDescriptionExpanded && (task.description || '').length > 140
                    ? `${task.description.slice(0, 140)}...`
                    : (task.description || '')"
            ></p>

            <a :href="`/tasks/${task.id}`" class="mt-2 inline-flex text-xs font-medium text-sky-400 transition hover:text-sky-300">View details</a>

            <button
                x-show="$store.tasksApp.viewMode === 'grid' && (task.description || '').length > 140"
                type="button"
                @click="isDescriptionExpanded = !isDescriptionExpanded"
                class="mt-2 text-xs font-medium text-sky-400 transition hover:text-sky-300"
                x-text="isDescriptionExpanded ? 'Show less' : 'Read more'"
            ></button>
        </div>

        <div class="ml-4 flex shrink-0 items-center gap-1">
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

    <div class="mt-auto flex flex-wrap items-center gap-3 border-t border-zinc-800/60 pt-4">
        <span
            :class="task.priority === 'High' ? 'bg-red-500/20 text-red-300' : (task.priority === 'Medium' ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300')"
            class="rounded-full px-3 py-1 text-xs font-medium"
            x-text="task.priority"
        ></span>

        <select
            x-init="$nextTick(() => { $el.value = task.status; })"
            x-effect="$el.value = task.status"
            @change="$store.tasksApp.changeStatus($store.tasksApp.resolveTaskId(task), $event.target.value)"
            :disabled="task.archived"
            class="cursor-pointer rounded-full border border-zinc-700 bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-200 outline-none"
        >
            <template x-for="status in $store.tasksApp.statuses" :key="status">
                <option :value="status" x-text="status"></option>
            </template>
        </select>

        <div class="ml-auto flex items-center justify-end gap-1 whitespace-nowrap text-xs text-zinc-400">
            <span>📅</span>
            <span x-text="task.dueDate"></span>
            <template x-if="task.dueTime">
                <span
                    class="inline-block"
                    x-text="(() => {
                        const [hours, minutes] = task.dueTime.split(':');
                        const hourNumber = Number(hours);
                        const period = hourNumber >= 12 ? 'PM' : 'AM';
                        const displayHour = ((hourNumber + 11) % 12) + 1;

                        return `, ${displayHour}:${minutes} ${period}`;
                    })()"
                ></span>
            </template>
        </div>
    </div>
</article>
