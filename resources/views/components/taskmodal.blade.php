<div
    x-cloak
    x-show="isModalOpen"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
    @keydown.escape.window="closeModal()"
>
    <div x-cloak x-show="isModalOpen" x-transition class="w-full max-w-md rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow-lg shadow-black/40">
        <h2 class="mb-4 text-xl font-semibold text-zinc-100" x-text="editingTaskId ? 'Edit Task' : 'Add New Task'"></h2>

        <form @submit.prevent="submitTask()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-zinc-300">Title</label>
                <input
                    type="text"
                    x-model="form.title"
                    class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-300">Description</label>
                <textarea
                    x-model="form.description"
                    :maxlength="$store.tasksApp.descriptionMaxLength"
                    class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                    rows="3"
                ></textarea>
                <p class="mt-1 text-right text-xs text-zinc-500" x-text="`${form.description.length}/${$store.tasksApp.descriptionMaxLength}`"></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-300">Priority</label>
                    <select
                        x-model="form.priority"
                        class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                    >
                        <option>High</option>
                        <option>Medium</option>
                        <option>Low</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-300">Due Date</label>
                    <input
                        type="date"
                        x-model="form.dueDate"
                        class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                        required
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-zinc-300">Due Time</label>
                        <button
                            type="button"
                            x-show="form.dueTime"
                            @click="form.dueTime = ''"
                            class="text-xs font-medium text-zinc-400 transition hover:text-zinc-200"
                        >
                            Clear
                        </button>
                    </div>
                    <input
                        type="time"
                        x-model="form.dueTime"
                        class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                    >
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    @click="closeModal()"
                    class="rounded-md bg-zinc-800 px-4 py-2 text-zinc-200 transition hover:bg-zinc-700"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-md bg-sky-500 px-4 py-2 text-zinc-950 transition hover:bg-sky-400"
                    x-text="editingTaskId ? 'Save Task' : 'Add Task'"
                ></button>
            </div>
        </form>
    </div>
</div>

<div
    x-cloak
    x-show="isArchiveConfirmOpen"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
    @keydown.escape.window="cancelArchiveTask()"
>
    <div x-cloak x-show="isArchiveConfirmOpen" x-transition class="w-full max-w-md rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow-lg shadow-black/40">
        <h2 class="mb-2 text-xl font-semibold text-zinc-100" x-text="(getTaskById(archiveTaskId)?.archived ? 'Restore Task' : 'Archive Task')"></h2>
        <p
            class="mb-6 text-sm text-zinc-400"
            x-text="getTaskById(archiveTaskId)?.archived
                ? 'Are you sure you want to restore this task to active tasks?'
                : 'Are you sure you want to archive this task?'"
        ></p>

        <div class="flex justify-end gap-2">
            <button
                type="button"
                @click="cancelArchiveTask()"
                class="rounded-md bg-zinc-800 px-4 py-2 text-zinc-200 transition hover:bg-zinc-700"
            >
                Cancel
            </button>
            <button
                type="button"
                @click="confirmArchiveTask()"
                class="rounded-md bg-amber-500 px-4 py-2 text-zinc-950 transition hover:bg-amber-400"
                x-text="getTaskById(archiveTaskId)?.archived ? 'Restore' : 'Archive'"
            ></button>
        </div>
    </div>
</div>

<div
    x-cloak
    x-show="isDeleteConfirmOpen"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
    @keydown.escape.window="cancelDeleteTask()"
>
    <div x-cloak x-show="isDeleteConfirmOpen" x-transition class="w-full max-w-md rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow-lg shadow-black/40">
        <h2 class="mb-2 text-xl font-semibold text-zinc-100">Delete Task</h2>
        <p class="mb-6 text-sm text-zinc-400">Are you sure you want to delete this task? This action cannot be undone.</p>

        <div class="flex justify-end gap-2">
            <button
                type="button"
                @click="cancelDeleteTask()"
                class="rounded-md bg-zinc-800 px-4 py-2 text-zinc-200 transition hover:bg-zinc-700"
            >
                Cancel
            </button>
            <button
                type="button"
                @click="confirmDeleteTask()"
                class="rounded-md bg-red-500 px-4 py-2 text-zinc-950 transition hover:bg-red-400"
            >
                Delete
            </button>
        </div>
    </div>
</div>
