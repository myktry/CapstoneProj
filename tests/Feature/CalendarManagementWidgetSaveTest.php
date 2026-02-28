<?php

namespace Tests\Feature;

use App\Filament\Widgets\CalendarManagementWidget;
use App\Models\ClosedDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarManagementWidgetSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_updates_existing_same_day_record_without_unique_violation(): void
    {
        ClosedDate::query()->create([
            'date' => '2026-02-28 00:00:00',
            'type' => 'holiday',
            'note' => 'Old note',
            'is_active' => true,
        ]);

        Livewire::test(CalendarManagementWidget::class)
            ->set('selectedDate', '2026-02-28')
            ->set('selectedStatus', 'closed')
            ->set('note', 'Close close')
            ->call('saveDateStatus');

        $this->assertSame(1, ClosedDate::query()->whereDate('date', '2026-02-28')->count());

        $this->assertDatabaseHas('closed_dates', [
            'type' => 'closed',
            'note' => 'Close close',
            'is_active' => 1,
        ]);
    }
}
