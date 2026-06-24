<x-app-layout>
    @php
        $adminCount = \App\Models\User::query()->where('role', 'admin')->count();
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{ __("You're logged in!") }}
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900">Admin Panel</h3>
                        @if (auth()->user()?->role === 'admin')
                            <p class="mt-2 text-sm text-gray-600">Open the Filament admin dashboard to manage calendar closures, services, and gallery content.</p>
                            <a
                                href="{{ url('/admin') }}"
                                class="mt-4 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                            >
                                Open Admin Dashboard
                            </a>
                        @else
                            <p class="mt-2 text-sm text-gray-600">Your account is not marked as admin yet.</p>
                            <p class="mt-1 text-sm text-gray-500">Use <span class="font-mono">php artisan admin:user</span> to create or update an admin account.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
