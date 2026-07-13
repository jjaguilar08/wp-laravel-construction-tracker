<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Set Savings Goal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">
                <form method="POST" action="{{ route('savings-goals.store') }}" class="space-y-6">
                    @include('savings-goals._form')

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('savings-goals.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600">Cancel</a>
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
