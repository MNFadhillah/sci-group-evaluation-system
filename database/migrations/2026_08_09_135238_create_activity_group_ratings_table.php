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
        Schema::create('activity_group_ratings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_activity');
            $table->unsignedBigInteger('id_group');

            // Orang yang memberikan penilaian
            $table->unsignedBigInteger('id_evaluator');

            // Orang yang dinilai
            $table->unsignedBigInteger('id_evaluated');

            // Nilai kinerja
            $table->unsignedTinyInteger('score');

            // Komentar/catatan penilaian
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->foreign('id_activity')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');

            $table->foreign('id_group')
                ->references('id')
                ->on('activity_groups')
                ->onDelete('cascade');

            $table->foreign('id_evaluator')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('id_evaluated')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Satu anggota hanya boleh menilai anggota tertentu
            // satu kali untuk satu aktivitas.
            $table->unique(
    ['id_activity', 'id_group', 'id_evaluator', 'id_evaluated'],
    'group_rating_unique'
);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_group_ratings');
    }
};