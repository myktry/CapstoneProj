<div class="w-full">
    <!-- Calendar Header with Month/Year and Navigation -->
    <div class="mb-4 flex items-center justify-between">
        <button
            wire:click="previousMonth"
            wire:loading.attr="disabled"
            @disabled(! $this->canGoPreviousMonth())
            class="rounded-full p-2 text-amber-400 hover:bg-amber-500/20 transition-colors duration-200"
            aria-label="Previous month"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <h3 class="text-lg font-semibold text-amber-400">{{ $this->monthYear }}</h3>

        <button
            wire:click="nextMonth"
            wire:loading.attr="disabled"
            @disabled(! $this->canGoNextMonth())
            class="rounded-full p-2 text-amber-400 hover:bg-amber-500/20 transition-colors duration-200"
            aria-label="Next month"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Calendar Grid -->
    <div class="rounded-xl border border-amber-500/10 bg-zinc-900 p-4">
        <!-- Day Headers (Sun-Sat) -->
        <div class="grid grid-cols-7 gap-2 mb-3">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                <div class="text-center text-xs font-bold text-amber-500/70 py-2">
                    {{ $dayName }}
                </div>
            @endforeach
        </div>

        <!-- Calendar Days Grid -->
        <div class="grid grid-cols-7 gap-2">
            @foreach ($this->calendarDays as $day)
                @if ($day['isCurrentMonth'])
                    <button
                        type="button"
                        wire:key="day-{{ $currentYear }}-{{ $currentMonth }}-{{ $day['fullDate'] !== '' ? $day['fullDate'] : 'pad-'.$loop->index }}"
                        wire:click="selectDate('{{ $day['fullDate'] }}')"
                        :class="{
                            'bg-amber-500 text-zinc-900 font-bold shadow-lg shadow-amber-500/30': @json($this->selectedDate === $day['fullDate'] && ! $day['isClosed'] && ! $day['isPast']),
                            'border-amber-500/20 text-white hover:border-amber-500/50 hover:bg-zinc-800': @json($this->selectedDate !== $day['fullDate'] || $day['isClosed'] || $day['isPast']) && !@json($day['isClosed'] || $day['isPast']),
                            'border-red-500/40 text-red-300 hover:border-red-500/70 hover:bg-red-500/10': @json($day['isClosed'] && ! $day['isPast']),
                            'border-zinc-700 text-zinc-600 cursor-not-allowed': @json($day['isPast'])
                        }"
                        class="rounded-lg border-2 py-3 text-sm font-semibold transition duration-200"
                        @if ($day['isPast']) disabled @endif
                    >
                        {{ $day['date'] }}
                    </button>
                @else
                    <!-- Previous/Next Month Days (Disabled) -->
                    <div class="rounded-lg border-2 border-zinc-800 py-3 text-center text-sm text-zinc-700">
                        {{ $day['date'] }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if ($this->blockedDate)
        <div class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 p-4">
            <p class="text-xs uppercase tracking-widest text-red-300">Date Unavailable</p>
            <p class="mt-2 text-sm text-zinc-200">
                {{ \Carbon\Carbon::parse($this->blockedDate)->format('F d, Y') }} is marked as {{ $this->blockedDateType === 'holiday' ? 'Holiday' : 'Closed' }}.
            </p>
            @if ($this->blockedDateNote)
                <p class="mt-2 text-sm text-zinc-300">Note: {{ $this->blockedDateNote }}</p>
            @endif
        </div>
    @endif

    <!-- Selected Date Display -->
    @if ($this->selectedDate)
        <div class="mt-4 text-center">
            <p class="text-sm text-zinc-400">
                Selected: <span class="text-amber-400 font-semibold">{{ \Carbon\Carbon::parse($this->selectedDate)->format('F d, Y') }}</span>
            </p>
        </div>
    @endif
</div>
