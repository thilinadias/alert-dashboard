<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alert;

$alerts = Alert::orderBy('id')->get();
echo "TOTAL ALERTS: " . $alerts->count() . "\n";
foreach ($alerts as $a) {
    echo "ID: " . $a->id . " | Subject: " . $a->subject . " | Created: " . $a->created_at . " | MsgID: " . ($a->message_id ?? 'NULL') . "\n";
}
