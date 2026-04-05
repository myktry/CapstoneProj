<?php

namespace App\Filament\Widgets;

use App\Models\ContactSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class ContactInformationWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.contact-information-widget';

    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $contact = ContactSetting::query()->first();

        if (! $contact) {
            $this->form->fill($this->defaultContactData());

            return;
        }

        $this->form->fill([
            'location_line_1' => $contact->location_line_1,
            'location_line_2' => $contact->location_line_2,
            'hours_line_1' => $contact->hours_line_1,
            'hours_line_2' => $contact->hours_line_2,
            'phone' => $contact->phone,
            'email' => $contact->email,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('location_line_1')
                    ->label('Location Line 1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location_line_2')
                    ->label('Location Line 2')
                    ->maxLength(255),
                TextInput::make('hours_line_1')
                    ->label('Hours Line 1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('hours_line_2')
                    ->label('Hours Line 2')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Phone')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $contact = ContactSetting::query()->orderBy('id')->first();

        if (! $contact) {
            ContactSetting::query()->create($data);
        } else {
            $contact->update($data);
        }

        Notification::make()
            ->title('Contact information updated')
            ->success()
            ->send();
    }

    private function defaultContactData(): array
    {
        return [
            'location_line_1' => '123 Ember Street',
            'location_line_2' => 'Downtown, PH 1000',
            'hours_line_1' => 'Mon - Sat: 10 AM - 8 PM',
            'hours_line_2' => 'Sun: 12 PM - 6 PM',
            'phone' => '+63 900 000 0000',
            'email' => 'hello@blackember.com',
        ];
    }
}
