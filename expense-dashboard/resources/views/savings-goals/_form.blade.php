@csrf

<div>
    <label for="month" class="mb-1 block text-xs text-[#f9f4ed]/70">Month</label>
    <input id="month" name="month" type="month"
        value="{{ old('month', isset($savingsGoal) ? $savingsGoal->month->format('Y-m') : '') }}" required
        class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
    <x-input-error :messages="$errors->get('month')" class="mt-2" />
</div>

<div>
    <label for="target_amount" class="mb-1 block text-xs text-[#f9f4ed]/70">Target Amount</label>
    <input id="target_amount" name="target_amount" type="number" step="0.01" min="0"
        value="{{ old('target_amount', $savingsGoal->target_amount ?? '') }}" required
        class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
    <x-input-error :messages="$errors->get('target_amount')" class="mt-2" />
</div>
