@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-full border border-[#7a8a5e]/30 bg-[#f0fae1] px-4 py-3 text-sm text-[#56633f]']) }}>
        {{ $status }}
    </div>
@endif
