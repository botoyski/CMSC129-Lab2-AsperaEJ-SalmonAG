<nav class="fixed left-0 right-0 top-0 z-50 h-16 border-b border-zinc-800 bg-zinc-900/95 shadow-sm shadow-black/20 backdrop-blur">
    <div class="flex h-full items-center justify-between px-4 lg:px-6">
        <div class="flex items-center gap-4">
            <button type="button" @click="$store.tasksApp?.toggleSidebar()" class="rounded-lg p-2 transition-colors hover:bg-zinc-800 lg:hidden" aria-label="Open menu">
                <svg class="h-6 w-6 text-zinc-200" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
                <div class="h-8 w-8 overflow-hidden rounded-lg">
                    <x-app-logo class="size-8"></x-app-logo>
                </div>
                <h1 class="hidden text-xl font-bold text-zinc-100 sm:block">Move it!</h1>
            </a>
        </div>

        <div class="flex items-center gap-6">
            <div
                x-data="{
                    currentTime: '',
                    updateTime() {
                        this.currentTime = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
                    },
                    init() {
                        this.updateTime();
                        setInterval(() => this.updateTime(), 60000);
                    }
                }"
                x-text="currentTime"
                class="hidden text-sm font-medium text-zinc-400 sm:block"
            ></div>

            <button type="button" class="relative rounded-lg p-2 transition-colors hover:bg-zinc-800" aria-label="Notifications">
                <svg class="h-5 w-5 text-zinc-200" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15 17H5.5C6.2 16.3 7 15 7 13V10.5C7 7.5 8.8 5.3 11.5 4.7V4C11.5 3.2 12.2 2.5 13 2.5C13.8 2.5 14.5 3.2 14.5 4V4.7C17.2 5.3 19 7.5 19 10.5V13C19 15 19.8 16.3 20.5 17H15ZM11 18.5C11.2 19.9 12.4 21 14 21C15.6 21 16.8 19.9 17 18.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-red-500"></span>
            </button>

            <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg border-l border-zinc-800 pl-4 transition-colors hover:bg-zinc-800" wire:navigate>
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-zinc-100">Juan Dela Cruz</p>
                    <p class="text-xs text-zinc-400">Productivity</p>
                </div>
                <img
                    src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop"
                    alt="Profile"
                    class="h-9 w-9 rounded-full object-cover transition-all hover:ring-2 hover:ring-blue-400"
                >
            </a>
        </div>
    </div>
</nav>
