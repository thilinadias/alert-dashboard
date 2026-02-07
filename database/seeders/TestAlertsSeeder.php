<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\SlaPolicy;
use App\Models\Alert;
use App\Models\User;
use Carbon\Carbon;

class TestAlertsSeeder extends Seeder
{
    public function run()
    {
        // 1. Create SLA Policies
        $goldSla = SlaPolicy::create([
            'name' => 'Gold (15m Response)',
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 60
        ]);

        $silverSla = SlaPolicy::create([
            'name' => 'Silver (1h Response)',
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 240
        ]);

        // 2. Create Clients
        $clientA = Client::create(['name' => 'Acme Corp', 'email_domain' => 'acme.com', 'sla_policy_id' => $goldSla->id]);
        $clientB = Client::create(['name' => 'Globex', 'email_domain' => 'globex.com', 'sla_policy_id' => $silverSla->id]);

        // 3. Create Alerts with different states

        // CAS E 1: OVERDUE (Critical & Blinking)
        // Created 30 mins ago, Gold SLA is 15 mins -> Overdue by 15 mins
        Alert::create([
            'subject' => 'CRITICAL: Server Down - Production DB',
            'description' => 'The main production database is not responding to ping requests. Code 500 errors on web.',
            'severity' => 'critical',
            'status' => 'new',
            'client_id' => $clientA->id,
            'device' => 'DB-PROD-01',
            'ticket_number' => 'INC-9001',
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        // CASE 2: WARNING (接近 SLA)
        // Created 50 mins ago, Silver SLA is 60 mins -> 10 mins left (Warning threshold is 15 mins)
        Alert::create([
            'subject' => 'High CPU Usage on Web Server',
            'description' => 'CPU usage is consistently above 90% for the last 5 minutes.',
            'severity' => 'warning',
            'status' => 'open',
            'client_id' => $clientB->id,
            'device' => 'WEB-02',
            'ticket_number' => 'INC-9002',
            'created_at' => Carbon::now()->subMinutes(50),
        ]);

        // CASE 3: NEW / OK
        // Created 1 min ago, Gold SLA is 15 mins -> 14 mins left
        Alert::create([
            'subject' => 'Disk Space Low - Backup Server',
            'description' => 'Free space is below 10% on volume D:',
            'severity' => 'info',
            'status' => 'new',
            'client_id' => $clientA->id,
            'device' => 'BKP-01',
            'ticket_number' => 'INC-9003',
            'created_at' => Carbon::now()->subMinute(),
        ]);

        // CASE 4: LOCKED / TAKEN
        $admin = User::first(); // Assuming admin exists
        Alert::create([
            'subject' => 'Network Latency Spike',
            'description' => 'User reports slow connection to VPN.',
            'severity' => 'default',
            'status' => 'open',
            'client_id' => $clientB->id,
            'device' => 'VPN-GW',
            'ticket_number' => 'INC-9004',
            'created_at' => Carbon::now()->subMinutes(10),
            'locked_by' => $admin ? $admin->id : null,
            'locked_at' => Carbon::now(),
        ]);

        $this->command->info('Test alerts seeded successfully!');
    }
}
