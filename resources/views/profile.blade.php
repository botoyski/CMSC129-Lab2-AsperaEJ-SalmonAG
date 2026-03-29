<x-layouts.app>
    <div class="mx-auto w-full max-w-3xl p-6 md:p-8">
        <h1 class="mb-4 text-3xl font-bold text-zinc-100">Profile</h1>

        <div class="rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow">
            <div class="mb-6 flex items-center gap-6">
                <img
                    src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop"
                    alt="Profile"
                    class="h-20 w-20 rounded-full object-cover"
                >
                <div>
                    <p class="text-xl font-semibold text-zinc-100">{{ auth()->user()->name }}</p>
                    <p class="text-zinc-400">Productivity Member</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-zinc-400">Email</p>
                    <p class="font-medium text-zinc-100">{{ auth()->user()->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-zinc-400">Role</p>
                    <p class="font-medium text-zinc-100">Productivity Member</p>
                </div>
                <div>
                    <p class="text-sm text-zinc-400">Joined</p>
                    <p class="font-medium text-zinc-100">January 1, 2025</p>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3">
                <a href="{{ url('/settings/profile') }}" class="inline-flex rounded-md border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800" wire:navigate>
                    Edit Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex rounded-md border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
