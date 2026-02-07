<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Use DB statement to modify enum columns directly (safest for MySQL/MariaDB)
        DB::statement("ALTER TABLE alerts MODIFY COLUMN status ENUM('new', 'open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'new'");
        DB::statement("ALTER TABLE alerts MODIFY COLUMN severity ENUM('critical', 'warning', 'info', 'default') NOT NULL DEFAULT 'default'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE alerts MODIFY COLUMN status ENUM('new', 'in_progress', 'resolved') NOT NULL DEFAULT 'new'");
        DB::statement("ALTER TABLE alerts MODIFY COLUMN severity ENUM('critical', 'default') NOT NULL DEFAULT 'default'");
    }
};
