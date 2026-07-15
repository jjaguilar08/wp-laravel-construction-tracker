<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Spending Trends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <p class="text-sm text-gray-500">Total Spend, Last 6 Months</p>
                <canvas id="trendChart" class="mt-4"></canvas>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <p class="text-sm text-gray-500 mb-4">Monthly Totals</p>

                {{-- Mobile: stacked cards, no horizontal scroll --}}
                <div class="space-y-3 sm:hidden">
                    @foreach ($monthlyTotals as $entry)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 text-sm">
                            <span class="font-medium text-gray-800">{{ $entry['month']->format('F Y') }}</span>
                            <span class="font-semibold">${{ number_format($entry['total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- sm and up: normal table --}}
                <div class="hidden overflow-hidden rounded-lg border border-gray-200 sm:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Month</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($monthlyTotals as $entry)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $entry['month']->format('F Y') }}</td>
                                        <td class="px-4 py-3 text-right">${{ number_format($entry['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyTotals->pluck('month')->map(fn ($month) => $month->format('M Y'))->values()),
                datasets: [{
                    label: 'Total Spend',
                    data: @json($monthlyTotals->pluck('total')->values()),
                    borderColor: '#4f46e5',
                    backgroundColor: '#4f46e5',
                    tension: 0.3,
                    pointRadius: 4,
                    fill: false,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    </script>
</x-app-layout>
