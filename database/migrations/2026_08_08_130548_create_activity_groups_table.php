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
        Schema::create('activity_groups', function (Blueprint $table) {
            $table->id();

            // Aktivitas yang memiliki kelompok ini
            $table->foreignId('id_activity')
                ->constrained('activities')
                ->cascadeOnDelete();

            // Nomor urut kelompok dalam satu aktivitas
            $table->unsignedInteger('group_number');

            // Nama kelompok, misalnya Kelompok 1
            $table->string('name');

            // Cara pembentukan: manual atau random
            $table->string('formation_method', 20);

            $table->timestamps();

            // Satu aktivitas tidak boleh memiliki nomor kelompok yang sama
            $table->unique(['id_activity', 'group_number']);

            // Satu aktivitas tidak boleh memiliki nama kelompok yang sama
            $table->unique(['id_activity', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_groups');
    }
};