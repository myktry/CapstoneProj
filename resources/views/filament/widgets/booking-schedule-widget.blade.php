<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Booking Schedule
        </x-slot>

        <x-slot name="description">
            Configure the available booking time range and slot interval.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            <div>
                {{ $this->form }}
            </div>

            <div class="w-full flex justify-end" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                <x-filament::button type="submit">
                    Save Booking Schedule
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
