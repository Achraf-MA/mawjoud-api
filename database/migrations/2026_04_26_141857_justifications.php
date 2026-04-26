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
        Schema::create('justifications', function (Blueprint $table) {
        $table->id();

        $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
        $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();

        $table->string('file_path')->nullable();
        $table->text('comment')->nullable();

        $table->enum('status', ['pending','accepted','rejected'])->default('pending');

        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();

        $table->timestamps();
        $table->softDeletes();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
