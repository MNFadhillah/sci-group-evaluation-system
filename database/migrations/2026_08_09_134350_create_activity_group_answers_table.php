<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_group_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_activity');
            $table->unsignedBigInteger('id_group');
            $table->unsignedBigInteger('id_question');
            $table->unsignedBigInteger('id_user');

            $table->text('answer')->nullable();

            $table->timestamps();

            $table->unique(
                ['id_activity', 'id_group', 'id_question', 'id_user'],
                'group_answer_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_group_answers');
    }
};