@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-3 text-center">
    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-zinc-500">Todo App</p>
    <h1 class="text-2xl font-semibold tracking-tight text-zinc-100">{{ $title }}</h1>
    <p class="text-center text-sm text-zinc-400">{{ $description }}</p>
</div>
