<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCalendarManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_calendar_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/calendar-management');

        $response->assertStatus(200);
    }
}
