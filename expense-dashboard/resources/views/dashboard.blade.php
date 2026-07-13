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

            <div class="grid gap-6 sm:grid-cols-3">
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

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <p class="text-sm text-gray-500">Spending by Category</p>
                    @if ($categoryTotals->isNotEmpty())
                        <canvas id="categoryChart" class="mt-4"></canvas>
                    @else
                        <p class="mt-2 text-sm text-gray-400">No expenses logged this month yet.</p>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
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
        </div>
    </div>

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
