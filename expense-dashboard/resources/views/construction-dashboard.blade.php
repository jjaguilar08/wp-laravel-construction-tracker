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
                <form method="GET" action="{{ route('construction.dashboard') }}" class="mt-8 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4">
                    <div>
                        <label for="category" class="block text-xs font-medium text-gray-500">Category</label>
                        <select name="category" id="category" class="mt-1 rounded-md border-gray-300 text-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $option)
                                <option value="{{ $option }}" @selected(($filters['category'] ?? '') === $option)>
                                    {{ ucfirst($option) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="from" class="block text-xs font-medium text-gray-500">From</label>
                        <input type="date" name="from" id="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 rounded-md border-gray-300 text-sm">
                    </div>

                    <div>
                        <label for="to" class="block text-xs font-medium text-gray-500">To</label>
                        <input type="date" name="to" id="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 rounded-md border-gray-300 text-sm">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                        <a href="{{ route('construction.dashboard') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600">Clear</a>
                    </div>
                </form>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
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
                                <p class="text-sm text-gray-400">No entries match these filters.</p>
                            @endforelse
                        </dl>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2">
                        <p class="text-sm text-gray-500">Category Spend</p>
                        @if ($categoryTotals->isNotEmpty())
                            <canvas id="categoryChart" class="mt-4"></canvas>
                        @else
                            <p class="mt-2 text-sm text-gray-400">Nothing to chart yet.</p>
                        @endif
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="overflow-x-auto">
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
                </div>
            @endif
        </div>

        @if (! $error && $categoryTotals->isNotEmpty())
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
            <script>
                new Chart(document.getElementById('categoryChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($categoryTotals->keys()->map(fn ($category) => ucfirst($category))),
                        datasets: [{
                            label: 'Spend by Category',
                            data: @json($categoryTotals->values()),
                            backgroundColor: '#4f46e5',
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } },
                    },
                });
            </script>
        @endif
    </body>
</html>
