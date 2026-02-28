<style>
    .admin-calendar-wrap { background: #05070d; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 20px; }
    .admin-calendar-title { color: #fbbf24; font-size: 12px; text-transform: uppercase; letter-spacing: 0.28em; margin: 0 0 8px; }
    .admin-calendar-subtitle { color: #fff; font-size: 34px; font-weight: 700; margin: 0 0 16px; }
    .admin-calendar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .admin-calendar-nav { background: transparent; color: #fbbf24; border: 0; border-radius: 999px; padding: 8px; cursor: pointer; }
    .admin-calendar-nav:hover { background: rgba(251, 191, 36, 0.14); }
    .admin-calendar-month { color: #fbbf24; font-size: 30px; font-weight: 700; margin: 0; }
    .admin-calendar-box { background: #131720; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 14px; }
    .admin-calendar-weekdays, .admin-calendar-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 8px; }
    .admin-calendar-weekdays { margin-bottom: 10px; }
    .admin-calendar-weekdays div { text-align: center; color: #d4a826; font-size: 13px; font-weight: 700; }
    .admin-calendar-empty { border: 2px solid #252b36; border-radius: 10px; padding: 10px 0; text-align: center; color: #3b4250; font-weight: 600; }
    .admin-day { border: 2px solid #3a4150; border-radius: 10px; padding: 10px 0; color: #d1d5db; background: transparent; font-weight: 700; cursor: pointer; transition: 160ms ease; }
    .admin-day:hover { border-color: #4b5563; background: #1b202b; }
    .admin-day.day-closed { border-color: rgba(239, 68, 68, 0.5); background: rgba(239, 68, 68, 0.18); color: #fca5a5; }
    .admin-day.day-holiday { border-color: rgba(251, 191, 36, 0.5); background: rgba(251, 191, 36, 0.2); color: #fcd34d; }
    .admin-day.day-selected { border-color: #f59e0b; background: #f59e0b; color: #111827; box-shadow: 0 0 18px rgba(245, 158, 11, 0.4); }
    .admin-status-card { margin-top: 16px; background: #131720; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 16px; }
    .admin-status-label { display: block; margin-bottom: 8px; color: #d1d5db; font-size: 13px; font-weight: 700; }
    .admin-status-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; }
    .admin-input, .admin-select { width: 100%; border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: #0f131a; color: #fff; padding: 10px 12px; }
    .admin-input:focus, .admin-select:focus { outline: none; border-color: #f59e0b; }
    .admin-save-btn { margin-top: 14px; border: 0; border-radius: 10px; padding: 11px 16px; font-weight: 700; color: #111827; background: linear-gradient(90deg, #f59e0b, #fbbf24); cursor: pointer; }
    .admin-save-btn:hover { filter: brightness(1.04); }
    .admin-help { margin-top: 10px; color: #a1a1aa; }
</style>

<div class="admin-calendar-wrap">
    <p class="admin-calendar-title">Calendar Setup</p>
    <h3 class="admin-calendar-subtitle">Select Date</h3>

    <div class="admin-calendar-header">
        <button type="button" wire:click="previousMonth" class="admin-calendar-nav" aria-label="Previous month">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <h4 class="admin-calendar-month">{{ $this->monthYear }}</h4>

        <button type="button" wire:click="nextMonth" class="admin-calendar-nav" aria-label="Next month">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <div class="admin-calendar-box">
        <div class="admin-calendar-weekdays">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                <div>{{ $dayName }}</div>
            @endforeach
        </div>

        <div class="admin-calendar-days">
            @foreach ($this->calendarDays as $day)
                @if ($day['isCurrentMonth'])
                    <button
                        type="button"
                        wire:click="selectDate('{{ $day['fullDate'] }}')"
                        wire:key="admin-day-{{ $day['fullDate'] }}"
                        @class([
                            'admin-day',
                            'day-closed' => $day['status'] === 'closed' && $selectedDate !== $day['fullDate'],
                            'day-holiday' => $day['status'] === 'holiday' && $selectedDate !== $day['fullDate'],
                            'day-selected' => $selectedDate === $day['fullDate'],
                        ])
                    >
                        {{ $day['date'] }}
                    </button>
                @else
                    <div class="admin-calendar-empty">{{ $day['date'] }}</div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="admin-status-card">
        <p class="admin-calendar-title" style="margin-bottom: 10px;">Date Status</p>

        @if ($selectedDate)
            <p style="color:#d4d4d8; margin:0 0 12px;">Selected date: <strong style="color:#fff;">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong></p>

            <div class="admin-status-grid">
                <div>
                    <label class="admin-status-label">Status</label>
                    <select wire:model.defer="selectedStatus" class="admin-select">
                        <option value="open">Open (default)</option>
                        <option value="closed">Closed</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>

                <div>
                    <label class="admin-status-label">Note</label>
                    <input type="text" wire:model.defer="note" class="admin-input" placeholder="Optional note" />
                </div>
            </div>

            <button type="button" wire:click="saveDateStatus" class="admin-save-btn">Save Date Status</button>
        @else
            <p class="admin-help">Select a date on the calendar first, then set it as Open, Closed, or Holiday.</p>
        @endif
    </div>
</div>
