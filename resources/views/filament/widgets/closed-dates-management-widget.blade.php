<x-filament-widgets::widget>
    <x-filament::section
        heading="Closed / Holiday Dates"
        description="Open the calendar, pick a date, then save a note for why it is unavailable."
    >
        <div class="rounded-2xl border border-amber-500/10 bg-[#0a0a0a] shadow-2xl shadow-black/20">
            <div class="flex flex-col gap-4 border-b border-amber-500/10 px-6 py-7 sm:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs uppercase tracking-[0.3em] text-amber-400">Reserve Your Time</p>
                        <h2 class="mt-3 text-3xl font-bold text-white">Closed / Holiday Dates</h2>
                        <p class="mt-2 max-w-2xl text-sm text-zinc-400">Select a date, assign its status, and leave a note so the booking calendar stays clear for customers.</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 text-xs font-medium text-zinc-300">
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1">1. Pick a date</span>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1">2. Choose status</span>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1">3. Save the note</span>
                </div>
            </div>
            <div class="bg-[#0a0a0a] px-6 py-6 sm:px-8 space-y-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.85fr)]">
                    <div class="rounded-2xl border border-amber-500/20 bg-zinc-900/60 p-4 sm:p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <button
                                type="button"
                                wire:click="previousMonth"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-amber-500/20 text-amber-300 transition hover:bg-amber-500/20 hover:text-amber-200"
                                aria-label="Previous month"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div class="text-center">
                                <p class="text-xs uppercase tracking-[0.3em] text-amber-400/70">Month</p>
                                <h4 class="mt-2 text-2xl font-bold text-white">{{ $this->monthYear }}</h4>
                            </div>

                            <button
                                type="button"
                                wire:click="nextMonth"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-amber-500/20 text-amber-300 transition hover:bg-amber-500/20 hover:text-amber-200"
                                aria-label="Next month"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-400/70">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                                <div class="py-1">{{ $weekday }}</div>
                            @endforeach
                        </div>

                        <div class="mt-2 grid grid-cols-7 gap-2">
                            @foreach($this->calendarDays as $dayData)
                                @if($dayData === null)
                                    <div class="aspect-square rounded-2xl border border-white/5 bg-white/[0.02]"></div>
                                @else
                                    <button
                                        type="button"
                                        wire:click="selectDate('{{ $dayData['date'] }}')"
                                        class="group relative aspect-square rounded-2xl border px-2 py-2 text-sm font-semibold transition duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400/50"
                                        @class([
                                            'border-white/10 bg-white/[0.03] text-zinc-200 hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-white' => ! $selectedDate || ($selectedDate !== $dayData['date'] && ! $dayData['isClosed'] && ! $dayData['isToday']),
                                            'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $dayData['isToday'] && $selectedDate !== $dayData['date'] && ! $dayData['isClosed'],
                                            'border-red-500/40 bg-red-500/15 text-red-200' => $dayData['isClosed'] && $selectedDate !== $dayData['date'],
                                            'border-amber-400 bg-amber-400 text-zinc-950 shadow-lg shadow-amber-500/20' => $selectedDate === $dayData['date'],
                                        ])
                                    >
                                        <span class="block text-base leading-none">{{ $dayData['day'] }}</span>
                                        <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.2em] opacity-70">
                                            {{ $dayData['isToday'] ? 'Today' : ($dayData['isClosed'] ? 'Closed' : 'Open') }}
                                        </span>

                                        <span
                                            class="absolute bottom-2 right-2 h-2.5 w-2.5 rounded-full transition"
                                            @class([
                                                'bg-emerald-400' => $dayData['isToday'] && $selectedDate !== $dayData['date'] && ! $dayData['isClosed'],
                                                'bg-red-400' => $dayData['isClosed'] && $selectedDate !== $dayData['date'],
                                                'bg-amber-500' => $selectedDate === $dayData['date'],
                                                'bg-white/20 group-hover:bg-amber-300' => ! $dayData['isToday'] && ! $dayData['isClosed'] && $selectedDate !== $dayData['date'],
                                            ])
                                        ></span>
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Today</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5"><span class="h-2 w-2 rounded-full bg-red-400"></span> Closed</span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Selected</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-5">
                            <p class="text-xs uppercase tracking-[0.3em] text-amber-400/70">Step-by-step</p>
                            <ol class="mt-3 space-y-3 text-sm text-zinc-300">
                                <li class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-300">1</span><span>Pick a day directly from the calendar grid.</span></li>
                                <li class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-300">2</span><span>Choose the status for that date.</span></li>
                                <li class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-300">3</span><span>Add a note and save the blocked date.</span></li>
                            </ol>
                        </div>

                        @if($selectedDate)
                            <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Selected date</p>
                                <p class="mt-2 text-xl font-bold text-white">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                                <p class="mt-1 text-sm text-amber-100/80">Update the availability for this date below.</p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-5">
                                <div class="grid gap-4">
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
                                            rows="5"
                                            placeholder="Add the reason for the unavailable date"
                                            class="w-full rounded-xl border border-white/10 bg-zinc-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-zinc-600 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap items-center gap-3">
                                    <button
                                        type="button"
                                        wire:click="saveDateStatus"
                                        class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-amber-500 to-amber-400 px-5 py-2.5 text-sm font-bold text-zinc-950 transition hover:shadow-lg hover:shadow-amber-500/30"
                                    >
                                        Save date
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="closeModal"
                                        class="inline-flex items-center justify-center rounded-full border border-white/10 px-5 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-white/20 hover:bg-white/5 hover:text-white"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] px-5 py-6 text-sm text-zinc-400">
                                Select a date to reveal the editor and save the unavailable-day reason.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
</x-filament-widgets::widget>
