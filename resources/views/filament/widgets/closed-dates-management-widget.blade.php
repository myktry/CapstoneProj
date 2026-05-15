<x-filament-widgets::widget>
    <x-filament::section
        heading="Closed / Holiday Dates"
        description="Open the calendar, pick a date, then save a note for why it is unavailable."
    >
        <div style="border: 1px solid rgba(251, 191, 36, 0.12); background: #0a0a0a; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.32); width: 100%;">
            <div style="padding: 20px 24px 16px; border-bottom: 1px solid rgba(251, 191, 36, 0.12);">
                <p style="margin: 0; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Reserve Your Time</p>
                <h2 style="margin: 10px 0 0; color: #ffffff; font-size: 26px; line-height: 1.1; font-weight: 800;">Closed / Holiday Dates</h2>
                <p style="margin: 8px 0 0; color: #94a3b8; font-size: 13px; max-width: 760px;">Select a date, assign its status, and leave a note so the booking calendar stays clear for customers.</p>

                <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px;">
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">1. Pick a date</span>
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">2. Choose status</span>
                    <span style="display: inline-block; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.04); color: #d1d5db; font-size: 12px;">3. Save the note</span>
                </div>
            </div>

            <div style="padding: 18px 24px 22px;">
                <div style="display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(240px, 0.75fr); gap: 18px; align-items: start;">
                    <div style="border: 1px solid rgba(251, 191, 36, 0.18); background: rgba(24, 24, 27, 0.74); border-radius: 18px; padding: 16px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                            <button type="button" wire:click="previousMonth" aria-label="Previous month" style="width: 38px; height: 38px; border-radius: 999px; border: 1px solid rgba(251, 191, 36, 0.20); background: rgba(251, 191, 36, 0.08); color: #fbbf24; font-size: 20px; font-weight: 700; cursor: pointer;">‹</button>

                            <div style="text-align: center;">
                                <div style="color: rgba(251, 191, 36, 0.75); text-transform: uppercase; letter-spacing: 0.3em; font-size: 10px; font-weight: 700;">Month</div>
                                <div style="margin-top: 4px; color: #ffffff; font-size: 22px; font-weight: 800;">{{ $this->monthYear }}</div>
                            </div>

                            <button type="button" wire:click="nextMonth" aria-label="Next month" style="width: 38px; height: 38px; border-radius: 999px; border: 1px solid rgba(251, 191, 36, 0.20); background: rgba(251, 191, 36, 0.08); color: #fbbf24; font-size: 20px; font-weight: 700; cursor: pointer;">›</button>
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

                                                    $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 0.94; border-radius: 14px; border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.03); color: #e5e7eb; cursor: pointer; padding: 8px; text-align: left;';

                                                    if ($isToday) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 0.94; border-radius: 14px; border: 1px solid rgba(16,185,129,0.28); background: rgba(16,185,129,0.10); color: #a7f3d0; cursor: pointer; padding: 8px; text-align: left;';
                                                    }

                                                    if ($isClosed) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 0.94; border-radius: 14px; border: 1px solid rgba(239,68,68,0.40); background: rgba(239,68,68,0.14); color: #fecaca; cursor: pointer; padding: 8px; text-align: left;';
                                                    }

                                                    if ($isSelected) {
                                                        $cellStyle = 'position: relative; width: 100%; aspect-ratio: 1 / 0.94; border-radius: 14px; border: 1px solid rgba(251,191,36,1); background: rgba(251,191,36,1); color: #111827; cursor: pointer; padding: 8px; text-align: left; box-shadow: 0 12px 24px rgba(251, 191, 36, 0.26);';
                                                    }

                                                    $dotStyle = 'position: absolute; right: 8px; bottom: 8px; width: 9px; height: 9px; border-radius: 999px; background: rgba(255,255,255,0.20);';
                                                    if ($isToday) {
                                                        $dotStyle = 'position: absolute; right: 8px; bottom: 8px; width: 9px; height: 9px; border-radius: 999px; background: #34d399;';
                                                    }
                                                    if ($isClosed) {
                                                        $dotStyle = 'position: absolute; right: 8px; bottom: 8px; width: 9px; height: 9px; border-radius: 999px; background: #f87171;';
                                                    }
                                                    if ($isSelected) {
                                                        $dotStyle = 'position: absolute; right: 8px; bottom: 8px; width: 9px; height: 9px; border-radius: 999px; background: #111827;';
                                                    }
                                                @endphp

                                                <td style="padding: 0;">
                                                    <button type="button" wire:click="selectDate('{{ $dayData['date'] }}')" style="{{ $cellStyle }}">
                                                        <div style="font-size: 15px; line-height: 1; font-weight: 800;">{{ $dayData['day'] }}</div>
                                                        <div style="margin-top: 6px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.16em; opacity: 0.72; font-weight: 700;">{{ $dayData['isToday'] ? 'Today' : ($dayData['isClosed'] ? 'Closed' : 'Open') }}</div>
                                                        <span style="{{ $dotStyle }}"></span>
                                                    </button>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px; color: #9ca3af; font-size: 12px;">
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #34d399;"></span> Today</span>
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #f87171;"></span> Closed</span>
                            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; background: rgba(255,255,255,0.03);"><span style="width: 8px; height: 8px; border-radius: 999px; background: #fbbf24;"></span> Selected</span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="border: 1px solid rgba(255,255,255,0.10); background: rgba(24,24,27,0.74); border-radius: 18px; padding: 16px;">
                            <div style="color: rgba(251,191,36,0.75); text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Step-by-step</div>
                            <ol style="margin: 10px 0 0; padding: 0 0 0 18px; color: #d1d5db; font-size: 13px; line-height: 1.45;">
                                <li>Pick a day directly from the calendar grid.</li>
                                <li>Choose the status for that date.</li>
                                <li>Add a note and save the blocked date.</li>
                            </ol>
                        </div>

                        @if($selectedDate)
                            <div style="border: 1px solid rgba(251,191,36,0.20); background: rgba(251,191,36,0.10); border-radius: 18px; padding: 16px;">
                                <div style="color: #fbbf24; text-transform: uppercase; letter-spacing: 0.3em; font-size: 11px; font-weight: 700;">Selected date</div>
                                <div style="margin-top: 6px; color: #ffffff; font-size: 20px; font-weight: 800;">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</div>
                                <div style="margin-top: 4px; color: rgba(254,243,199,0.84); font-size: 12px;">Update the availability for this date below.</div>
                            </div>

                            <div
                                style="border: 1px solid rgba(255,255,255,0.10); background: rgba(24,24,27,0.74); border-radius: 18px; padding: 16px;"
                                x-data="{
                                    busy: false,
                                    error: '',
                                    async saveDate() {
                                        if (this.busy) return;
                                        this.busy = true;
                                        this.error = '';
                                        try {
                                            const status = $wire.selectedStatus;
                                            if (status === 'open') {
                                                await $wire.saveDateStatus(null);
                                                return;
                                            }
                                            if (!window.StegoDemo?.generateClosedDateMetadataPng) {
                                                this.error = 'Steganography bundle not loaded. Run npm run build and refresh.';
                                                return;
                                            }
                                            const png = await window.StegoDemo.generateClosedDateMetadataPng({
                                                closedDateId: $wire.closedDateId,
                                                date: $wire.selectedDate,
                                                type: status,
                                                note: $wire.note ?? '',
                                            });
                                            await $wire.saveDateStatus(png);
                                        } catch (e) {
                                            this.error = e?.message ? String(e.message) : String(e);
                                        } finally {
                                            this.busy = false;
                                        }
                                    }
                                }"
                            >
                                <div style="display: grid; gap: 12px;">
                                    <div>
                                        <label style="display:block; margin-bottom: 6px; color: #d1d5db; font-size: 13px; font-weight: 600;">Status</label>
                                        <select wire:model="selectedStatus" style="width: 100%; border: 1px solid rgba(255,255,255,0.10); background: #09090b; color: #ffffff; border-radius: 12px; padding: 10px 12px; font-size: 13px; outline: none;">
                                            <option value="open">Open (available)</option>
                                            <option value="closed">Closed</option>
                                            <option value="holiday">Holiday</option>
                                        </select>
                                        @error('selectedStatus')
                                            <p style="margin-top: 6px; font-size: 12px; color: #f87171;">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label style="display:block; margin-bottom: 6px; color: #d1d5db; font-size: 13px; font-weight: 600;">Note</label>
                                        <textarea wire:model="note" rows="4" placeholder="Add the reason for the unavailable date" style="width: 100%; border: 1px solid rgba(255,255,255,0.10); background: #09090b; color: #ffffff; border-radius: 12px; padding: 10px; font-size: 13px; outline: none; resize: vertical;"></textarea>
                                    </div>
                                </div>

                                <p x-show="error" x-text="error" x-cloak style="margin-top: 10px; font-size: 12px; color: #f87171;"></p>

                                <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                                    <button
                                        type="button"
                                        x-on:click="saveDate()"
                                        x-bind:disabled="busy"
                                        style="border: 0; border-radius: 999px; padding: 10px 16px; background: linear-gradient(90deg, #f59e0b, #fbbf24); color: #111827; font-size: 13px; font-weight: 800; cursor: pointer;"
                                        x-bind:style="busy ? 'opacity: 0.6; cursor: wait;' : ''"
                                    >
                                        <span x-show="!busy">Save date</span>
                                        <span x-show="busy" x-cloak>Saving…</span>
                                    </button>
                                    <button type="button" wire:click="closeModal" x-bind:disabled="busy" style="border: 1px solid rgba(255,255,255,0.10); border-radius: 999px; padding: 10px 16px; background: transparent; color: #d1d5db; font-size: 13px; font-weight: 700; cursor: pointer;">Reset</button>
                                </div>
                            </div>
                        @else
                            <div style="border: 1px dashed rgba(255,255,255,0.12); background: rgba(255,255,255,0.02); border-radius: 18px; padding: 16px; color: #9ca3af; font-size: 13px;">Select a date to reveal the editor and save the unavailable-day reason.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
