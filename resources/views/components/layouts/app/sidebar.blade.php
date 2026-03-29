<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <x-layouts.app.navbar />

        <div x-data="{ expandedSections: { status: true, priority: true, projects: true } }">
            <div
                x-show="$store.tasksApp.sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"
                @click="$store.tasksApp.closeSidebar()"
            ></div>

            <aside
                class="fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] w-64 overflow-y-auto border-r border-zinc-800 bg-zinc-900 shadow-lg transition-transform duration-300 ease-out lg:translate-x-0 lg:shadow-none"
                :class="$store.tasksApp.sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="p-6">
                    <div class="mb-8">
                        <button @click="expandedSections.status = !expandedSections.status" class="mb-4 flex w-full items-center justify-between px-2" type="button">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Task Status</h4>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform" :class="expandedSections.status ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <nav x-show="expandedSections.status" x-transition class="space-y-2">
                            <button
                                type="button"
                                @click="$store.tasksApp.setFilterStatus('All')"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="!$store.tasksApp.showArchived && $store.tasksApp.filterStatus === 'All' && $store.tasksApp.filterPriority === 'All' ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'"
                            >
                                <span class="text-lg">📋</span>
                                <span>All Tasks</span>
                                <span class="ml-auto text-xs text-zinc-400" x-text="$store.tasksApp.allCount"></span>
                            </button>

                            <button
                                type="button"
                                @click="$store.tasksApp.setFilterStatus('Not Started')"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="$store.tasksApp.filterStatus === 'Not Started' && !$store.tasksApp.showArchived ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'"
                            >
                                <span class="text-lg">⭕</span>
                                <span>Not Started</span>
                                <span class="ml-auto text-xs text-zinc-400" x-text="$store.tasksApp.notStartedCount"></span>
                            </button>

                            <button
                                type="button"
                                @click="$store.tasksApp.setFilterStatus('In Progress')"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="$store.tasksApp.filterStatus === 'In Progress' && !$store.tasksApp.showArchived ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'"
                            >
                                <span class="text-lg">⚡</span>
                                <span>In Progress</span>
                                <span class="ml-auto text-xs text-zinc-400" x-text="$store.tasksApp.inProgressCount"></span>
                            </button>

                            <button
                                type="button"
                                @click="$store.tasksApp.setFilterStatus('Completed')"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="$store.tasksApp.filterStatus === 'Completed' && !$store.tasksApp.showArchived ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'"
                            >
                                <span class="text-lg">✅</span>
                                <span>Completed</span>
                                <span class="ml-auto text-xs text-zinc-400" x-text="$store.tasksApp.completedCount"></span>
                            </button>

                            <button
                                type="button"
                                @click="$store.tasksApp.setFilterStatus('Archived')"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="$store.tasksApp.showArchived ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'"
                            >
                                <span class="text-lg">📦</span>
                                <span>Archived</span>
                                <span class="ml-auto text-xs text-zinc-400" x-text="$store.tasksApp.archivedCount"></span>
                            </button>
                        </nav>
                    </div>

                    <div class="mb-8 border-b border-zinc-800 pb-8">
                        <button @click="expandedSections.priority = !expandedSections.priority" class="mb-4 flex w-full items-center justify-between px-2" type="button">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Priority</h4>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform" :class="expandedSections.priority ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <nav x-show="expandedSections.priority" x-transition class="space-y-2">
                            <button type="button" @click="$store.tasksApp.setFilterPriority('High')" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition" :class="$store.tasksApp.filterPriority === 'High' ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                <span>High Priority</span>
                            </button>
                            <button type="button" @click="$store.tasksApp.setFilterPriority('Medium')" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition" :class="$store.tasksApp.filterPriority === 'Medium' ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'">
                                <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                                <span>Medium Priority</span>
                            </button>
                            <button type="button" @click="$store.tasksApp.setFilterPriority('Low')" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition" :class="$store.tasksApp.filterPriority === 'Low' ? 'bg-zinc-800 font-medium text-zinc-100' : 'text-zinc-300 hover:bg-zinc-800'">
                                <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                                <span>Low Priority</span>
                            </button>
                        </nav>
                    </div>

                    <div>
                        <button @click="expandedSections.projects = !expandedSections.projects" class="mb-4 flex w-full items-center justify-between px-2" type="button">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-zinc-500">Projects</h4>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform" :class="expandedSections.projects ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <nav x-show="expandedSections.projects" x-transition class="space-y-2">
                            <button type="button" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-zinc-300 transition hover:bg-zinc-800">
                                <span class="text-lg">🌐</span>
                                <span class="text-sm">Website Redesign</span>
                            </button>
                            <button type="button" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-zinc-300 transition hover:bg-zinc-800">
                                <span class="text-lg">📱</span>
                                <span class="text-sm">Mobile App</span>
                            </button>
                            <button type="button" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-zinc-300 transition hover:bg-zinc-800">
                                <span class="text-lg">📈</span>
                                <span class="text-sm">Marketing Q3</span>
                            </button>
                        </nav>
                    </div>
                </div>
            </aside>
        </div>

        <div class="pt-16 lg:pl-64">
            {{ $slot }}
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('tasksApp', {
                    storageKey: 'dashboard_tasks_v1',
                    hasInitialized: false,
                    sidebarOpen: false,
                    tasks: [],
                    statuses: ['Not Started', 'In Progress', 'Completed'],
                    searchValue: '',
                    filterStatus: 'All',
                    filterPriority: 'All',
                    showArchived: false,
                    viewMode: 'list',
                    isModalOpen: false,
                    editingTaskId: null,
                    form: {
                        title: '',
                        description: '',
                        priority: 'Medium',
                        dueDate: '',
                        dueTime: '',
                    },

                    init() {
                        if (this.hasInitialized) {
                            return;
                        }

                        const fromStorage = localStorage.getItem(this.storageKey);

                        if (fromStorage) {
                            try {
                                const parsed = JSON.parse(fromStorage);
                                this.tasks = this.normalizeTasks(Array.isArray(parsed) ? parsed : []);
                            } catch (error) {
                                this.tasks = [];
                            }

                            this.saveTasks();
                            this.hasInitialized = true;
                            return;
                        }

                        this.tasks = [
                            {
                                id: Date.now() + 1,
                                title: 'Finalize dashboard layout',
                                description: 'Build the initial responsive dashboard structure and align visual hierarchy.',
                                priority: 'High',
                                status: 'Not Started',
                                dueDate: '2026-04-02',
                                dueTime: '09:00',
                                archived: false,
                            },
                            {
                                id: Date.now() + 2,
                                title: 'Connect API integration',
                                description: 'Wire task endpoints and map response states into UI-ready collections.',
                                priority: 'Medium',
                                status: 'In Progress',
                                dueDate: '2026-04-04',
                                dueTime: '13:30',
                                archived: false,
                            },
                            {
                                id: Date.now() + 3,
                                title: 'Polish profile view',
                                description: 'Refine spacing, typography, and actions for the profile page experience.',
                                priority: 'Low',
                                status: 'Completed',
                                dueDate: '2026-03-30',
                                dueTime: '',
                                archived: false,
                            },
                        ];

                        this.saveTasks();
                        this.hasInitialized = true;
                    },

                    normalizeTasks(taskList) {
                        return taskList.map((task, index) => {
                            const resolvedId = task.id || task._id || Date.now() + index;

                            return {
                                ...task,
                                id: resolvedId,
                                archived: Boolean(task.archived),
                                priority: task.priority || 'Medium',
                                status: task.status || 'Not Started',
                                description: task.description || '',
                                dueDate: task.dueDate || '',
                                dueTime: task.dueTime || '',
                            };
                        });
                    },

                    resolveTaskId(task) {
                        return task.id || task._id;
                    },

                    saveTasks() {
                        this.tasks = this.normalizeTasks(this.tasks);
                        localStorage.setItem(this.storageKey, JSON.stringify(this.tasks));
                    },

                    toggleSidebar() {
                        this.sidebarOpen = !this.sidebarOpen;
                    },

                    closeSidebar() {
                        this.sidebarOpen = false;
                    },

                    setFilterStatus(val) {
                        this.filterPriority = 'All';

                        if (val === 'Archived') {
                            this.showArchived = true;
                            this.filterStatus = 'All';
                        } else {
                            this.showArchived = false;
                            this.filterStatus = val;
                        }

                        this.closeSidebar();
                    },

                    setFilterPriority(val) {
                        this.filterPriority = val;
                        this.filterStatus = 'All';
                        this.showArchived = false;
                        this.closeSidebar();
                    },

                    get activeTasks() {
                        return this.tasks.filter((task) => !task.archived);
                    },

                    get archivedTasks() {
                        return this.tasks.filter((task) => task.archived);
                    },

                    get allCount() {
                        return this.activeTasks.length;
                    },

                    get notStartedCount() {
                        return this.activeTasks.filter((task) => task.status === 'Not Started').length;
                    },

                    get inProgressCount() {
                        return this.activeTasks.filter((task) => task.status === 'In Progress').length;
                    },

                    get completedCount() {
                        return this.activeTasks.filter((task) => task.status === 'Completed').length;
                    },

                    get archivedCount() {
                        return this.archivedTasks.length;
                    },

                    get filteredActiveTasks() {
                        return this.activeTasks.filter((task) => {
                            const search = this.searchValue.toLowerCase();
                            const matchesSearch = task.title.toLowerCase().includes(search) || task.description.toLowerCase().includes(search);
                            const matchesStatus = this.filterStatus === 'All' || task.status === this.filterStatus;
                            const matchesPriority = this.filterPriority === 'All' || task.priority === this.filterPriority;

                            return matchesSearch && matchesStatus && matchesPriority;
                        });
                    },

                    get displayTasks() {
                        return this.showArchived ? this.archivedTasks : this.filteredActiveTasks;
                    },

                    countByStatus(status) {
                        return this.activeTasks.filter((task) => task.status === status).length;
                    },

                    addTask() {
                        this.editingTaskId = null;
                        this.form = {
                            title: '',
                            description: '',
                            priority: 'Medium',
                            dueDate: '',
                            dueTime: '',
                        };
                        this.isModalOpen = true;
                    },

                    editTask(taskId) {
                        const targetId = String(taskId);
                        const task = this.tasks.find((item) => String(this.resolveTaskId(item)) === targetId);

                        if (!task) {
                            return;
                        }

                        this.editingTaskId = task.id;
                        this.form = {
                            title: task.title || '',
                            description: task.description || '',
                            priority: task.priority || 'Medium',
                            dueDate: task.dueDate || '',
                            dueTime: task.dueTime || '',
                        };
                        this.isModalOpen = true;
                    },

                    closeModal() {
                        this.isModalOpen = false;
                        this.editingTaskId = null;
                    },

                    submitTask() {
                        if (!this.form.title || !this.form.dueDate) {
                            return;
                        }

                        if (this.editingTaskId) {
                            const targetId = String(this.editingTaskId);
                            const task = this.tasks.find((item) => String(this.resolveTaskId(item)) === targetId);

                            if (!task) {
                                return;
                            }

                            task.title = this.form.title.trim();
                            task.description = (this.form.description || '').trim();
                            task.priority = this.form.priority;
                            task.dueDate = this.form.dueDate;
                            task.dueTime = this.form.dueTime;
                        } else {
                            this.tasks.unshift({
                                id: Date.now(),
                                title: this.form.title.trim(),
                                description: (this.form.description || '').trim(),
                                priority: this.form.priority,
                                status: 'Not Started',
                                dueDate: this.form.dueDate,
                                dueTime: this.form.dueTime,
                                archived: false,
                            });
                        }

                        this.saveTasks();
                        this.closeModal();
                    },

                    archiveTask(taskId) {
                        const targetId = String(taskId);

                        this.tasks = this.tasks.map((item) => {
                            if (String(this.resolveTaskId(item)) !== targetId) {
                                return item;
                            }

                            return {
                                ...item,
                                archived: !item.archived,
                            };
                        });

                        this.saveTasks();
                    },

                    deleteTask(taskId) {
                        const targetId = String(taskId);
                        this.tasks = this.tasks.filter((item) => String(this.resolveTaskId(item)) !== targetId);
                        this.saveTasks();
                    },

                    changeStatus(taskOrId, nextStatus) {
                        if (typeof taskOrId === 'object' && taskOrId !== null) {
                            taskOrId.status = nextStatus;
                            this.tasks = [...this.tasks];
                            this.saveTasks();
                            return;
                        }

                        const targetId = typeof taskOrId === 'object'
                            ? this.resolveTaskId(taskOrId)
                            : taskOrId;

                        if (!targetId) {
                            return;
                        }

                        this.tasks = this.tasks.map((item) => {
                            if (String(this.resolveTaskId(item)) !== String(targetId)) {
                                return item;
                            }

                            return {
                                ...item,
                                status: nextStatus,
                            };
                        });

                        this.saveTasks();
                    },
                });
            });
        </script>

        @fluxScripts
    </body>
</html>
