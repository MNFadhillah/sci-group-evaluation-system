<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('mode2_question_source')->nullable()->change();
            $table->json('mode2_question_types')->nullable()->change();
            $table->boolean('mode2_random_questions')->nullable()->change();
            $table->boolean('mode2_random_order')->nullable()->change();
            $table->integer('jumlah_soal')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('mode2_question_source')->nullable(false)->change();
            $table->json('mode2_question_types')->nullable(false)->change();
            $table->boolean('mode2_random_questions')->nullable(false)->change();
            $table->boolean('mode2_random_order')->nullable(false)->change();
            $table->integer('jumlah_soal')->nullable(false)->change();
        });
    }
};