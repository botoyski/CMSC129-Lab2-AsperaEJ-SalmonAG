<a
    {{ $attributes->merge(['class' => 'text-sm font-medium text-sky-300 underline decoration-sky-400/40 underline-offset-2 transition duration-300 ease-out hover:text-sky-200 hover:decoration-sky-300']) }}
    wire:navigate
>
    {{ $slot }}
</a>
