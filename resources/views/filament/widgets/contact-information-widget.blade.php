<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Contact Information
        </x-slot>

        <x-slot name="description">
            Update website contact details shown on the homepage.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            <div>
                {{ $this->form }}
            </div>

            <div class="w-full mt-8 pt-2 flex justify-end">
                <x-filament::button type="submit">
                    Save Contact Information
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
