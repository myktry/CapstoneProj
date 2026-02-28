@props([
    'open' => false,
])

<div x-data="bookingSlideOver()" @open-booking.window="slideOpen = true">
    <!-- Trigger Button (removed - now uses event dispatch) -->
    <!-- Button is in the welcome.blade.php file and dispatches 'open-booking' event -->

    <!-- Fixed Overlay Backdrop -->
    <div
        x-show="slideOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="slideOpen = false"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    <!-- Fixed Slide-Over Panel -->
    <div
        x-show="slideOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 w-full md:max-w-[500px] overflow-y-auto scrollbar-hide bg-[#0a0a0a] border-l border-amber-500/20 shadow-2xl"
    >
        <!-- Panel Content -->
        <div class="h-full flex flex-col">
            <!-- Header Section -->
            <div class="border-b border-amber-500/10 bg-[#0a0a0a] px-6 py-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-xs uppercase tracking-[0.3em] text-amber-400">Reserve Your Spot</p>
                        <h2 class="mt-3 text-3xl font-bold text-white">Book Appointment</h2>
                    </div>
                    <button
                        type="button"
                        @click.stop="slideOpen = false"
                        class="flex-shrink-0 rounded-full p-2 text-zinc-400 hover:text-amber-400 hover:bg-amber-500/20 transition-colors duration-200"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto scrollbar-hide space-y-8 px-6 py-8">
                <!-- Calendar Date Selection Section -->
                <div>
                    <p class="mb-4 text-xs uppercase tracking-widest text-amber-400/70">Select Date</p>
                    <livewire:components.booking-calendar />
                </div>

                <!-- Time Selection Section -->
                <div x-show="selectedDate" x-transition.duration.300ms>
                    <p class="mb-4 text-xs uppercase tracking-widest text-amber-400/70">Select Time</p>
                    <div class="grid grid-cols-3 gap-3">
                        @php
                            $times = ['10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'];
                        @endphp
                        @foreach ($times as $time)
                            <button
                                type="button"
                                @click="selectedTime = '{{ $time }}'"
                                :class="{
                                    'border-amber-400 bg-amber-500/20 text-amber-300 shadow-lg shadow-amber-500/30': selectedTime === '{{ $time }}',
                                    'border-white/10 text-zinc-400 hover:border-amber-500/30 hover:text-zinc-300': selectedTime !== '{{ $time }}'
                                }"
                                class="rounded-xl border-2 px-3 py-3 text-sm font-bold transition duration-200"
                            >
                                {{ $time }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Contact Form Section -->
                <div
                    x-show="selectedTime"
                    x-transition.duration.500ms
                    class="space-y-6 border-t border-amber-500/10 pt-8"
                >
                    <div>
                        <p class="text-xs uppercase tracking-widest text-amber-400/70">Your Information</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-300">Full Name *</label>
                        <input
                            type="text"
                            x-model="form.name"
                            placeholder="Juan Dela Cruz"
                            class="w-full rounded-xl border-2 border-white/10 bg-zinc-900/50 px-4 py-3 text-white placeholder-zinc-600 transition duration-200 focus:border-amber-400 focus:outline-none hover:border-white/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-300">Email *</label>
                        <input
                            type="email"
                            x-model="form.email"
                            placeholder="your@email.com"
                            class="w-full rounded-xl border-2 border-white/10 bg-zinc-900/50 px-4 py-3 text-white placeholder-zinc-600 transition duration-200 focus:border-amber-400 focus:outline-none hover:border-white/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-300">Phone Number *</label>
                        <input
                            type="tel"
                            x-model="form.phone"
                            placeholder="+63 900 000 0000"
                            class="w-full rounded-xl border-2 border-white/10 bg-zinc-900/50 px-4 py-3 text-white placeholder-zinc-600 transition duration-200 focus:border-amber-400 focus:outline-none hover:border-white/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-300">Service *</label>
                        <select
                            x-model="form.service"
                            class="w-full rounded-xl border-2 border-white/10 bg-zinc-900/50 px-4 py-3 text-white transition duration-200 focus:border-amber-400 focus:outline-none hover:border-white/20"
                        >
                            <option value="">Select a service</option>
                            <option value="Midnight Fade">Midnight Fade - 45 min</option>
                            <option value="Classic Gentleman">Classic Gentleman - 40 min</option>
                            <option value="Beard Ritual">Beard Ritual - 30 min</option>
                        </select>
                    </div>

                    <!-- Booking Summary Card -->
                    <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-4">
                        <p class="text-xs uppercase tracking-widest text-amber-400/70 mb-2">Booking Summary</p>
                        <p class="text-sm text-amber-200">
                            <span x-text="selectedDate"></span> at <span x-text="selectedTime"></span>
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="button"
                        @click="submitBooking()"
                        class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 px-6 py-4 font-bold text-zinc-900 transition duration-200 hover:shadow-lg hover:shadow-amber-500/30 active:scale-95"
                    >
                        Confirm Booking
                    </button>
                </div>

                <!-- Empty State -->
                <div x-show="!selectedDate && !selectedTime" class="py-12 text-center">
                    <p class="text-sm text-zinc-500">Select a date to continue</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.bookingSlideOver = function () {
        return {
            slideOpen: false,
            selectedDate: '',
            selectedTime: '',
            form: {
                name: '',
                email: '',
                phone: '',
                service: '',
            },

            init() {
                // Listen for Livewire date selection events
                this.$watch('$data', () => {});
                Livewire.hook('message.processed', () => {
                    // Refresh when Livewire processes updates
                    this.$nextTick(() => {});
                });
            },

            submitBooking() {
                if (!this.form.name || !this.form.email || !this.form.phone || !this.form.service) {
                    alert('Please fill in all required fields.');
                    return;
                }
                alert(
                    `✓ Booking confirmed!\n\nDate: ${this.selectedDate}\nTime: ${this.selectedTime}\n\nName: ${this.form.name}\nEmail: ${this.form.email}\nService: ${this.form.service}`
                );
                this.slideOpen = false;
                this.selectedDate = '';
                this.selectedTime = '';
                this.form = { name: '', email: '', phone: '', service: '' };
            },
        };
    };
</script>
