@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#f6a06b] text-start text-base font-medium text-[#f6a06b] bg-[#f6a06b]/10 focus:outline-none focus:text-[#ffc6a5] focus:bg-[#f6a06b]/15 focus:border-[#f6a06b] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#f9f4ed]/60 hover:text-[#f9f4ed] hover:bg-[#f9f4ed]/5 hover:border-[#f9f4ed]/20 focus:outline-none focus:text-[#f9f4ed] focus:bg-[#f9f4ed]/5 focus:border-[#f9f4ed]/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
