@csrf

<div>
    <label for="amount" class="mb-1 block text-xs text-[#f9f4ed]/70">Amount</label>
    <input id="amount" name="amount" type="number" step="0.01" min="0.01"
        value="{{ old('amount', $expense->amount ?? '') }}" required
        class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
</div>

<div>
    <label for="category" class="mb-1 block text-xs text-[#f9f4ed]/70">Category</label>
    <select id="category" name="category" required
        class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
        <option value="">Select a category</option>
        @foreach ($categories as $option)
            <option value="{{ $option }}" @selected(old('category', $expense->category ?? '') === $option)>
                {{ ucfirst($option) }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('category')" class="mt-2" />
</div>

<div>
    <label for="date" class="mb-1 block text-xs text-[#f9f4ed]/70">Date</label>
    <input id="date" name="date" type="date"
        value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : '') }}" required
        class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
    <x-input-error :messages="$errors->get('date')" class="mt-2" />
</div>

<div>
    <label for="notes" class="mb-1 block text-xs text-[#f9f4ed]/70">Notes</label>
    <textarea id="notes" name="notes" rows="3"
        class="block w-full rounded-2xl border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">{{ old('notes', $expense->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>
