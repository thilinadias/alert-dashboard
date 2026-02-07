<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->client = Client::create(['name' => 'Test Client', 'email_domain' => 'test.com']);
    }

    public function test_user_can_view_my_alerts_page()
    {
        $response = $this->actingAs($this->user)->get(route('alerts.mine'));
        $response->assertStatus(200);
        $response->assertSee('My Alerts');
    }

    public function test_user_can_take_alert()
    {
        $alert = Alert::create([
            'subject' => 'Test Alert',
            'description' => 'Test Description',
            'severity' => 'default',
            'status' => 'new',
            'client_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->user)->postJson(route('alerts.take', $alert));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'locked_by' => $this->user->id,
            'status' => 'open'
        ]);
    }

    public function test_my_alerts_only_shows_locked_alerts()
    {
        // Locked by current user
        Alert::create([
            'subject' => 'My Locked Alert',
            'description' => 'Test',
            'severity' => 'default',
            'client_id' => $this->client->id,
            'locked_by' => $this->user->id
        ]);

        // Locked by another user
        $otherUser = User::factory()->create();
        Alert::create([
            'subject' => 'Other Locked Alert',
            'description' => 'Test',
            'severity' => 'default',
            'client_id' => $this->client->id,
            'locked_by' => $otherUser->id
        ]);

        // Unlocked
        Alert::create([
            'subject' => 'Unlocked Alert',
            'description' => 'Test',
            'severity' => 'default',
            'client_id' => $this->client->id,
            'locked_by' => null
        ]);

        $response = $this->actingAs($this->user)->get(route('alerts.mine'));

        $response->assertSee('My Locked Alert');
        $response->assertDontSee('Other Locked Alert');
        $response->assertDontSee('Unlocked Alert');
    }

    public function test_user_can_release_alert()
    {
        $alert = Alert::create([
            'subject' => 'Test Alert',
            'description' => 'Test',
            'severity' => 'default',
            'client_id' => $this->client->id,
            'locked_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->postJson(route('alerts.release', $alert));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'locked_by' => null
        ]);
    }

    public function test_cannot_release_others_alert()
    {
        $otherUser = User::factory()->create();
        $alert = Alert::create([
            'subject' => 'Test Alert',
            'description' => 'Test',
            'severity' => 'default',
            'client_id' => $this->client->id,
            'locked_by' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)->postJson(route('alerts.release', $alert));

        $response->assertStatus(403);
    }
}
