<?php

namespace App\Livewire;

use App\Models\Service;
use Illuminate\Support\Facades\Session;
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

    public function getFormStatus()
    {
        return [
            'name' => trim($this->form['name']),
            'email' => trim($this->form['email']),
            'phone' => trim($this->form['phone']),
        ];
    }

    public function openPanel()
    {
        $this->prefillContactForm();
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

    public function render()
    {
        return view('livewire.booking-panel');
    }
}
