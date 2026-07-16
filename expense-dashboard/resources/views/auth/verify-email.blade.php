<x-guest-layout>
    <div class="mb-4 text-sm text-[#f9f4ed]/70">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-full border border-[#7a8a5e]/30 bg-[#f0fae1] px-4 py-3 text-sm text-[#56633f]">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#f6a06b] px-5 py-2.5 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-[#f9f4ed]/60 hover:text-[#f6a06b] hover:underline rounded-md focus:outline-none focus:ring-2 focus:ring-[#f6a06b]/40">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
