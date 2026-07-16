<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-[#f9f4ed]/25 bg-[#2e2b25] text-[#f6a06b] focus:ring-[#f6a06b]/40 focus:ring-offset-0">
                <span class="ms-2 text-sm text-[#f9f4ed]/70">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-4 pt-1">
            @if (Route::has('password.request'))
                <a class="text-sm text-[#f9f4ed]/60 hover:text-[#f6a06b] hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/40" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit"
                class="inline-flex items-center rounded-full bg-[#f6a06b] px-5 py-2.5 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</x-guest-layout>
