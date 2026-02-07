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
        Schema::table('alerts', function (Blueprint $table) {
            $table->text('resolution_notes')->nullable()->after('description');
            $table->unsignedBigInteger('closed_by')->nullable()->after('locked_at');
            $table->timestamp('closed_at')->nullable()->after('closed_by');
            
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            //
        });
    }
};
