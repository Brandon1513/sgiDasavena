@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-[28px] border border-white/80 bg-white/70 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl']) }}>
    @if($title || $subtitle || isset($header))
    <div class="flex items-start justify-between gap-3 px-6 pt-6">
        <div>
            @if($title)<h2 class="font-display text-base font-bold text-indigo-950">{{ $title }}</h2>@endif
            @if($subtitle)<p class="mt-0.5 text-xs text-gray-400">{{ $subtitle }}</p>@endif
        </div>
        @isset($header){{ $header }}@endisset
    </div>
    @endif
    {{ $slot }}
</div>
