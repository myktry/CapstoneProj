<x-filament-widgets::widget>
    <x-filament::section
        heading="Closed / Holiday Dates"
        description="Open the calendar, pick a date, then save a note for why it is unavailable."
    >
        <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-4">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white">Tracking calendar</p>
                <p class="mt-1 text-sm text-zinc-400">Use the calendar button to mark unavailable dates.</p>
            </div>

            <button
                type="button"
                wire:click="openModal"
                class="inline-flex h-11 items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/20 hover:text-amber-200"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Open Calendar</span>
            </button>
        </div>

        <x-modal name="closed-dates-calendar" maxWidth="2xl" focusable>
            <div class="border-b border-white/10 bg-zinc-950 px-6 py-5 sm:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-400">Calendar Setup</p>
                        <h2 class="mt-2 text-2xl font-bold text-white">Block unavailable dates</h2>
                        <p class="mt-1 text-sm text-zinc-400">Pick a day, add a reason, and save it to the closed dates list.</p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-full border border-white/10 p-2 text-zinc-400 transition hover:border-white/20 hover:bg-white/5 hover:text-white"
                        aria-label="Close calendar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-6 bg-zinc-950 px-6 py-6 sm:px-8">
                <div class="rounded-2xl border border-white/10 bg-zinc-900/80 p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            wire:click="previousMonth"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-zinc-300 transition hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-300"
                            aria-label="Previous month"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-zinc-500">Month</p>
                            <h3 class="mt-1 text-xl font-bold text-white">{{ $this->monthYear }}</h3>
                        </div>

                        <button
                            type="button"
                            wire:click="nextMonth"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-zinc-300 transition hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-300"
                            aria-label="Next month"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-500">
                        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <div class="py-1">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="mt-2 grid grid-cols-7 gap-2">
                        @foreach($this->calendarDays as $dayData)
                            @if($dayData === null)
                                <div class="aspect-square rounded-xl border border-dashed border-white/5 bg-white/[0.02]"></div>
                            @else
                                <button
                                    type="button"
                                    wire:click="selectDate('{{ $dayData['date'] }}')"
                                    class="aspect-square rounded-xl border px-2 py-2 text-sm font-semibold transition duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
                                    @class([
                                        'border-white/10 bg-white/[0.03] text-zinc-200 hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-200' => ! $selectedDate || $selectedDate !== $dayData['date'] && ! $dayData['isClosed'] && ! $dayData['isToday'],
                                        'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $dayData['isToday'] && $selectedDate !== $dayData['date'] && ! $dayData['isClosed'],
                                        'border-red-500/40 bg-red-500/15 text-red-200' => $dayData['isClosed'] && $selectedDate !== $dayData['date'],
                                        'border-amber-400 bg-amber-400 text-zinc-950 shadow-lg shadow-amber-500/20' => $selectedDate === $dayData['date'],
                                    ])
                                >
                                    <span class="block text-base leading-none">{{ $dayData['day'] }}</span>
                                    <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.2em] opacity-70">
                                        {{ $dayData['isToday'] ? 'Today' : ($dayData['isClosed'] ? 'Closed' : 'Open') }}
                                    </span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if($selectedDate)
                    <div class="grid gap-4 rounded-2xl border border-white/10 bg-zinc-900/80 p-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-zinc-500">Selected date</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">Status</label>
                                <select
                                    wire:model="selectedStatus"
                                    class="w-full rounded-xl border border-white/10 bg-zinc-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                                >
                                    <option value="open">Open (available)</option>
                                    <option value="closed">Closed</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-300">Note</label>
                                <textarea
                                    wire:model="note"
                                    rows="4"
                                    placeholder="Add the reason for the unavailable date"
                                    class="w-full rounded-xl border border-white/10 bg-zinc-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-zinc-600 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                wire:click="saveDateStatus"
                                class="inline-flex items-center justify-center rounded-full bg-amber-400 px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-amber-300"
                            >
                                Save date
                            </button>

                            <button
                                type="button"
                                wire:click="closeModal"
                                class="inline-flex items-center justify-center rounded-full border border-white/10 px-5 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-white/20 hover:bg-white/5 hover:text-white"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] px-5 py-6 text-sm text-zinc-400">
                        Select a date to reveal the note input and save the unavailable-day reason.
                    </div>
                @endif

                <div class="flex items-center gap-4 text-xs text-zinc-500">
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Today</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-red-400"></span> Closed</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Selected</span>
                </div>
            </div>
        </x-modal>
    </x-filament::section>
</x-filament-widgets::widget>
