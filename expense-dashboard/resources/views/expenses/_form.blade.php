@csrf

<div>
    <x-input-label for="amount" value="Amount" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
        value="{{ old('amount', $expense->amount ?? '') }}" required />
    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
</div>

<div>
    <x-input-label for="category" value="Category" />
    <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
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
    <x-input-label for="date" value="Date" />
    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
        value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : '') }}" required />
    <x-input-error :messages="$errors->get('date')" class="mt-2" />
</div>

<div>
    <x-input-label for="notes" value="Notes" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $expense->notes ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>
