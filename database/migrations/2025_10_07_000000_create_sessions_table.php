<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();

            // Link session to user
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Session time tracking
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->unsignedInteger('duration_minutes');
            
            // Session type
            $table->boolean('is_lifetime')->default(false);
            $table->boolean('is_yearly')->default(false);
            $table->boolean('is_monthly')->default(false);
            $table->boolean('is_weekly')->default(false);
            $table->boolean('is_daily')->default(false);
            $table->unsignedInteger('start_year')->nullable();
            $table->unsignedInteger('end_year')->nullable();
            $table->unsignedInteger('start_month')->nullable();
            $table->unsignedInteger('end_month')->nullable();
            $table->unsignedInteger('start_day')->nullable();
            $table->unsignedInteger('end_day')->nullable();
            $table->unsignedInteger('start_hour')->nullable();
            $table->unsignedInteger('end_hour')->nullable();
            
            // Time selection fields for Monthly and Weekly (removed from Daily)
            $table->string('monthly_start_time')->nullable();
            $table->string('monthly_end_time')->nullable();
            $table->string('weekly_start_time')->nullable();
            $table->string('weekly_end_time')->nullable();

            // Audit columns (created_by, updated_by)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Automatic timestamps
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'start_at']);
            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['is_lifetime']);
            $table->index(['is_yearly']);
            $table->index(['is_monthly']);
            $table->index(['is_weekly']);
            $table->index(['is_daily']);

            // Foreign keys (optional but recommended)
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop FKs first to avoid constraint errors (PostgreSQL requirement)
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });

        Schema::dropIfExists('sessions');
    }
};