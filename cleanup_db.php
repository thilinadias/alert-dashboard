<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alert;
use Illuminate\Support\Facades\DB;

// 1. Group by subject and find duplicates
$alerts = Alert::orderBy('id')->get();
$seen = [];
$toDelete = [];

foreach ($alerts as $a) {
    $key = $a->subject . '|' . $a->description;
    if (isset($seen[$key])) {
        // We already have one, this is a duplicate.
        $toDelete[] = $a->id;
    } else {
        $seen[$key] = $a->id;
    }
}

echo "IDs to delete: " . implode(', ', $toDelete) . "\n";

if (!empty($toDelete)) {
    Alert::whereIn('id', $toDelete)->delete();
    echo "Deleted " . count($toDelete) . " duplicate alerts.\n";
}

// 2. Try to populate message_id for the remaining alerts from history
$remaining = Alert::whereNull('message_id')->get();
foreach ($remaining as $r) {
    $history = $r->alertHistories()->where('details', 'like', '%Message ID:%')->first();
    if ($history) {
        if (preg_match('/Message ID: (.*)/', $history->details, $m)) {
            $msgId = trim($m[1]);
            try {
                $r->update(['message_id' => $msgId]);
                echo "Updated ID {$r->id} with MsgID {$msgId}\n";
            } catch (\Exception $e) {
                echo "Failed to update ID {$r->id}: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "FINAL COUNT: " . Alert::count() . "\n";
foreach (Alert::all() as $f) {
    echo "ID: {$f->id} | Subject: {$f->subject} | MsgID: " . ($f->message_id ?? 'NULL') . "\n";
}
