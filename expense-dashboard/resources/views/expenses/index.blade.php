<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-[#f9f4ed] leading-tight">
                {{ __('Expenses') }}
            </h2>
            <a href="{{ route('expenses.create') }}" class="inline-flex items-center justify-center rounded-full bg-[#f6a06b] px-4 py-2 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48]">
                Add Expense
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-[32px] bg-[#201e1d] p-5 sm:p-8 space-y-4">
                @if (session('status'))
                    <div class="rounded-full border border-[#7a8a5e]/30 bg-[#f0fae1] px-4 py-3 text-sm text-[#56633f]">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($expenses->isEmpty())
                    <div class="rounded-[28px] bg-[#474238] px-4 py-6 text-center text-[#f9f4ed]/45">
                        No expenses yet.
                    </div>
                @else
                    {{-- Mobile: stacked cards, one per expense, no horizontal scroll --}}
                    <div class="space-y-3 sm:hidden">
                        @foreach ($expenses as $expense)
                            <div class="rounded-2xl border border-[#f9f4ed]/10 bg-[#2e2b25] p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#f9f4ed]">{{ $expense->date->format('Y-m-d') }}</span>
                                    <span class="font-semibold text-[#ffc6a5]">{{ money($expense->amount) }}</span>
                                </div>
                                <dl class="mt-2 space-y-1">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-[#f9f4ed]/50">Category</dt>
                                        <dd class="capitalize text-[#f9f4ed]/70">{{ $expense->category }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-[#f9f4ed]/50">Notes</dt>
                                        <dd class="text-right text-[#f9f4ed]/70">{{ $expense->notes }}</dd>
                                    </div>
                                </dl>
                                <div class="mt-3 flex justify-end gap-3 border-t border-[#f9f4ed]/10 pt-3">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="text-[#f6a06b] hover:text-[#ffc6a5] hover:underline">Edit</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#c96a55] hover:text-[#e08a72] hover:underline">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- sm and up: normal table --}}
                    <div class="hidden overflow-hidden rounded-2xl border border-[#f9f4ed]/10 sm:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Date</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Category</th>
                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Amount</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#f9f4ed]/50">Notes</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f9f4ed]/8">
                                    @foreach ($expenses as $expense)
                                        <tr class="hover:bg-[#f9f4ed]/[0.06]">
                                            <td class="whitespace-nowrap px-4 py-3 text-[#f9f4ed]/70">{{ $expense->date->format('Y-m-d') }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full bg-[#402310] px-2.5 py-0.5 text-xs capitalize text-[#ffc6a5]">
                                                    {{ $expense->category }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-[#ffc6a5]">{{ money($expense->amount) }}</td>
                                            <td class="px-4 py-3 text-[#f9f4ed]/60">{{ $expense->notes }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right space-x-3">
                                                <a href="{{ route('expenses.edit', $expense) }}" class="text-[#f6a06b] hover:text-[#ffc6a5] hover:underline">Edit</a>
                                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[#c96a55] hover:text-[#e08a72] hover:underline">Delete</button>
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
    </div>
</x-app-layout>
