@csrf

<div>
    <x-input-label for="month" value="Month" />
    <x-text-input id="month" name="month" type="month" class="mt-1 block w-full"
        value="{{ old('month', isset($incomeExpectation) ? $incomeExpectation->month->format('Y-m') : '') }}" required />
    <x-input-error :messages="$errors->get('month')" class="mt-2" />
</div>

<div>
    <x-input-label for="expected_amount" value="Expected Amount" />
    <x-text-input id="expected_amount" name="expected_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
        value="{{ old('expected_amount', $incomeExpectation->expected_amount ?? '') }}" required />
    <x-input-error :messages="$errors->get('expected_amount')" class="mt-2" />
</div>
