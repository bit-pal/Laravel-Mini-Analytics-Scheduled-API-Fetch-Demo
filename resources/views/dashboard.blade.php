<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>{{ __("You're logged in!") }}</div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('stats') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-medium hover:bg-gray-800">
                            Open statistics
                        </a>

                        <a href="{{ url('/api/api-fetches') }}"
                           class="inline-flex items-center px-4 py-2 bg-white text-gray-900 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                            View API fetch JSON
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
