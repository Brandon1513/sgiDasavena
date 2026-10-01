@props(['icon', 'value', 'label', 'desc' => null, 'theme' => 'purple', 'href' => null])

@php
    $themes = [
        'purple' => ['bg' => 'bg-dasavena-purple/10', 'border' => 'border-dasavena-purple/15', 'text' => 'text-dasavena-purple'],
        'gold'   => ['bg' => 'bg-dasavena-gold/15',    'border' => 'border-dasavena-gold/25',    'text' => 'text-dasavena-gold-dark'],
        'green'  => ['bg' => 'bg-emerald-50',          'border' => 'border-emerald-200',          'text' => 'text-emerald-600'],
        'rose'   => ['bg' => 'bg-rose-50',             'border' => 'border-rose-200',             'text' => 'text-rose-600'],
        'amber'  => ['bg' => 'bg-amber-50',            'border' => 'border-amber-200',            'text' => 'text-amber-600'],
    ];
    $t = $themes[$theme] ?? $themes['purple'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    class="group relative flex flex-col justify-between overflow-hidden rounded-[24px] border border-white/80 bg-white/70 p-5 shadow-[0_8px_24px_rgba(74,30,82,0.05),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(74,30,82,0.1),inset_0_1px_1px_rgba(255,255,255,1)]">
    <div class="pointer-events-none absolute -bottom-6 -right-6 h-24 w-24 rounded-full {{ $t['bg'] }} blur-xl transition-transform group-hover:scale-125"></div>

    <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $t['bg'] }} border {{ $t['border'] }} {{ $t['text'] }} shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)]">
        <i class="ti {{ $icon }} text-[22px]"></i>
    </div>

    <div class="mt-4">
        <div class="font-mono text-3xl font-extrabold tracking-tight {{ $t['text'] }}" data-count="{{ $value }}">0</div>
        <div class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $label }}</div>
    </div>

    @if($desc)
    <div class="mt-3 flex items-center justify-between border-t border-black/[0.04] pt-3">
        <span class="text-[11.5px] leading-snug text-gray-400">{{ $desc }}</span>
        @if($href)<i class="ti ti-arrow-narrow-right text-[15px] text-outline transition-transform group-hover:translate-x-0.5"></i>@endif
    </div>
    @endif
</{{ $tag }}>
