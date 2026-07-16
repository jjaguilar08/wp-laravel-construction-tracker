<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#f9f4ed] leading-tight">
            {{ __('Set Savings Goal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-[32px] bg-[#201e1d] p-5 sm:p-8">
                <div class="rounded-[28px] bg-[#474238] p-6 shadow-[0_1px_2px_rgba(46,43,37,0.14)]">
                    <form method="POST" action="{{ route('savings-goals.store') }}" class="space-y-4">
                        @include('savings-goals._form')

                        <div class="flex justify-end gap-3 pt-1">
                            <a href="{{ route('savings-goals.index') }}" class="inline-flex items-center rounded-full border border-[#f9f4ed]/20 px-5 py-2.5 text-xs font-semibold uppercase tracking-widest text-[#f9f4ed]/70 transition hover:bg-[#f9f4ed]/10">Cancel</a>
                            <button type="submit"
                                class="inline-flex items-center rounded-full bg-[#f6a06b] px-5 py-2.5 font-['Caprasimo'] text-xs uppercase tracking-widest text-[#2e2b25] transition hover:bg-[#ffc6a5] active:bg-[#d67f48] focus:outline-none focus:ring-2 focus:ring-[#f6a06b] focus:ring-offset-2 focus:ring-offset-[#474238]">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
