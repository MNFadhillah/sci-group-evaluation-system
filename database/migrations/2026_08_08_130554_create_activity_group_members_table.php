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
        Schema::create('activity_group_members', function (Blueprint $table) {
            $table->id();

            // Kelompok yang diikuti siswa
            $table->foreignId('id_group')
                ->constrained('activity_groups')
                ->cascadeOnDelete();

            // Siswa yang menjadi anggota kelompok
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Satu siswa hanya boleh satu kali
            // berada dalam kelompok yang sama
            $table->unique(['id_group', 'id_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_group_members');
    }
};