<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Savings Goals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('savings-goals.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Set Savings Goal
                </a>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Month</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Target Amount</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($savingsGoals as $savingsGoal)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $savingsGoal->month->format('F Y') }}</td>
                                    <td class="px-4 py-3 text-right">${{ number_format($savingsGoal->target_amount, 2) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('savings-goals.edit', $savingsGoal) }}" class="text-indigo-600 hover:underline">Edit</a>
                                        <form action="{{ route('savings-goals.destroy', $savingsGoal) }}" method="POST" class="inline" onsubmit="return confirm('Delete this goal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">No savings goals set yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
