<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="mb-1 block text-xs text-[#f9f4ed]/70">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="block w-full rounded-full border border-[#f9f4ed]/15 bg-[#2e2b25] px-4 py-2.5 text-sm text-[#f9f4ed] focus:border-[#f6a06b] focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/30">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end gap-4 pt-1">
            <a class="text-sm text-[#f9f4ed]/60 hover:text-[#f6a06b] hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/40" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit"
                class="inline-flex items-center rounded-full bg-[#f6a06b] px-5 py-2.5 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>
