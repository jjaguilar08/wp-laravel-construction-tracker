<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }} — Construction Expense Dashboard</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased">
        <div class="mx-auto max-w-5xl px-6 py-10">
            <h1 class="text-2xl font-semibold">Construction Expense Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Data pulled live from the WordPress Construction Tracker plugin.</p>

            @if ($error)
                <div class="mt-8 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $error }}
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <p class="text-sm text-gray-500">Total Spend</p>
                        <p class="mt-1 text-3xl font-semibold">${{ number_format($totalSpend, 2) }}</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <p class="text-sm text-gray-500">By Category</p>
                        <dl class="mt-2 space-y-1">
                            @forelse ($categoryTotals as $category => $amount)
                                <div class="flex justify-between text-sm">
                                    <dt class="capitalize text-gray-600">{{ $category }}</dt>
                                    <dd class="font-medium">${{ number_format($amount, 2) }}</dd>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">No entries yet.</p>
                            @endforelse
                        </dl>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Title</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Amount</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($entries as $entry)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $entry['entry_date'] }}</td>
                                    <td class="px-4 py-3 font-medium">{{ html_entity_decode($entry['title']) }}</td>
                                    <td class="px-4 py-3 capitalize text-gray-600">{{ $entry['category'] }}</td>
                                    <td class="px-4 py-3 text-right">${{ number_format($entry['amount'], 2) }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ html_entity_decode($entry['notes'] ?? '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </body>
</html>
