<?php

namespace Tests\Feature;

use App\Models\ClassificationRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Module5ClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create admin role and user
        $role = Role::create(['name' => 'admin']);
        $this->admin = User::factory()->create();
        // Manually attach role to bypass package alias issues in test env
        $this->admin->roles()->attach($role);
    }

    public function test_admin_can_view_classification_rules()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.classification-rules.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_classification_rule()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classification-rules.store'), [
            'keyword' => 'urgent_test',
            'rule_type' => 'subject',
            'target_severity' => 'critical',
            'priority' => 1,
        ]);

        $response->assertRedirect(route('admin.classification-rules.index'));
        $this->assertDatabaseHas('classification_rules', ['keyword' => 'urgent_test']);
    }

    public function test_rules_are_ordered_by_priority()
    {
        ClassificationRule::create(['keyword' => 'low', 'rule_type' => 'body', 'target_severity' => 'info', 'priority' => 10]);
        ClassificationRule::create(['keyword' => 'high', 'rule_type' => 'body', 'target_severity' => 'critical', 'priority' => 1]);

        $response = $this->actingAs($this->admin)->get(route('admin.classification-rules.index'));
        
        $rules = $response->viewData('rules');
        $this->assertEquals('high', $rules->first()->keyword);
        $this->assertEquals('low', $rules->last()->keyword);
    }
}
