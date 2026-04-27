<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <x-layouts.app.navbar />

        <div x-data="{ expandedSections: { status: true, priority: true, ai: true, projects: true } }">
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

                    <div class="mb-8 border-b border-zinc-800 pb-8">
                        <button @click="expandedSections.ai = !expandedSections.ai" class="mb-4 flex w-full items-center justify-between px-2" type="button">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-zinc-500">AI Assistant</h4>
                            <svg class="h-4 w-4 text-zinc-500 transition-transform" :class="expandedSections.ai ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <nav x-show="expandedSections.ai" x-transition class="space-y-2">
                            <button
                                type="button"
                                @click="$store.tasksApp.openChatModal(); $store.tasksApp.closeSidebar()"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-zinc-300 transition hover:bg-zinc-800"
                            >
                                <span class="text-lg">🤖</span>
                                <span>AI Chatbot</span>
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
            @yield('content')
        </div>

        <div x-data>
            <button
                type="button"
                @click="$store.tasksApp.toggleChatModal()"
                class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full border border-zinc-700 bg-zinc-900 text-zinc-100 shadow-lg shadow-black/30 transition hover:-translate-y-0.5 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                aria-label="Open chat"
            >
                <svg class="h-8 w-8 text-zinc-100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="4" y="6" width="16" height="12" rx="4" stroke="currentColor" stroke-width="1.6" />
                    <circle cx="9" cy="12" r="1" fill="currentColor" />
                    <circle cx="15" cy="12" r="1" fill="currentColor" />
                    <path d="M9 15.5H15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    <path d="M12 3V6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
            </button>

            <div
                x-show="$store.tasksApp.isChatModalOpen"
                x-transition
                class="fixed inset-0 z-40 bg-black/50"
                @click="$store.tasksApp.closeChatModal()"
            ></div>

            <div
                x-show="$store.tasksApp.isChatModalOpen"
                x-transition
                class="fixed bottom-24 right-6 z-50 flex h-96 w-80 flex-col overflow-hidden rounded-lg border border-zinc-700 bg-zinc-900 shadow-2xl shadow-black/50 sm:w-96 md:h-[32rem]"
            >
            <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-800/50 px-4 py-3">
                <h2 class="text-sm font-semibold text-zinc-100">AI Chat Assistant</h2>
                <button
                    type="button"
                    @click="$store.tasksApp.closeChatModal()"
                    class="rounded p-1 transition-colors hover:bg-zinc-700"
                    aria-label="Close chat"
                >
                    <svg class="h-5 w-5 text-zinc-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4">
                <div class="space-y-4">
                    <div class="flex justify-start">
                        <div class="max-w-xs rounded-lg bg-zinc-800 px-3 py-2 text-sm text-zinc-100">
                            Hi there! How can I help you today?
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-800 bg-zinc-800/50 px-4 py-3">
                <div class="flex gap-2">
                    <input
                        type="text"
                        placeholder="Type your message..."
                        class="flex-1 rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-blue-500 focus:outline-none"
                    />
                    <button
                        type="button"
                        class="rounded bg-zinc-700 px-3 py-2 text-sm text-zinc-100 transition hover:bg-zinc-600 disabled:opacity-50"
                        disabled
                        aria-label="Send message"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.99 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L4.13399899,1.01401515 C3.34915502,0.9 2.40734225,1.00636533 1.77946707,1.4776575 C0.994623095,2.10604706 0.837654326,3.0486314 1.15159189,3.99040561 L3.03521743,10.4314005 C3.03521743,10.5884979 3.34915502,10.7455953 3.50612381,10.7455953 L16.6915026,11.5310822 C16.6915026,11.5310822 17.1624089,11.5310822 17.1624089,12.0023744 C17.1624089,12.4744748 16.6915026,12.4744748 16.6915026,12.4744748 Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('tasksApp', {
                    hasInitialized: false,
                    sidebarOpen: false,
                    tasks: [],
                    categories: [],
                    statuses: ['Not Started', 'In Progress', 'Completed'],
                    descriptionMaxLength: 100,
                    searchValue: '',
                    searchDebounceId: null,
                    filterStatus: 'All',
                    filterPriority: 'All',
                    filterCategoryId: 'All',
                    showArchived: false,
                    viewMode: 'list',
                    isLoading: false,
                    pagination: {
                        currentPage: 1,
                        lastPage: 1,
                        perPage: 9,
                        total: 0,
                    },
                    counts: {
                        all: 0,
                        not_started: 0,
                        in_progress: 0,
                        completed: 0,
                        archived: 0,
                        added_today: 0,
                    },
                    isModalOpen: false,
                    isArchiveConfirmOpen: false,
                    archiveTaskId: null,
                    isDeleteConfirmOpen: false,
                    deleteTaskId: null,
                    formErrors: {},
                    toastMessage: '',
                    toastVisible: false,
                    toastTimeoutId: null,
                    editingTaskId: null,
                    isChatModalOpen: false,
                    form: {
                        title: '',
                        description: '',
                        status: 'Not Started',
                        priority: 'Medium',
                        dueDate: '',
                        dueTime: '',
                        categoryId: '',
                    },

                    init() {
                        if (this.hasInitialized) {
                            return;
                        }
                        this.hasInitialized = true;
                        this.fetchTasks(1);
                    },

                    csrfToken() {
                        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    },

                    buildHeaders(extra = {}) {
                        return {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...extra,
                        };
                    },

                    buildQueryParams(page = 1) {
                        const params = new URLSearchParams({
                            page: String(page),
                            search: this.searchValue,
                            status: this.filterStatus,
                            priority: this.filterPriority,
                            view: this.showArchived ? 'trash' : 'active',
                            per_page: String(this.pagination.perPage),
                        });

                        if (this.filterCategoryId !== 'All') {
                            params.set('category_id', this.filterCategoryId);
                        }

                        return params;
                    },

                    async fetchTasks(page = 1) {
                        this.isLoading = true;

                        try {
                            const response = await fetch(`/tasks?${this.buildQueryParams(page).toString()}`, {
                                headers: this.buildHeaders(),
                            });

                            if (!response.ok) {
                                throw new Error('Could not fetch tasks.');
                            }

                            const payload = await response.json();
                            this.tasks = Array.isArray(payload.data) ? payload.data : [];
                            this.categories = Array.isArray(payload.categories) ? payload.categories : [];
                            this.pagination = {
                                currentPage: Number(payload.pagination?.current_page || 1),
                                lastPage: Number(payload.pagination?.last_page || 1),
                                perPage: Number(payload.pagination?.per_page || 9),
                                total: Number(payload.pagination?.total || 0),
                            };
                            this.counts = {
                                ...this.counts,
                                ...(payload.counts || {}),
                            };
                        } catch (error) {
                            this.showToast('Unable to load tasks right now.');
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    queueSearch() {
                        if (this.searchDebounceId) {
                            clearTimeout(this.searchDebounceId);
                        }

                        this.searchDebounceId = setTimeout(() => {
                            this.fetchTasks(1);
                        }, 300);
                    },

                    resolveTaskId(task) {
                        return task?.id ?? task?._id;
                    },

                    getTaskById(taskId) {
                        const targetId = String(taskId);

                        return this.tasks.find((item) => String(this.resolveTaskId(item)) === targetId) || null;
                    },

                    toggleSidebar() {
                        this.sidebarOpen = !this.sidebarOpen;
                    },

                    closeSidebar() {
                        this.sidebarOpen = false;
                    },

                    toggleChatModal() {
                        this.isChatModalOpen = !this.isChatModalOpen;
                    },

                    openChatModal() {
                        this.isChatModalOpen = true;
                    },

                    closeChatModal() {
                        this.isChatModalOpen = false;
                    },

                    setFilterStatus(val) {
                        this.filterCategoryId = 'All';

                        if (val === 'Archived') {
                            this.showArchived = true;
                            this.filterStatus = 'All';
                        } else {
                            this.showArchived = false;
                            this.filterStatus = val;
                        }

                        this.fetchTasks(1);
                        this.closeSidebar();
                    },

                    setFilterPriority(val) {
                        this.filterPriority = val;
                        this.filterStatus = 'All';
                        this.showArchived = false;
                        this.fetchTasks(1);
                        this.closeSidebar();
                    },

                    setFilterCategory(val) {
                        this.filterCategoryId = val;
                        this.showArchived = false;
                        this.fetchTasks(1);
                    },

                    toggleArchived() {
                        this.showArchived = !this.showArchived;
                        this.fetchTasks(1);
                    },

                    get allCount() {
                        return this.counts.all;
                    },

                    get addedTodayCount() {
                        return this.counts.added_today;
                    },

                    get notStartedCount() {
                        return this.counts.not_started;
                    },

                    get inProgressCount() {
                        return this.counts.in_progress;
                    },

                    get completedCount() {
                        return this.counts.completed;
                    },

                    get archivedCount() {
                        return this.counts.archived;
                    },

                    get displayTasks() {
                        return this.tasks;
                    },

                    countByStatus(status) {
                        if (status === 'Not Started') {
                            return this.notStartedCount;
                        }

                        if (status === 'In Progress') {
                            return this.inProgressCount;
                        }

                        if (status === 'Completed') {
                            return this.completedCount;
                        }

                        return 0;
                    },

                    goToPage(page) {
                        if (page < 1 || page > this.pagination.lastPage || this.isLoading) {
                            return;
                        }

                        this.fetchTasks(page);
                    },

                    addTask() {
                        this.editingTaskId = null;
                        this.formErrors = {};
                        this.form = {
                            title: '',
                            description: '',
                            status: 'Not Started',
                            priority: 'Medium',
                            dueDate: '',
                            dueTime: '',
                            categoryId: '',
                        };
                        this.isModalOpen = true;
                    },

                    editTask(taskId) {
                        const targetId = String(taskId);
                        const task = this.tasks.find((item) => String(this.resolveTaskId(item)) === targetId);

                        if (!task) {
                            return;
                        }

                        this.formErrors = {};
                        this.editingTaskId = task.id;
                        this.form = {
                            title: task.title || '',
                            description: String(task.description || '').slice(0, this.descriptionMaxLength),
                            status: task.status || 'Not Started',
                            priority: task.priority || 'Medium',
                            dueDate: task.dueDate || '',
                            dueTime: task.dueTime || '',
                            categoryId: task.categoryId ? String(task.categoryId) : '',
                        };
                        this.isModalOpen = true;
                    },

                    closeModal() {
                        this.isModalOpen = false;
                        this.editingTaskId = null;
                        this.formErrors = {};
                    },

                    showToast(message) {
                        this.toastMessage = message;
                        this.toastVisible = true;

                        if (this.toastTimeoutId) {
                            clearTimeout(this.toastTimeoutId);
                        }

                        this.toastTimeoutId = setTimeout(() => {
                            this.toastVisible = false;
                            this.toastMessage = '';
                        }, 2200);
                    },

                    requestDeleteTask(taskId) {
                        if (!taskId) {
                            return;
                        }

                        this.deleteTaskId = String(taskId);
                        this.isDeleteConfirmOpen = true;
                    },

                    cancelDeleteTask() {
                        this.isDeleteConfirmOpen = false;
                        this.deleteTaskId = null;
                    },

                    async confirmDeleteTask() {
                        if (!this.deleteTaskId) {
                            return;
                        }

                        await this.deleteTask(this.deleteTaskId);
                        this.cancelDeleteTask();
                    },

                    collectFormErrors(errorBag) {
                        const reduced = {};

                        Object.entries(errorBag || {}).forEach(([field, messages]) => {
                            reduced[field] = Array.isArray(messages) ? messages[0] : String(messages);
                        });

                        return reduced;
                    },

                    buildTaskPayload(statusOverride = null) {
                        return {
                            title: this.form.title.trim(),
                            description: this.form.description,
                            status: statusOverride || this.form.status,
                            priority: this.form.priority,
                            due_date: this.form.dueDate,
                            due_time: this.form.dueTime || null,
                            category_id: this.form.categoryId ? Number(this.form.categoryId) : null,
                        };
                    },

                    async submitTask() {
                        this.formErrors = {};

                        if (!this.form.title || !this.form.dueDate) {
                            this.formErrors = {
                                title: !this.form.title ? 'A task title is required.' : '',
                                due_date: !this.form.dueDate ? 'Please set a due date.' : '',
                            };
                            return;
                        }

                        try {
                            const targetId = this.editingTaskId ? String(this.editingTaskId) : null;
                            const response = await fetch(targetId ? `/tasks/${targetId}` : '/tasks', {
                                method: targetId ? 'PUT' : 'POST',
                                headers: this.buildHeaders({
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                }),
                                body: JSON.stringify(this.buildTaskPayload()),
                            });

                            if (response.status === 422) {
                                const payload = await response.json();
                                this.formErrors = this.collectFormErrors(payload.errors);
                                return;
                            }

                            if (!response.ok) {
                                throw new Error('Could not save task.');
                            }

                            await this.fetchTasks(1);
                            this.closeModal();
                            this.showToast(targetId ? 'Task successfully updated' : 'Task successfully created');
                        } catch (error) {
                            this.showToast('Unable to save this task right now.');
                        }
                    },

                    requestArchiveTask(taskId) {
                        if (!taskId) {
                            return;
                        }

                        this.archiveTaskId = String(taskId);
                        this.isArchiveConfirmOpen = true;
                    },

                    cancelArchiveTask() {
                        this.isArchiveConfirmOpen = false;
                        this.archiveTaskId = null;
                    },

                    async confirmArchiveTask() {
                        if (!this.archiveTaskId) {
                            return;
                        }

                        const task = this.getTaskById(this.archiveTaskId);
                        const wasArchived = Boolean(task?.archived);

                        await this.archiveTask(this.archiveTaskId);
                        this.cancelArchiveTask();
                        this.showToast(wasArchived ? 'Task successfully restored' : 'Task successfully archived');
                    },

                    async archiveTask(taskId) {
                        const targetId = String(taskId);

                        const task = this.getTaskById(targetId);

                        if (!task) {
                            return;
                        }

                        try {
                            const endpoint = task.archived ? `/tasks/${targetId}/restore` : `/tasks/${targetId}`;
                            const method = task.archived ? 'PATCH' : 'DELETE';
                            const response = await fetch(endpoint, {
                                method,
                                headers: this.buildHeaders({
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                }),
                            });

                            if (!response.ok) {
                                throw new Error('Could not archive/restore task.');
                            }

                            await this.fetchTasks(this.pagination.currentPage);
                        } catch (error) {
                            this.showToast('Unable to update archive state right now.');
                        }
                    },

                    async deleteTask(taskId) {
                        const targetId = String(taskId);

                        try {
                            const endpoint = this.showArchived ? `/tasks/${targetId}/force` : `/tasks/${targetId}`;
                            const response = await fetch(endpoint, {
                                method: 'DELETE',
                                headers: this.buildHeaders({
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                }),
                            });

                            if (!response.ok) {
                                throw new Error('Could not delete task.');
                            }

                            if (this.showArchived) {
                                this.showToast('Task permanently deleted');
                            } else {
                                this.showToast('Task moved to trash');
                            }

                            const requestedPage = this.tasks.length === 1 && this.pagination.currentPage > 1
                                ? this.pagination.currentPage - 1
                                : this.pagination.currentPage;

                            await this.fetchTasks(requestedPage);
                        } catch (error) {
                            this.showToast('Unable to delete task right now.');
                        }
                    },

                    async changeStatus(taskOrId, nextStatus) {
                        const targetId = typeof taskOrId === 'object'
                            ? this.resolveTaskId(taskOrId)
                            : taskOrId;

                        if (!targetId || !this.statuses.includes(nextStatus)) {
                            return;
                        }

                        const task = this.getTaskById(targetId);

                        if (!task || task.archived) {
                            return;
                        }

                        try {
                            const response = await fetch(`/tasks/${targetId}`, {
                                method: 'PUT',
                                headers: this.buildHeaders({
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken(),
                                }),
                                body: JSON.stringify({
                                    title: task.title,
                                    description: task.description,
                                    status: nextStatus,
                                    priority: task.priority,
                                    due_date: task.dueDate,
                                    due_time: task.dueTime || null,
                                    category_id: task.categoryId || null,
                                }),
                            });

                            if (!response.ok) {
                                throw new Error('Could not update status.');
                            }

                            await this.fetchTasks(this.pagination.currentPage);
                        } catch (error) {
                            this.showToast('Unable to update task status.');
                        }
                    },
                });
            });
        </script>

        @fluxScripts
    </body>
</html>
