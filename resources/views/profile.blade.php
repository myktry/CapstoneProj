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

            <div class="p-4 sm:p-8 bg-zinc-900/80 border border-amber-500/20 shadow-2xl shadow-black/40 sm:rounded-2xl">
                <div class="max-w-3xl space-y-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Notifications</p>
                        <h3 class="mt-1 text-lg font-semibold text-white">Account alerts</h3>
                        <p class="mt-1 text-sm text-zinc-400">Refund updates and other account activity appear here.</p>
                    </div>

                    <div class="space-y-3">
                        @forelse (($notifications ?? collect()) as $notification)
                            <div class="rounded-xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                                        <p class="mt-1 text-sm text-zinc-300">{{ $notification->message }}</p>
                                    </div>

                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider {{ $notification->is_read ? 'bg-zinc-700 text-zinc-300' : 'bg-emerald-500/10 text-emerald-300' }}">
                                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                                    </span>
                                </div>

                                @if ($notification->created_at)
                                    <p class="mt-2 text-xs text-zinc-500">{{ $notification->created_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-400">
                                You do not have any account notifications yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-zinc-900/80 border border-red-500/20 shadow-2xl shadow-black/40 sm:rounded-2xl">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
