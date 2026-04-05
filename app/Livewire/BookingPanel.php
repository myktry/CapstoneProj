<?php

namespace App\Livewire;

use App\Models\ContactSetting;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BookingPanel extends Component
{
    public bool $isOpen = false;
    public ?string $selectedDate = null;
    public ?int $selectedServiceId = null;
    public ?string $selectedTime = null;
    public array $form = [
        'name' => '',
        'email' => '',
        'phone' => '',
    ];

    protected $listeners = ['open-booking' => 'openPanel', 'select-date' => 'handleDateSelected'];

    public function mount(): void
    {
        $this->prefillContactForm();

        $this->applySelectedService(request()->query('service'));
    }

    #[Computed]
    public function isFormComplete()
    {
        if (! $this->selectedDate || ! $this->selectedServiceId || ! $this->selectedTime) {
            return false;
        }

        return !empty(trim($this->form['name'])) 
            && !empty(trim($this->form['email'])) 
            && !empty(trim($this->form['phone']));
    }

    #[Computed]
    public function activeServices()
    {
        return Service::query()
            ->active()
            ->get(['id', 'name', 'price']);
    }

    #[Computed]
    public function selectedService()
    {
        if (! $this->selectedServiceId) {
            return null;
        }

        return $this->activeServices->firstWhere('id', $this->selectedServiceId);
    }

    #[Computed]
    public function timeSlots(): array
    {
        if (! Schema::hasColumns('contact_settings', ['booking_start_time', 'booking_end_time', 'booking_interval_minutes'])) {
            return ['10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'];
        }

        $contact = ContactSetting::query()->first(['booking_start_time', 'booking_end_time', 'booking_interval_minutes']);

        $startTime = $contact?->booking_start_time ?: '10:00:00';
        $endTime = $contact?->booking_end_time ?: '17:00:00';
        $intervalMinutes = max(15, (int) ($contact?->booking_interval_minutes ?: 60));

        $start = Carbon::createFromTimeString($startTime);
        $end = Carbon::createFromTimeString($endTime);

        if ($end->lessThan($start)) {
            return [];
        }

        $slots = [];
        $current = $start->copy();

        while ($current->lessThanOrEqualTo($end)) {
            $slots[] = $current->format('g:i A');
            $current->addMinutes($intervalMinutes);
        }

        return $slots;
    }

    public function getFormStatus()
    {
        return [
            'name' => trim($this->form['name']),
            'email' => trim($this->form['email']),
            'phone' => trim($this->form['phone']),
        ];
    }

    public function openPanel(mixed $service = null, mixed $serviceId = null): void
    {
        $this->prefillContactForm();

        $selectedService = $serviceId ?? $service;

        if (is_array($service)) {
            $selectedService = $service['serviceId'] ?? $service['service'] ?? $selectedService;
        }

        $this->applySelectedService(is_int($selectedService) || is_string($selectedService) ? $selectedService : null);

        $this->isOpen = true;
    }

    public function closePanel()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function selectTime(string $time)
    {
        $this->selectedTime = $time;
    }

    public function updatedSelectedServiceId(): void
    {
        $this->selectedTime = null;
    }

    public function handleDateSelected(?string $date)
    {
        $this->selectedDate = $date;

        if (! $date) {
            $this->selectedTime = null;
        }
    }

    public function submitBooking()
    {
        if (!$this->selectedDate || !$this->selectedServiceId || !$this->selectedTime) {
            $this->dispatch('notify', message: 'Please select date, service, and time first.');
            return;
        }

        if (!$this->form['name'] || !$this->form['email'] || !$this->form['phone']) {
            $this->dispatch('notify', message: 'Please fill in all required fields.');
            return;
        }

        // Store booking details in session so the checkout controller can use them
        Session::put('pending_booking', [
            'user_id'          => auth()->id(),
            'service_id'       => $this->selectedServiceId,
            'appointment_date' => $this->selectedDate,
            'appointment_time' => $this->selectedTime,
            'customer_name'    => trim($this->form['name']),
            'customer_email'   => trim($this->form['email']),
            'customer_phone'   => trim($this->form['phone']),
        ]);

        // Redirect to Stripe Checkout (hard redirect — not SPA)
        $this->redirect(route('checkout.create'), navigate: false);
    }

    private function resetForm()
    {
        $this->selectedDate = null;
        $this->selectedServiceId = null;
        $this->selectedTime = null;
        $this->form = [
            'name' => '',
            'email' => '',
            'phone' => '',
        ];

        $this->prefillContactForm();
    }

    private function prefillContactForm(): void
    {
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        $this->form['name'] = $user->name ?? '';
        $this->form['email'] = $user->email ?? '';
        $this->form['phone'] = $user->phone ?? '';
    }

    private function applySelectedService(int|string|null $serviceInput): void
    {
        if (blank($serviceInput)) {
            return;
        }

        $matchedService = null;

        if (is_numeric($serviceInput)) {
            $serviceId = (int) $serviceInput;
            $matchedService = $this->activeServices->first(fn ($service) => (int) $service->id === $serviceId);
        }

        if (! $matchedService && is_string($serviceInput)) {
            $needle = strtolower(trim($serviceInput));
            $matchedService = $this->activeServices->first(
                fn ($service) => strtolower(trim((string) $service->name)) === $needle
            );
        }

        if (! $matchedService) {
            return;
        }

        $serviceId = (int) $matchedService->id;

        if ($this->selectedServiceId !== $serviceId) {
            $this->selectedServiceId = $serviceId;
            $this->selectedTime = null;
        }
    }

    public function render()
    {
        return view('livewire.booking-panel');
    }
}
