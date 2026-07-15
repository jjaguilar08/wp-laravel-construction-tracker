<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Expenses') }}
            </h2>
            <a href="{{ route('expenses.create') }}" class="inline-block rounded-md bg-gray-900 px-4 py-2 text-center text-sm font-medium text-white">
                Add Expense
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($expenses->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-white px-4 py-6 text-center text-gray-400">
                    No expenses yet.
                </div>
            @else
                {{-- Mobile: stacked cards, one per expense, no horizontal scroll --}}
                <div class="space-y-3 sm:hidden">
                    @foreach ($expenses as $expense)
                        <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm">
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
                            <div class="mt-3 flex justify-end gap-3 border-t border-gray-100 pt-3">
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- sm and up: normal table --}}
                <div class="hidden overflow-hidden rounded-lg border border-gray-200 bg-white sm:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Amount</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Notes</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $expense->date->format('Y-m-d') }}</td>
                                        <td class="px-4 py-3 capitalize text-gray-600">{{ $expense->category }}</td>
                                        <td class="px-4 py-3 text-right">${{ number_format($expense->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $expense->notes }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:underline">Edit</a>
                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
