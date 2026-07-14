<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} — Help</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased">
        <div class="mx-auto max-w-3xl px-6 py-10">
            <h1 class="text-2xl font-semibold">Help</h1>
            <p class="mt-1 text-sm text-gray-500">Onboarding and how-to-use articles.</p>

            @if ($error)
                <div class="mt-8 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $error }}
                </div>
            @else
                <div class="mt-8 space-y-8">
                    @forelse ($articles as $article)
                        <article class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            @if ($article['featured_image'])
                                <img src="{{ $article['featured_image'] }}" alt="" class="h-48 w-full object-cover">
                            @endif

                            <div class="p-6">
                                <h2 class="text-lg font-semibold">{{ $article['title'] }}</h2>
                                <div class="prose prose-sm mt-3 max-w-none text-gray-600">
                                    {!! $article['content'] !!}
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-400">No help articles found.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </body>
</html>
