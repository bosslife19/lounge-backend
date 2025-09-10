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
Schema::create('mentor_user', function (Blueprint $table) {
    $table->id();

    // The mentee (regular user)
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

    // The mentor (also a user, but with is_mentor = true)
    $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();

    // Optional extras
    $table->timestamp('matched_at')->nullable(); // when match was confirmed
    $table->timestamps();

    // Ensure no duplicates
    $table->unique(['user_id', 'mentor_id']);
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_user');
    }
};
