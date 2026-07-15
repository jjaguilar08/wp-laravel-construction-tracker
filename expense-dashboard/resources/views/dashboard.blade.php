<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }} — {{ $month->format('F Y') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500">Total Spent This Month</p>
                    <p class="mt-1 text-3xl font-semibold">${{ number_format($totalSpent, 2) }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500">Expected Income</p>
                    @if ($incomeExpectation)
                        <p class="mt-1 text-3xl font-semibold">${{ number_format($incomeExpectation->expected_amount, 2) }}</p>
                    @else
                        <p class="mt-1 text-sm text-gray-500">
                            You haven't set expected income for this month yet.
                        </p>
                        <a href="{{ route('income-expectations.create') }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">
                            Set expected income &rarr;
                        </a>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500">Savings Goal</p>
                    @if ($savingsGoal)
                        <p class="mt-1 text-3xl font-semibold">${{ number_format($savingsGoal->target_amount, 2) }}</p>

                        @if ($savingsProgress !== null)
                            <div class="mt-3">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $savingsProgress }}%"></div>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ $savingsProgress }}% of goal
                                    ({{ $actualSavings < 0 ? '-' : '' }}${{ number_format(abs($actualSavings), 2) }}
                                    {{ $actualSavings < 0 ? 'over budget' : 'saved so far' }})
                                </p>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-gray-500">
                                Set your expected income to see progress toward this goal.
                            </p>
                            <a href="{{ route('income-expectations.create') }}" class="mt-1 inline-block text-sm font-medium text-indigo-600 hover:underline">
                                Set expected income &rarr;
                            </a>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-gray-500">
                            You haven't set a savings goal for this month yet.
                        </p>
                        <a href="{{ route('savings-goals.create') }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">
                            Set a savings goal &rarr;
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500">Spending by Category</p>
                    @if ($categoryTotals->isNotEmpty())
                        <canvas id="categoryChart" class="mt-4"></canvas>
                    @else
                        <p class="mt-2 text-sm text-gray-400">No expenses logged this month yet.</p>
                    @endif
                </div>

                <div id="quick-add-expense" class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500 mb-4">Quick Add Expense</p>

                    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="amount" value="Amount" />
                                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                    value="{{ old('amount') }}" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="date" value="Date" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                    value="{{ old('date', now()->toDateString()) }}" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="category" value="Category" />
                            <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Select a category</option>
                                @foreach ($categories as $option)
                                    <option value="{{ $option }}" @selected(old('category') === $option)>
                                        {{ ucfirst($option) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notes (optional)" />
                            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" value="{{ old('notes') }}" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500 hover:underline">View all expenses &rarr;</a>
                            <x-primary-button>Add Expense</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">Recent Expenses</p>
                    <a href="{{ route('expenses.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all &rarr;</a>
                </div>

                @if ($recentExpenses->isEmpty())
                    <p class="text-sm text-gray-500">
                        You haven't logged any expenses yet.
                    </p>
                    <a href="{{ route('expenses.create') }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">
                        Add an expense &rarr;
                    </a>
                @else
                    {{-- Mobile: stacked cards, no horizontal scroll --}}
                    <div class="space-y-3 sm:hidden">
                        @foreach ($recentExpenses as $expense)
                            <div class="rounded-lg border border-gray-200 p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">{{ $expense->date->format('Y-m-d') }}</span>
                                    <span class="font-semibold">${{ number_format($expense->amount, 2) }}</span>
                                </div>
                                <dl class="mt-2 space-y-1">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">Category</dt>
                                        <dd class="capitalize text-gray-600">{{ $expense->category }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-gray-500">Notes</dt>
                                        <dd class="text-right text-gray-600">{{ $expense->notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>

                    {{-- sm and up: normal table --}}
                    <div class="hidden overflow-hidden rounded-lg border border-gray-200 sm:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                                        <th class="px-4 py-3 text-right font-medium text-gray-500">Amount</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($recentExpenses as $expense)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $expense->date->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3 capitalize text-gray-600">{{ $expense->category }}</td>
                                            <td class="px-4 py-3 text-right">${{ number_format($expense->amount, 2) }}</td>
                                            <td class="px-4 py-3 text-gray-500">{{ $expense->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mobile-only quick-access FAB: jumps to the Quick Add Expense form
         already on the page instead of duplicating it in a modal - desktop
         shows that form inline without scrolling, so it has no FAB. --}}
    <a href="#quick-add-expense"
       class="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-gray-800 text-white shadow-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:hidden"
       aria-label="Jump to quick add expense form">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
    </a>

    @if ($categoryTotals->isNotEmpty())
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
</x-app-layout>
