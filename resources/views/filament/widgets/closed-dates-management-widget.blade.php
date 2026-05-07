<div>
    <div class="fi-wi">
        <div class="fi-wi-header">
            <h3 class="text-lg font-semibold text-white mb-4">Quick Calendar</h3>
        </div>

        <!-- Calendar Header with Icon -->
        <div class="flex items-center justify-between mb-4 gap-4">
            <div class="flex-1">
                <div class="text-sm text-gray-300 mb-2">Block dates for calendar</div>
                <button
                    type="button"
                    wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-gray-900 font-semibold rounded-lg transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Open Calendar</span>
                </button>
            </div>
        </div>

        <!-- Modal -->
        @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeModal">
            <div class="bg-gray-900 rounded-lg shadow-lg p-6 max-w-md w-full mx-4" @click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Block Date</h2>
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="text-gray-400 hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Calendar Grid -->
                <div class="mb-6">
                    <!-- Month/Year Navigation -->
                    <div class="flex items-center justify-between mb-4">
                        <button
                            type="button"
                            wire:click="previousMonth"
                            class="p-2 hover:bg-gray-800 rounded-lg transition"
                        >
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span class="text-sm font-semibold text-gray-200">{{ $this->monthYear }}</span>
                        <button
                            type="button"
                            wire:click="nextMonth"
                            class="p-2 hover:bg-gray-800 rounded-lg transition"
                        >
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Weekday Headers -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                        <div class="text-center text-xs font-semibold text-gray-400 py-2">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($this->calendarDays as $dayData)
                            @if($dayData === null)
                                <div></div>
                            @else
                                <button
                                    type="button"
                                    wire:click="selectDate('{{ $dayData['date'] }}')"
                                    class="
                                        py-2 px-1 rounded-lg text-sm font-semibold transition
                                        @if($selectedDate === $dayData['date'])
                                            bg-amber-500 text-gray-900
                                        @elseif($dayData['isClosed'])
                                            bg-red-500/30 text-red-300 border border-red-500/50
                                        @elseif($dayData['isToday'])
                                            bg-blue-500/30 text-blue-300 border border-blue-500/50
                                        @else
                                            bg-gray-800 text-gray-200 hover:bg-gray-700
                                        @endif
                                    "
                                >
                                    {{ $dayData['day'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Selected Date Info and Form -->
                @if($selectedDate)
                    <div class="border-t border-gray-700 pt-4">
                        <div class="mb-4">
                            <p class="text-sm text-gray-300 mb-3">
                                <span class="font-semibold">Selected Date:</span> {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
                            </p>

                            <!-- Status Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-200 mb-2">Status</label>
                                <select
                                    wire:model="selectedStatus"
                                    class="w-full bg-gray-800 text-gray-200 border border-gray-700 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                >
                                    <option value="open">Open (Available)</option>
                                    <option value="closed">Closed</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>

                            <!-- Note Input -->
                            @if($selectedStatus !== 'open')
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-200 mb-2">Note</label>
                                    <textarea
                                        wire:model="note"
                                        placeholder="Add reason for unavailability..."
                                        class="w-full bg-gray-800 text-gray-200 border border-gray-700 rounded-lg px-3 py-2 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm"
                                        rows="3"
                                    ></textarea>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="saveDateStatus"
                                class="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-gray-900 font-semibold rounded-lg transition"
                            >
                                Save
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold rounded-lg transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Info Text -->
        <div class="mt-4 p-4 bg-gray-800/50 rounded-lg border border-gray-700">
            <p class="text-xs text-gray-400">
                📅 <span class="text-blue-300">Blue</span> = Today • <span class="text-red-300">Red</span> = Closed • Click a date to block it or add a note
            </p>
        </div>
    </div>
</div>
