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
        Schema::table('mentor_user', function (Blueprint $table) {
            // Drop the wrong FK first
            $table->dropForeign(['mentor_id']);
            // Re-add correctly pointing to users table
            $table->foreign('mentor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_user', function (Blueprint $table) {
            $table->dropForeign(['mentor_id']);
            $table->foreign('mentor_id')->references('id')->on('mentors')->cascadeOnDelete();
        });
    }
};
