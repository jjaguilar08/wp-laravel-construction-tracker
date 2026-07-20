<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ledger') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|caprasimo:400&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#201e1d] text-[#f9f4ed]">

    {{-- Nav --}}
    <header class="border-b border-[#f9f4ed]/10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <span class="font-['Caprasimo'] text-2xl text-[#f9f4ed]">Ledger</span>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold px-5 py-2 rounded-full bg-[#f6a06b] text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#201e1d]">
                        Go to dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm px-4 py-2 rounded-full text-[#f9f4ed]/60 hover:text-[#f6a06b]">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm font-semibold px-5 py-2 rounded-full bg-[#f6a06b] text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#201e1d]">
                            Get started
                        </a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="max-w-6xl mx-auto px-6 pt-20 pb-16 text-center">
        <p class="text-sm tracking-widest uppercase mb-4 text-[#f6a06b]">Personal finance, kept simple</p>
        <h1 class="font-['Caprasimo'] text-5xl sm:text-6xl leading-tight mb-6 text-[#f9f4ed]">
            Know where every<br class="hidden sm:block"> dollar is going.
        </h1>
        <p class="max-w-xl mx-auto text-lg mb-10 text-[#f9f4ed]/60">
            Ledger tracks your expenses, expected income, and savings goals in one
            place &mdash; so month-end never catches you off guard.
        </p>
        <div class="flex items-center justify-center gap-4 flex-col sm:flex-row">
            @guest
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full sm:w-auto text-center font-semibold px-8 py-3 rounded-full bg-[#f6a06b] text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#201e1d]">
                        Create free account
                    </a>
                @endif
                <a href="{{ route('login') }}" class="w-full sm:w-auto text-center px-8 py-3 rounded-full border border-[#f9f4ed]/10 text-[#f9f4ed]/60 hover:text-[#f9f4ed]">
                    Log in
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto text-center font-semibold px-8 py-3 rounded-full bg-[#f6a06b] text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#201e1d]">
                    Go to dashboard
                </a>
            @endguest
        </div>
    </section>

    {{-- Dashboard preview mock (static sample data, not live) --}}
    <section class="max-w-5xl mx-auto px-6 pb-24">
        <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-2xl p-6 sm:p-8 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <span class="text-sm text-[#f9f4ed]/60">July 2026 overview</span>
                <span class="font-['Caprasimo'] text-xs px-3 py-1 rounded-full bg-[#2e2b25] text-[#f6a06b]">Sample data</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-[#2e2b25] rounded-xl p-4">
                    <p class="text-xs mb-1 text-[#f9f4ed]/60">Spent this month</p>
                    <p class="font-['Caprasimo'] text-2xl text-[#f9f4ed]">$2,140</p>
                </div>
                <div class="bg-[#2e2b25] rounded-xl p-4">
                    <p class="text-xs mb-1 text-[#f9f4ed]/60">Expected income</p>
                    <p class="font-['Caprasimo'] text-2xl text-[#f9f4ed]">$3,600</p>
                </div>
                <div class="bg-[#2e2b25] rounded-xl p-4">
                    <p class="text-xs mb-1 text-[#f9f4ed]/60">Savings goal</p>
                    <p class="font-['Caprasimo'] text-2xl text-[#f9f4ed]">$2,000</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-[#2e2b25] rounded-xl p-4">
                    <p class="text-xs mb-3 text-[#f9f4ed]/60">Spending by category</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-20 text-[#f9f4ed]/60">Housing</span>
                            <div class="flex-1 h-2 rounded-full bg-[#f9f4ed]/10"><div class="h-2 rounded-full bg-[#f6a06b]" style="width: 85%"></div></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-20 text-[#f9f4ed]/60">Food</span>
                            <div class="flex-1 h-2 rounded-full bg-[#f9f4ed]/10"><div class="h-2 rounded-full bg-[#f6a06b]" style="width: 55%"></div></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-20 text-[#f9f4ed]/60">Bills</span>
                            <div class="flex-1 h-2 rounded-full bg-[#f9f4ed]/10"><div class="h-2 rounded-full bg-[#f6a06b]" style="width: 40%"></div></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs w-20 text-[#f9f4ed]/60">Other</span>
                            <div class="flex-1 h-2 rounded-full bg-[#f9f4ed]/10"><div class="h-2 rounded-full bg-[#f6a06b]" style="width: 22%"></div></div>
                        </div>
                    </div>
                </div>
                <div class="bg-[#2e2b25] rounded-xl p-4">
                    <p class="text-xs mb-3 text-[#f9f4ed]/60">Savings progress</p>
                    <div class="h-2 rounded-full mb-2 bg-[#f9f4ed]/10"><div class="h-2 rounded-full bg-[#f6a06b]" style="width: 72%"></div></div>
                    <p class="text-sm text-[#f9f4ed]">$1,460 saved so far</p>
                    <p class="text-xs mt-4 mb-3 text-[#f9f4ed]/60">Recent expenses</p>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-[#f9f4ed]/60">Groceries</span><span class="text-[#f9f4ed]">$64.20</span></div>
                        <div class="flex justify-between"><span class="text-[#f9f4ed]/60">Electric bill</span><span class="text-[#f9f4ed]">$88.00</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature highlights --}}
    <section class="max-w-6xl mx-auto px-6 pb-24">
        <h2 class="font-['Caprasimo'] text-3xl text-center mb-12 text-[#f9f4ed]">Everything your budget needs</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">Expense tracking</h3>
                <p class="text-sm text-[#f9f4ed]/60">Log every expense by category, date, and note &mdash; see exactly where your money goes.</p>
            </div>

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v-2m0-8a9 9 0 100 18 9 9 0 000-18z"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">Income expectations</h3>
                <p class="text-sm text-[#f9f4ed]/60">Set what you expect to earn each month and measure actual spending against it.</p>
            </div>

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">Savings goals</h3>
                <p class="text-sm text-[#f9f4ed]/60">Set a monthly target and watch a live progress bar as you save toward it.</p>
            </div>

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0a2 2 0 002 2h2a2 2 0 002-2v-3a2 2 0 00-2-2h-2a2 2 0 00-2 2v3z"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">One-glance dashboard</h3>
                <p class="text-sm text-[#f9f4ed]/60">Totals, category breakdown, and savings progress for the current month, all in one view.</p>
            </div>

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l4-4 4 4 5-6"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">6-month trends</h3>
                <p class="text-sm text-[#f9f4ed]/60">See your spending trend over the last six months to catch patterns early.</p>
            </div>

            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-xl p-6">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-4 bg-[#2e2b25] text-[#f6a06b]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="font-semibold mb-2 text-[#f9f4ed]">Private by default</h3>
                <p class="text-sm text-[#f9f4ed]/60">Your data is scoped to your account only &mdash; nothing is shared across users.</p>
            </div>

        </div>
    </section>

    {{-- CTA band --}}
    @guest
        <section class="max-w-6xl mx-auto px-6 pb-24">
            <div class="bg-[#474238] border border-[#f9f4ed]/10 rounded-2xl p-10 sm:p-14 text-center">
                <h2 class="font-['Caprasimo'] text-3xl mb-4 text-[#f9f4ed]">Start tracking in under a minute</h2>
                <p class="mb-8 text-[#f9f4ed]/60">No credit card, no spreadsheet. Just sign up and add your first expense.</p>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-block font-semibold px-8 py-3 rounded-full bg-[#f6a06b] text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                        Create free account
                    </a>
                @endif
            </div>
        </section>
    @endguest

    {{-- Footer --}}
    <footer class="border-t border-[#f9f4ed]/10">
        <div class="max-w-6xl mx-auto px-6 py-8 flex items-center justify-between gap-4 text-sm text-[#f9f4ed]/60">
            <span class="font-['Caprasimo'] text-[#f9f4ed]">Ledger</span>
            <a href="https://github.com/jjaguilar08/personal-finance-tracker" target="_blank" rel="noopener" class="hover:underline text-[#f9f4ed]/60">GitHub</a>
        </div>
    </footer>

</body>
</html>
