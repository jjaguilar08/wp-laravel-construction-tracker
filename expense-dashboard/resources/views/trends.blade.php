<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#f9f4ed] leading-tight">
            {{ __('Spending Trends') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-[32px] bg-[#201e1d] p-5 sm:p-8 space-y-5">
                <div class="rounded-[28px] bg-[#474238] p-6 shadow-[0_1px_2px_rgba(46,43,37,0.14)]">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[#f9f4ed]/60">Total Spend, Last 6 Periods</p>
                    <canvas id="trendChart" class="mt-4"></canvas>
                </div>

                <div class="rounded-[28px] bg-[#474238] p-6 shadow-[0_1px_2px_rgba(46,43,37,0.14)]">
                    <p class="mb-4 text-[10px] font-semibold uppercase tracking-[0.1em] text-[#f9f4ed]/60">Period Totals</p>

                    {{-- Mobile: stacked cards, no horizontal scroll --}}
                    <div class="space-y-3 sm:hidden">
                        @foreach ($periodTotals as $entry)
                            <div class="flex items-center justify-between rounded-2xl border border-[#f9f4ed]/10 bg-[#2e2b25] p-4 text-sm">
                                <span class="font-medium text-[#f9f4ed]">{{ $entry['label'] }}</span>
                                <span class="font-semibold text-[#ffc6a5]">{{ money($entry['total']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- sm and up: normal table --}}
                    <div class="hidden overflow-hidden rounded-2xl border border-[#f9f4ed]/10 sm:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Period</th>
                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f9f4ed]/8">
                                    @foreach ($periodTotals as $entry)
                                        <tr class="hover:bg-[#f9f4ed]/[0.06]">
                                            <td class="whitespace-nowrap px-4 py-3 text-[#f9f4ed]/70">{{ $entry['label'] }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-[#ffc6a5]">{{ money($entry['total']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                labels: @json($periodTotals->pluck('shortLabel')->values()),
                datasets: [{
                    label: 'Total Spend',
                    data: @json($periodTotals->pluck('total')->values()),
                    borderColor: '#f6a06b',
                    backgroundColor: '#f6a06b',
                    tension: 0.3,
                    pointRadius: 4,
                    fill: false,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(249,244,237,0.12)' } },
                    x: { grid: { display: false } },
                },
            },
        });
    </script>
</x-app-layout>
