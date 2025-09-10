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
        Schema::create('marches', function (Blueprint $table) {
 $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete(); // mentors are also users
    $table->enum('user_status', ['pending', 'accepted', 'rejected'])->default('pending');
    $table->enum('mentor_status', ['pending', 'accepted', 'rejected'])->default('pending');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marches');
    }
};
