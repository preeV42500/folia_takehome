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
        Schema::create('recurrence_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reminder_id');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'yearly', 'custom']);
            $table->integer('frequency');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign('reminder_id')->references('id')->on('reminders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('recurrence_rules');
    }
};