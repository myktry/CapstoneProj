<x-filament-widgets::widget>
    <x-filament::section
        heading="Closed / Holiday Dates"
        description="Open the calendar, pick a date, then save a note for why it is unavailable."
    >
        <div style="border: 1px solid rgba(251, 191, 36, 0.12); background: #0a0a0a; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.32);">
            <div style="padding: 28px 28px 20px; border-bottom: 1px solid rgba(251, 191, 36, 0.12);">
                <p style="margin: 0; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Reserve Your Time</p>
                <h2 style="margin: 14px 0 0; color: #ffffff; font-size: 30px; line-height: 1.1; font-weight: 800;">Closed / Holiday Dates</h2>
                <p style="margin: 10px 0 0; color: #94a3b8; font-size: 14px; max-width: 760px;">Select a date, assign its status, and leave a note so the booking calendar stays clear for customers.</p>

                <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">1. Pick a date</span>
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">2. Choose status</span>
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">3. Save the note</span>
                </div>
            </div>

            <div style="padding: 24px 28px 28px;">
                <div style="display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.85fr); gap: 24px; align-items: start;">
                    <div style="border: 1px solid rgba(251, 191, 36, 0.18); background: rgba(24, 24, 27, 0.74); border-radius: 18px; padding: 18px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px;">
                            <button type="button" wire:click="previousMonth" aria-label="Previous month" style="width: 42px; height: 42px; border-radius: 999px; border: 1px solid rgba(251, 191, 36, 0.20); background: rgba(251, 191, 36, 0.08); color: #fbbf24; font-size: 20px; font-weight: 700; cursor: pointer;">‹</button>

                            <div style="text-align: center;">
                                <div style="color: rgba(251, 191, 36, 0.75); text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Month</div>
                                <div style="margin-top: 8px; color: #ffffff; font-size: 24px; font-weight: 800;">{{ $this->monthYear }}</div>
                            </div>

                            <button type="button" wire:click="nextMonth" aria-label="Next month" style="width: 42px; height: 42px; border-radius: 999px; border: 1px solid rgba(251, 191, 36, 0.20); background: rgba(251, 191, 36, 0.08); color: #fbbf24; font-size: 20px; font-weight: 700; cursor: pointer;">›</button>
                        </div>

                        <table style="width: 100%; border-collapse: separate; border-spacing: 8px; table-layout: fixed;">
                            <thead>
                                <tr>
                                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                                        <th style="padding: 0 0 4px; color: rgba(251, 191, 36, 0.70); text-transform: uppercase; letter-spacing: 0.24em; font-size: 11px; font-weight: 700; text-align: center;">{{ $weekday }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_chunk($this->calendarDays, 7) as $week)
                                    <tr>
                                        @foreach($week as $dayData)
                                            @if($dayData === null)
                                                <td style="padding: 0;">
                                                    <div style="aspect-ratio: 1 / 1; border-radius: 16px; border: 1px dashed rgba(255,255,255,0.08); background: rgba(255,255,255,0.02);"></div>
                                                </td>
                                            @else
                                                @php
                                                    $isSelected = $selectedDate === $dayData['date'];
                                                    $isToday = $dayData['isToday'] && ! $isSelected && ! $dayData['isClosed'];
                                                    $isClosed = $dayData['isClosed'] && ! $isSelected;

                                                    $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 1; border-radius: 16px; border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03); color: #e5e7eb; cursor: pointer; padding: 10px; text-align: left;';

                                                    if ($isToday) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 1; border-radius: 16px; border: 1px solid rgba(16,185,129,0.28); background: rgba(16,185,129,0.10); color: #a7f3d0; cursor: pointer; padding: 10px; text-align: left;';
                                                    }

                                                    if ($isClosed) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 1; border-radius: 16px; border: 1px solid rgba(239,68,68,0.40); background: rgba(239,68,68,0.14); color: #fecaca; cursor: pointer; padding: 10px; text-align: left;';
                                                    }

                                                    if ($isSelected) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 1; border-radius: 16px; border: 1px solid rgba(251,191,36,1); background: rgba(251,191,36,1); color: #111827; cursor: pointer; padding: 10px; text-align: left; box-shadow: 0 12px 24px rgba(251, 191, 36, 0.26);';
                                                    }

                                                    $dotStyle = 'position: absolute; right: 10px; bottom: 10px; width: 10px; height: 10px; border-radius: 999px; background: rgba(255,255,255,0.20);';
                                                    if ($isToday) {
                                                        $dotStyle = 'position: absolute; right: 10px; bottom: 10px; width: 10px; height: 10px; border-radius: 999px; background: #34d399;';
                                                    }
                                                    if ($isClosed) {
                                                        $dotStyle = 'position: absolute; right: 10px; bottom: 10px; width: 10px; height: 10px; border-radius: 999px; background: #f87171;';
                                                    }
                                                    if ($isSelected) {
                                                        $dotStyle = 'position: absolute; right: 10px; bottom: 10px; width: 10px; height: 10px; border-radius: 999px; background: #111827;';
                                                    }
                                                @endphp

                                                <td style="padding: 0;">
                                                    <button type="button" wire:click="selectDate('{{ $dayData['date'] }}')" style="{{ $cellStyle }}">
                                                        <div style="font-size: 16px; line-height: 1; font-weight: 800;">{{ $dayData['day'] }}</div>
                                                        <div style="margin-top: 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.18em; opacity: 0.72; font-weight: 700;">{{ $dayData['isToday'] ? 'Today' : ($dayData['isClosed'] ? 'Closed' : 'Open') }}</div>
                                                        <span style="{{ $dotStyle }}"></span>
                                                    </button>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div style="margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px; color: #9ca3af; font-size: 12px;">
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #34d399;"></span> Today</span>
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #f87171;"></span> Closed</span>
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #fbbf24;"></span> Selected</span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="border: 1px solid rgba(255,255,255,0.10); background: rgba(24,24,27,0.74); border-radius: 18px; padding: 18px;">
                            <div style="color: rgba(251,191,36,0.75); text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Step-by-step</div>
                            <ol style="margin: 14px 0 0; padding: 0 0 0 20px; color: #d1d5db; font-size: 14px; line-height: 1.6;">
                                <li>Pick a day directly from the calendar grid.</li>
                                <li>Choose the status for that date.</li>
                                <li>Add a note and save the blocked date.</li>
                            </ol>
                        </div>

                        @if($selectedDate)
                            <div style="border: 1px solid rgba(251,191,36,0.20); background: rgba(251,191,36,0.10); border-radius: 18px; padding: 18px;">
                                <div style="color: #fbbf24; text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Selected date</div>
                                <div style="margin-top: 8px; color: #ffffff; font-size: 22px; font-weight: 800;">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</div>
                                <div style="margin-top: 6px; color: rgba(254,243,199,0.84); font-size: 13px;">Update the availability for this date below.</div>
                            </div>

                            <div style="border: 1px solid rgba(255,255,255,0.10); background: rgba(24,24,27,0.74); border-radius: 18px; padding: 18px;">
                                <div style="display: grid; gap: 14px;">
                                    <div>
                                        <label style="display:block; margin-bottom: 8px; color: #d1d5db; font-size: 14px; font-weight: 600;">Status</label>
                                        <select wire:model="selectedStatus" style="width: 100%; border: 1px solid rgba(255,255,255,0.10); background: #09090b; color: #ffffff; border-radius: 12px; padding: 12px 12px; font-size: 14px; outline: none;">
                                            <option value="open">Open (available)</option>
                                            <option value="closed">Closed</option>
                                            <option value="holiday">Holiday</option>
                                            <option value="maintenance">Maintenance</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label style="display:block; margin-bottom: 8px; color: #d1d5db; font-size: 14px; font-weight: 600;">Note</label>
                                        <textarea wire:model="note" rows="5" placeholder="Add the reason for the unavailable date" style="width: 100%; border: 1px solid rgba(255,255,255,0.10); background: #09090b; color: #ffffff; border-radius: 12px; padding: 12px; font-size: 14px; outline: none; resize: vertical;"></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 16px; display: flex; flex-wrap: wrap; gap: 10px;">
                                    <button type="button" wire:click="saveDateStatus" style="border: 0; border-radius: 999px; padding: 12px 18px; background: linear-gradient(90deg, #f59e0b, #fbbf24); color: #111827; font-size: 14px; font-weight: 800; cursor: pointer;">Save date</button>
                                    <button type="button" wire:click="closeModal" style="border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; padding: 12px 18px; background: transparent; color: #d1d5db; font-size: 14px; font-weight: 700; cursor: pointer;">Reset</button>
                                </div>
                            </div>
                        @else
                            <div style="border: 1px dashed rgba(255,255,255,0.12); background: rgba(255,255,255,0.02); border-radius: 18px; padding: 18px; color: #9ca3af; font-size: 14px;">Select a date to reveal the editor and save the unavailable-day reason.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
