<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|caprasimo:400&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#f9f4ed] antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#201e1d]">
            <div>
                <a href="/" class="font-['Caprasimo'] text-2xl tracking-wide text-[#f6a06b]">
                    Ledger
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 rounded-[28px] bg-[#474238] px-6 py-6 shadow-[0_1px_2px_rgba(46,43,37,0.14)] overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
