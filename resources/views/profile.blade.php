<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Account</p>
            <h2 class="mt-1 font-semibold text-2xl text-white leading-tight">
            {{ __('Profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-zinc-950 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-zinc-900/80 border border-white/10 shadow-2xl shadow-black/40 sm:rounded-2xl">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-zinc-900/80 border border-white/10 shadow-2xl shadow-black/40 sm:rounded-2xl">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-zinc-900/80 border border-white/10 shadow-2xl shadow-black/40 sm:rounded-2xl">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
