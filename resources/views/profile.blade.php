<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Account</p>
            <h2 class="mt-1 font-semibold text-2xl text-white leading-tight">
            {{ __('Profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-white dark:bg-zinc-950 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Information Section -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-zinc-800 sm:rounded-lg">
                <div class="px-4 py-6 sm:px-6">
                    <div class="max-w-2xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-zinc-800 sm:rounded-lg">
                <div class="px-4 py-6 sm:px-6">
                    <div class="max-w-2xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="overflow-hidden bg-white shadow-sm dark:border-red-900/30 dark:bg-zinc-800 sm:rounded-lg border border-red-500/20">
                <div class="px-4 py-6 sm:px-6">
                    <div class="max-w-2xl">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
