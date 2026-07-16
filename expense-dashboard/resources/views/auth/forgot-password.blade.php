<x-guest-layout>
    <div class="mb-4 text-sm text-[#f9f4ed]/70">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end pt-1">
            <button type="submit"
                class="inline-flex items-center rounded-full bg-[#f6a06b] px-5 py-2.5 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
