@props(['icon', 'title', 'desc', 'value', 'theme' => 'purple'])

@php
    $themes = [
        'purple' => ['bg' => 'bg-dasavena-purple/10', 'border' => 'border-dasavena-purple/15', 'text' => 'text-dasavena-purple'],
        'gold'   => ['bg' => 'bg-dasavena-gold/15',    'border' => 'border-dasavena-gold/25',    'text' => 'text-dasavena-gold-dark'],
    ];
    $t = $themes[$theme] ?? $themes['purple'];
@endphp

<div class="flex items-center justify-between gap-3 rounded-2xl border border-white/80 bg-white/50 p-4 shadow-[0_2px_8px_rgba(0,0,0,0.02),inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl">
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $t['bg'] }} border {{ $t['border'] }} {{ $t['text'] }} shadow-inner">
            <i class="ti {{ $icon }} text-[17px]"></i>
        </div>
        <div class="min-w-0">
            <h3 class="text-[13px] font-semibold text-indigo-950">{{ $title }}</h3>
            <p class="text-[11px] leading-snug text-gray-400">{{ $desc }}</p>
        </div>
    </div>
    <span class="shrink-0 font-mono text-lg font-extrabold {{ $t['text'] }}">{{ $value ?? '—' }}</span>
</div>
