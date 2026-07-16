@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#f6a06b] text-sm font-medium leading-5 text-[#f9f4ed] focus:outline-none focus:border-[#ffc6a5] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#f9f4ed]/60 hover:text-[#f9f4ed] hover:border-[#f9f4ed]/25 focus:outline-none focus:text-[#f9f4ed] focus:border-[#f9f4ed]/25 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
