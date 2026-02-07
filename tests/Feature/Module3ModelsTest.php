<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Alert;
use App\Models\AlertHistory;
use App\Models\ClassificationRule;
use App\Models\SlaPolicy;

class Module3ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_relationships_and_creation()
    {
        // 1. Create SLA Policy
        $policy = SlaPolicy::create([
            'name' => 'Gold SLA',
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 60,
        ]);
        $this->assertDatabaseHas('sla_policies', ['name' => 'Gold SLA']);

        // 2. Create Client
        $client = Client::create([
            'name' => 'Acme Corp',
            'email_domain' => 'acme.com',
            'sla_policy_id' => $policy->id,
        ]);
        $this->assertDatabaseHas('clients', ['name' => 'Acme Corp']);
        $this->assertTrue($client->slaPolicy->is($policy));
        $this->assertTrue($policy->clients->contains($client));

        // 3. Create User (for locking)
        $user = User::factory()->create();

        // 4. Create Alert
        $alert = Alert::create([
            'subject' => 'Server Down',
            'description' => 'Critical server issue',
            'severity' => 'critical',
            'status' => 'new',
            'client_id' => $client->id,
            'locked_by' => $user->id,
            'locked_at' => now(),
            'ticket_number' => 'INC-1234',
        ]);
        $this->assertDatabaseHas('alerts', ['subject' => 'Server Down']);
        $this->assertTrue($alert->client->is($client));
        $this->assertTrue($alert->lockedBy->is($user));
        $this->assertTrue($client->alerts->contains($alert));

        // 5. Create Alert History
        $history = AlertHistory::create([
            'alert_id' => $alert->id,
            'user_id' => $user->id,
            'action' => 'created',
            'details' => 'Alert created via API',
        ]);
        $this->assertDatabaseHas('alert_histories', ['action' => 'created']);
        $this->assertTrue($history->alert->is($alert));
        $this->assertTrue($alert->alertHistories->contains($history));

        // 6. Create Classification Rule
        $rule = ClassificationRule::create([
            'priority' => 1,
            'rule_type' => 'subject',
            'keyword' => 'urgent',
            'target_severity' => 'critical',
            'target_client_id' => $client->id,
        ]);
        $this->assertDatabaseHas('classification_rules', ['keyword' => 'urgent']);
        $this->assertTrue($rule->targetClient->is($client));
    }
}
