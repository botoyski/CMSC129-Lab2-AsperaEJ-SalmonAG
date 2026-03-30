@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div x-data="{ isProfileModalOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="mx-auto w-full max-w-3xl p-6 md:p-8">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-zinc-100">Profile</h1>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex rounded-md border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800"
            >
                Back to Dashboard
            </a>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="mb-4 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                Profile updated successfully.
            </div>
        @endif

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
                    <p class="font-medium text-zinc-100">{{ auth()->user()->created_at?->format('F j, Y') }}</p>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3">
                <button
                    type="button"
                    @click="isProfileModalOpen = true"
                    class="inline-flex rounded-md border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800"
                >
                    Edit Profile
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex rounded-md border border-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 transition hover:border-zinc-500 hover:bg-zinc-800">
                        Log Out
                    </button>
                </form>
            </div>
        </div>

        <div
            x-cloak
            x-show="isProfileModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
            @keydown.escape.window="isProfileModalOpen = false"
        >
            <div x-cloak x-show="isProfileModalOpen" x-transition class="w-full max-w-lg rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow-lg shadow-black/40">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-100">Edit Profile</h2>
                    <p class="mt-1 text-sm text-zinc-400">Update your editable profile details below.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-300">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', auth()->user()->name) }}"
                            class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-300">Email address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            class="mt-1 block w-full rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-200 outline-none focus:border-sky-500"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-zinc-300">Role</label>
                        <input
                            id="role"
                            type="text"
                            value="Productivity Member"
                            class="mt-1 block w-full cursor-not-allowed rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-400 outline-none"
                            disabled
                        >
                    </div>

                    <div>
                        <label for="joined" class="block text-sm font-medium text-zinc-300">Joined</label>
                        <input
                            id="joined"
                            type="text"
                            value="{{ auth()->user()->created_at?->format('F j, Y') }}"
                            class="mt-1 block w-full cursor-not-allowed rounded-md border border-zinc-700 bg-zinc-950 p-2 text-zinc-400 outline-none"
                            disabled
                        >
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="isProfileModalOpen = false"
                            class="rounded-md bg-zinc-800 px-4 py-2 text-zinc-200 transition hover:bg-zinc-700"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-sky-500 px-4 py-2 text-zinc-950 transition hover:bg-sky-400"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
