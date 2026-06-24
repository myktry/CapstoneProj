<?php

namespace App\Livewire\Admin;

use App\Models\ClosedDate;
use Carbon\Carbon;
use Livewire\Component;

class CalendarManagement extends Component
{
    public int $currentMonth;

    public int $currentYear;

    public ?string $selectedDate = null;

    public string $selectedStatus = 'open';

    public ?string $note = null;

    public function mount(): void
    {
        $now = now();

        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();

        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();

        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function selectDate(string $date): void
    {
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
        if (! $this->selectedDate) {
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

        $this->dispatch('refresh');
    }

    public function getCalendarDaysProperty(): array
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $daysInMonth = $lastDay->day;
        $startingDayOfWeek = $firstDay->dayOfWeek;

        $dateStatuses = ClosedDate::query()
            ->active()
            ->whereBetween('date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->get(['date', 'type'])
            ->mapWithKeys(fn (ClosedDate $record) => [
                Carbon::parse($record->date)->toDateString() => $record->type,
            ]);

        $days = [];

        $prevMonthLastDay = $firstDay->copy()->subDay()->day;

        for ($index = $startingDayOfWeek - 1; $index >= 0; $index--) {
            $days[] = [
                'date' => $prevMonthLastDay - $index,
                'isCurrentMonth' => false,
                'fullDate' => null,
                'status' => 'open',
            ];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObject = Carbon::create($this->currentYear, $this->currentMonth, $day);
            $fullDate = $dateObject->toDateString();

            $days[] = [
                'date' => $day,
                'isCurrentMonth' => true,
                'fullDate' => $fullDate,
                'status' => $dateStatuses[$fullDate] ?? 'open',
            ];
        }

        $totalCells = count($days);
        $remainingCells = ($totalCells % 7 === 0) ? 0 : 7 - ($totalCells % 7);

        for ($day = 1; $day <= $remainingCells; $day++) {
            $days[] = [
                'date' => $day,
                'isCurrentMonth' => false,
                'fullDate' => null,
                'status' => 'open',
            ];
        }

        return $days;
    }

    public function getMonthYearProperty(): string
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
    }

    public function render()
    {
        return view('livewire.admin.calendar-management');
    }
}
