<div>
    <!-- Fixed Overlay Backdrop -->
    <div
        wire:click="closePanel"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm transition-opacity duration-300 {{ !$isOpen ? 'opacity-0 pointer-events-none' : 'opacity-100' }}"
        aria-hidden="true"
    ></div>

    <!-- Fixed Slide-Over Panel -->
    <div
        class="fixed inset-y-0 right-0 z-50 w-full md:max-w-[500px] overflow-y-auto scrollbar-hide bg-[#0a0a0a] border-l border-amber-500/20 shadow-2xl transition-transform duration-300 {{ !$isOpen ? 'translate-x-full' : 'translate-x-0' }}"
    >
        <!-- Panel Content -->
        <div class="h-full flex flex-col">
            <!-- Header Section -->
            <div class="border-b border-amber-500/10 bg-[#0a0a0a] px-6 py-8 sticky top-0 z-10">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-xs uppercase tracking-[0.3em] text-amber-400">Reserve Your Spot</p>
                        <h2 class="mt-3 text-3xl font-bold text-white">Book Appointment</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="closePanel"
                        class="flex-shrink-0 rounded-full p-2 text-zinc-400 hover:text-amber-400 hover:bg-amber-500/20 transition-colors duration-200"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Scrollable Content -->
            @if ($isOpen)
            <div class="flex-1 overflow-y-auto scrollbar-hide space-y-8 px-6 py-8">
                <!-- Calendar Date Selection Section -->
                <div>
                    <p class="mb-4 text-xs uppercase tracking-widest text-amber-400/70">Step 1: Select Date</p>
                    <livewire:components.booking-calendar wire:key="calendar-{{ $isOpen }}" />
                </div>

                <!-- Service Selection Section -->
                @if ($selectedDate)
                    <div class="transition-all duration-300">
                        <p class="mb-4 text-xs uppercase tracking-widest text-amber-400/70">Step 2: Select Service</p>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-300">Service *</label>
                            <select
                                wire:model.change="selectedServiceId"
                                class="w-full rounded-lg border border-amber-500/20 bg-zinc-900 px-4 py-3 text-white focus:border-amber-500/50 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                            >
                                <option value="">Choose a service</option>
                                @foreach ($this->activeServices as $service)
                                    <option value="{{ $service->id }}">
                                        {{ $service->name }} - ₱{{ number_format((float) $service->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <!-- Time Selection Section -->
                @if ($selectedDate && $selectedServiceId)
                    <div class="transition-all duration-300">
                        <p class="mb-4 text-xs uppercase tracking-widest text-amber-400/70">Step 3: Select Time</p>
                        <div class="grid grid-cols-3 gap-3">
                            @php
                                $times = ['10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'];
                            @endphp
                            @foreach ($times as $time)
                                <button
                                    type="button"
                                    wire:click="selectTime('{{ $time }}')"
                                    class="rounded-xl border-2 px-3 py-3 text-sm font-bold transition duration-200 {{ $selectedTime === $time ? 'border-amber-400 bg-amber-500/20 text-amber-300 shadow-lg shadow-amber-500/30' : 'border-white/10 text-zinc-400 hover:border-amber-500/30 hover:text-zinc-300 hover:bg-zinc-800' }}"
                                >
                                    {{ $time }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Contact Form Section -->
                @if ($selectedTime)
                    <div class="transition-all duration-500 space-y-6 border-t border-amber-500/10 pt-8">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-amber-400/70">Step 4: Your Information</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-300">Full Name *</label>
                            <input
                                type="text"
                                wire:model.blur="form.name"
                                placeholder="John Doe"
                                class="w-full rounded-lg border border-amber-500/20 bg-zinc-900 px-4 py-3 text-white placeholder-zinc-600 focus:border-amber-500/50 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-300">Email Address *</label>
                            <input
                                type="email"
                                wire:model.blur="form.email"
                                placeholder="john@example.com"
                                class="w-full rounded-lg border border-amber-500/20 bg-zinc-900 px-4 py-3 text-white placeholder-zinc-600 focus:border-amber-500/50 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-zinc-300">Phone Number *</label>
                            <input
                                type="tel"
                                wire:model.blur="form.phone"
                                placeholder="(555) 123-4567"
                                class="w-full rounded-lg border border-amber-500/20 bg-zinc-900 px-4 py-3 text-white placeholder-zinc-600 focus:border-amber-500/50 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all duration-200"
                            />
                        </div>

                        <!-- Booking Summary -->
                        @php
                            $isComplete = !empty(trim($form['name'])) && !empty(trim($form['email'])) && !empty(trim($form['phone']));
                        @endphp
                        @if ($isComplete)
                            <div class="transition-all duration-500 rounded-xl bg-amber-500/10 border border-amber-500/20 p-4">
                                <p class="text-xs uppercase tracking-widest text-amber-400/70 mb-3">Booking Summary</p>
                                <div class="space-y-2 text-sm text-amber-200">
                                    <p><strong>📅 Date:</strong> {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                                    @if ($this->selectedService)
                                        <p><strong>✂️ Service:</strong> {{ $this->selectedService->name }}</p>
                                        <p><strong>💰 Price:</strong> ₱{{ number_format((float) $this->selectedService->price, 2) }}</p>
                                    @endif
                                    <p><strong>⏰ Time:</strong> {{ $selectedTime }}</p>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="button"
                                wire:click="submitBooking"
                                class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 py-4 font-bold text-zinc-900 transition duration-200 hover:shadow-lg hover:shadow-amber-500/30 active:scale-95"
                            >
                                Confirm Booking
                            </button>
                        @else
                            <div class="rounded-lg bg-zinc-800/50 border border-zinc-700 p-4 text-center">
                                <p class="text-sm text-zinc-400">Fill in all fields to confirm</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Empty State -->
                @if (!$selectedDate)
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-amber-500/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-zinc-500">Select a date to continue</p>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
