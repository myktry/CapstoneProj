<?php

namespace App\Livewire\Components;

use App\Models\ClosedDate;
use Carbon\Carbon;
use Livewire\Component;

class BookingCalendar extends Component
{
    public int $currentMonth;
    public int $currentYear;
    public int $maxAdvanceMonths = 12;
    public ?string $selectedDate = null;
    public ?string $blockedDate = null;
    public ?string $blockedDateType = null;
    public ?string $blockedDateNote = null;

    public function mount()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function previousMonth()
    {
        if (! $this->canGoPreviousMonth()) {
            return;
        }

        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        if (! $this->canGoNextMonth()) {
            return;
        }

        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function selectDate($date)
    {
        $closedDate = ClosedDate::query()
            ->active()
            ->whereDate('date', $date)
            ->first(['type', 'note']);

        if ($closedDate) {
            $this->selectedDate = null;
            $this->blockedDate = $date;
            $this->blockedDateType = $closedDate->type;
            $this->blockedDateNote = $closedDate->note;
            $this->dispatch('select-date', date: null)->to('booking-panel');

            return;
        }

        $this->selectedDate = $date;
        $this->blockedDate = null;
        $this->blockedDateType = null;
        $this->blockedDateNote = null;
        $this->dispatch('select-date', date: $date)->to('booking-panel');
    }

    public function getCalendarDaysProperty()
    {
        $today = now()->startOfDay();
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $daysInMonth = $lastDay->day;
        $startingDayOfWeek = $firstDay->dayOfWeek; // 0 = Sunday

        $closedDates = ClosedDate::query()
            ->active()
            ->whereDate('date', '>=', $firstDay->toDateString())
            ->whereDate('date', '<=', $lastDay->toDateString())
            ->get(['date', 'type', 'note'])
            ->mapWithKeys(fn (ClosedDate $record) => [
                Carbon::parse($record->date)->toDateString() => [
                    'type' => $record->type,
                    'note' => $record->note,
                ],
            ]);

        $days = [];

        // Previous month's days (grayed out)
        $prevMonthLastDay = $firstDay->copy()->subDay()->day;
        for ($i = $startingDayOfWeek - 1; $i >= 0; $i--) {
            $days[] = [
                'date' => $prevMonthLastDay - $i,
                'isCurrentMonth' => false,
                'fullDate' => '',
                'isClosed' => false,
            ];
        }

        // Current month's days
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateObj = Carbon::create($this->currentYear, $this->currentMonth, $i);
            $fullDate = $dateObj->format('Y-m-d');

            $closedData = $closedDates->get($fullDate);
            $isClosed = filled($closedData);

            // Don't allow booking dates in the past
            $isPast = $dateObj->isBefore($today);

            $days[] = [
                'date' => $i,
                'isCurrentMonth' => true,
                'fullDate' => $fullDate,
                'isClosed' => $isClosed,
                'isPast' => $isPast,
                'closedType' => $closedData['type'] ?? null,
                'closedNote' => $closedData['note'] ?? null,
            ];
        }

        // Next month's days (grayed out)
        $totalCells = count($days);
        $remainingCells = ($totalCells % 7 === 0) ? 0 : 7 - ($totalCells % 7);
        for ($i = 1; $i <= $remainingCells; $i++) {
            $days[] = [
                'date' => $i,
                'isCurrentMonth' => false,
                'fullDate' => '',
                'isClosed' => false,
            ];
        }

        return $days;
    }

    public function getMonthYearProperty()
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    public function canGoPreviousMonth(): bool
    {
        $current = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();

        return $current->greaterThan(now()->startOfMonth());
    }

    public function canGoNextMonth(): bool
    {
        $current = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $lastAllowed = now()->startOfMonth()->addMonths($this->maxAdvanceMonths);

        return $current->lessThan($lastAllowed);
    }

    public function render()
    {
        return view('livewire.components.booking-calendar');
    }
}
