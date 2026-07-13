@csrf

<div>
    <x-input-label for="month" value="Month" />
    <x-text-input id="month" name="month" type="month" class="mt-1 block w-full"
        value="{{ old('month', isset($savingsGoal) ? $savingsGoal->month->format('Y-m') : '') }}" required />
    <x-input-error :messages="$errors->get('month')" class="mt-2" />
</div>

<div>
    <x-input-label for="target_amount" value="Target Amount" />
    <x-text-input id="target_amount" name="target_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
        value="{{ old('target_amount', $savingsGoal->target_amount ?? '') }}" required />
    <x-input-error :messages="$errors->get('target_amount')" class="mt-2" />
</div>
