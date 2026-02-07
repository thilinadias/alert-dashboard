<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Client;
use App\Models\SlaPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module6DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('alerts.index'));
        $response->assertStatus(200);
    }

    public function test_sla_calculation()
    {
        $policy = SlaPolicy::create([
            'name' => 'Gold',
            'response_time_minutes' => 30,
            'resolution_time_minutes' => 120
        ]);

        $client = Client::create([
            'name' => 'Gold Client',
            'email_domain' => 'gold.com',
            'sla_policy_id' => $policy->id
        ]);

        // Use DB directly to bypass potential model issues in test env
        $clientId = \Illuminate\Support\Facades\DB::table('clients')->insertGetId([
            'name' => 'Gold Client',
            'email_domain' => 'gold.com',
            'sla_policy_id' => $policy->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $alert = Alert::create([
            'subject' => 'Test Alert',
            'description' => 'Test',
            'severity' => 'critical',
            'status' => 'new',
            'client_id' => $clientId,
            'ticket_number' => 'TKT-123'
        ]);

        // Verify deadline is roughly 30 mins from now
        $this->assertTrue($alert->getSlaDeadline()->diffInMinutes(now()->addMinutes(30)) < 1);
        
        // Verify not overdue yet
        $this->assertFalse($alert->isOverdue());

        // Update created_at to simulate overdue
        $alert->created_at = now()->subMinutes(31);
        $alert->save();

        $this->assertTrue($alert->isOverdue());
        $this->assertEquals('critical', $alert->getSlaStatus());
    }

    public function test_user_can_take_alert()
    {
        $alert = Alert::create([
            'subject' => 'Free Alert',
            'description' => 'Test',
            'severity' => 'default',
            'status' => 'new',
            'ticket_number' => 'TKT-FREE'
        ]);

        $response = $this->actingAs($this->user)->postJson(route('alerts.take', $alert));
        
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertEquals($this->user->id, $alert->fresh()->locked_by);
        $this->assertEquals('open', $alert->fresh()->status);
    }
}
