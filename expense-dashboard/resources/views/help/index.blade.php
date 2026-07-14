<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Help') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">Onboarding and how-to-use articles.</p>

            @if ($error)
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $error }}
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @forelse ($articles as $article)
                        <article class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            @if ($article['featured_image'])
                                <img src="{{ $article['featured_image'] }}" alt="" class="h-48 w-full object-cover">
                            @endif

                            <div class="p-6">
                                <h3 class="text-lg font-semibold">{{ $article['title'] }}</h3>
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
    </div>
</x-app-layout>
