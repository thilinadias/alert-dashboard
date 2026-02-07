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
        Schema::create('classification_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('priority')->default(0);
            $table->enum('rule_type', ['subject', 'body', 'sender']);
            $table->string('keyword');
            $table->enum('target_severity', ['critical', 'warning', 'info', 'default']);
            $table->foreignId('target_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_rules');
    }
};
