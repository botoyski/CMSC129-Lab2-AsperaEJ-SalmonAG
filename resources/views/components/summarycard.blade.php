@props([
    'icon' => '📊',
    'label' => 'Summary',
    'value' => 0,
    'color' => 'blue',
    'trend' => null,
])

@php
    $colorClasses = [
        'blue' => 'bg-blue-500/15 text-blue-300',
        'gray' => 'bg-zinc-800 text-zinc-300',
        'green' => 'bg-emerald-500/15 text-emerald-300',
        'orange' => 'bg-amber-500/15 text-amber-300',
    ];

    $iconClass = $colorClasses[$color] ?? $colorClasses['blue'];
    $xValue = $attributes->get('x-value');
    $xTrend = $attributes->get('x-trend');
@endphp

<div class="rounded-lg border border-zinc-800 bg-zinc-900 p-6 shadow-sm shadow-black/20 transition-all hover:border-zinc-700 hover:shadow-md hover:shadow-black/30">
    <div class="mb-4 flex items-center justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg text-xl {{ $iconClass }}">
            {{ $icon }}
        </div>

        @if (! is_null($xTrend))
            <span
                class="text-xs font-medium"
                :class="({{ $xTrend }}) > 0 ? 'text-emerald-300' : 'text-zinc-400'"
                x-text="`${({{ $xTrend }}) > 0 ? '↑ ' : ''}${Math.abs(Number({{ $xTrend }}))} added today`"
            ></span>
        @elseif (! is_null($trend))
            <span class="text-xs font-medium {{ $trend > 0 ? 'text-emerald-300' : 'text-zinc-400' }}">
                {{ $trend > 0 ? '↑' : '' }} {{ abs((int) $trend) }} added today
            </span>
        @endif
    </div>

    <p class="mb-2 text-sm font-medium text-zinc-400">{{ $label }}</p>
    <p class="text-3xl font-bold text-zinc-100" @if ($xValue) x-text="{{ $xValue }}" @endif>{{ $value }}</p>
</div>
