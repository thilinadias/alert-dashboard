<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_resolve_owned_alert()
    {
        $user = User::factory()->create();
        $alert = Alert::factory()->create([
            'locked_by' => $user->id,
            'status' => 'open'
        ]);

        $response = $this->actingAs($user)->postJson(route('alerts.resolve', $alert), [
            'resolution_notes' => 'Fixed by restarting service.',
            'ticket_number' => 'INC-123'
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'status' => 'resolved',
            'resolution_notes' => 'Fixed by restarting service.',
            'ticket_number' => 'INC-123'
        ]);
    }

    public function test_user_cannot_resolve_unowned_alert()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $alert = Alert::factory()->create([
            'locked_by' => $otherUser->id,
            'status' => 'open'
        ]);

        $response = $this->actingAs($user)->postJson(route('alerts.resolve', $alert), [
            'resolution_notes' => 'Fixed'
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_close_resolved_alert()
    {
        $user = User::factory()->create();
        $alert = Alert::factory()->create([
            'locked_by' => $user->id,
            'status' => 'resolved'
        ]);

        $response = $this->actingAs($user)->postJson(route('alerts.close', $alert));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'status' => 'closed',
            'closed_by' => $user->id
        ]);
    }

    public function test_user_can_reopen_closed_alert()
    {
        $user = User::factory()->create();
        $alert = Alert::factory()->create([
            'locked_by' => $user->id,
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $user->id
        ]);

        $response = $this->actingAs($user)->postJson(route('alerts.reopen', $alert));

        $response->assertStatus(200);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null
        ]);
    }
}
