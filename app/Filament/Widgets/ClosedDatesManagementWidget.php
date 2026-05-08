<?php

namespace App\Filament\Widgets;

use App\Models\ClosedDate;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ClosedDatesManagementWidget extends Widget
{
    protected string $view = 'filament.widgets.closed-dates-management-widget';

    protected int|string|array $columnSpan = 'full';

    public ?int $currentMonth = null;

    public ?int $currentYear = null;

    public ?string $selectedDate = null;

    public string $selectedStatus = 'open';

    public ?string $note = null;

    public function mount(): void
    {
        $this->initializeCalendarState();
    }

    public function previousMonth(): void
    {
        $this->initializeCalendarState();

        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $this->initializeCalendarState();

        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function openModal(): void
    {
        $this->dispatch('open-modal', 'closed-dates-calendar');
    }

    public function closeModal(): void
    {
        $this->selectedDate = null;
        $this->selectedStatus = 'open';
        $this->note = null;

        $this->dispatch('close-modal', 'closed-dates-calendar');
    }

    public function selectDate(string $date): void
    {
        $this->initializeCalendarState();

        $this->selectedDate = $date;

        $closedDate = ClosedDate::query()
            ->whereDate('date', $date)
            ->where('is_active', true)
            ->first();

        if ($closedDate) {
            $this->selectedStatus = $closedDate->type;
            $this->note = $closedDate->note;
            return;
        }

        $this->selectedStatus = 'open';
        $this->note = null;
    }

    public function saveDateStatus(): void
    {
        $this->initializeCalendarState();

        if (!$this->selectedDate) {
            return;
        }

        $normalizedDate = Carbon::parse($this->selectedDate)->toDateString();

        if ($this->selectedStatus === 'open') {
            ClosedDate::query()
                ->whereDate('date', $normalizedDate)
                ->get()
                ->each
                ->delete();
        } else {
            $now = now();

            ClosedDate::query()->upsert([
                [
                    'date' => $normalizedDate,
                    'type' => $this->selectedStatus,
                    'note' => $this->note,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['date'], ['type', 'note', 'is_active', 'updated_at']);
        }

        $this->closeModal();
        $this->dispatch('calendar-updated');
    }

    public function getCalendarDaysProperty(): array
    {
        $this->initializeCalendarState();

        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();

        $days = [];
        $weekday = $firstDay->dayOfWeekIso;
        $daysInMonth = $lastDay->day;

        for ($i = 1; $i < $weekday; $i++) {
            $days[] = null;
        }

        $closedDates = ClosedDate::query()
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->where('is_active', true)
            ->pluck('date')
            ->toArray();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $dateString = $date->toDateString();
            $isClosed = in_array($dateString, $closedDates);

            $days[] = [
                'day' => $day,
                'date' => $dateString,
                'isClosed' => $isClosed,
                'isToday' => $date->isToday(),
            ];
        }

        return $days;
    }

    public function getMonthYearProperty(): string
    {
        $this->initializeCalendarState();

        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    protected function initializeCalendarState(): void
    {
        if ($this->currentMonth !== null && $this->currentYear !== null) {
            return;
        }

        $now = now();

        $this->currentMonth ??= $now->month;
        $this->currentYear ??= $now->year;
    }
}
