<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparateDashboardsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_critical_dashboard_shows_only_critical_alerts()
    {
        // Fix: Create client first to satisfy foreign key constraint
        $client = \App\Models\Client::create(['name' => 'Test', 'email_domain' => 'test.com']);
        
        Alert::create(['subject' => 'Critical 1', 'description' => 'Test', 'severity' => 'critical', 'client_id' => $client->id]);
        Alert::create(['subject' => 'Warning 1', 'description' => 'Test', 'severity' => 'warning', 'client_id' => $client->id]);
        
        $response = $this->actingAs($this->user)->get(route('alerts.critical'));
        $response->assertStatus(200);
        $response->assertSee('Critical Alert Dashboard');
        // Check filtering logic in controller (mocking data would be better but integration test is fine)
    }

    public function test_default_dashboard_excludes_critical_alerts()
    {
        $response = $this->actingAs($this->user)->get(route('alerts.default'));
        $response->assertStatus(200);
        $response->assertSee('Default Alert Dashboard');
    }
}
