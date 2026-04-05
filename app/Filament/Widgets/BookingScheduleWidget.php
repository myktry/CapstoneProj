<?php

namespace App\Filament\Widgets;

use App\Models\ContactSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class BookingScheduleWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.booking-schedule-widget';

    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '220px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public ?array $data = [];

    public function mount(): void
    {
        $contact = ContactSetting::query()->first();

        if (! $contact) {
            $this->form->fill($this->defaultScheduleData());

            return;
        }

        $this->form->fill([
            'booking_start_time' => $contact->booking_start_time,
            'booking_end_time' => $contact->booking_end_time,
            'booking_interval_minutes' => $contact->booking_interval_minutes,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_start_time')
                    ->label('Booking Start Time')
                    ->type('time')
                    ->required(),
                TextInput::make('booking_end_time')
                    ->label('Booking End Time')
                    ->type('time')
                    ->required(),
                Select::make('booking_interval_minutes')
                    ->label('Booking Slot Interval (Minutes)')
                    ->options([
                        15 => '15',
                        30 => '30',
                        45 => '45',
                        60 => '60',
                    ])
                    ->required(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $contact = ContactSetting::query()->orderBy('id')->first();

        if (! $contact) {
            ContactSetting::query()->create(array_merge([
                'location_line_1' => '123 Ember Street',
                'location_line_2' => 'Downtown, PH 1000',
                'hours_line_1' => 'Mon - Sat: 10 AM - 8 PM',
                'hours_line_2' => 'Sun: 12 PM - 6 PM',
                'phone' => '+63 900 000 0000',
                'email' => 'hello@blackember.com',
            ], $data));
        } else {
            $contact->update($data);
        }

        Notification::make()
            ->title('Booking schedule updated')
            ->success()
            ->send();
    }

    private function defaultScheduleData(): array
    {
        return [
            'booking_start_time' => '10:00',
            'booking_end_time' => '17:00',
            'booking_interval_minutes' => 60,
        ];
    }
}
