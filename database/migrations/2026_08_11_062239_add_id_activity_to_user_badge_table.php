<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_badge', function (Blueprint $table) {
            // Menambahkan kolom id_activity setelah id_badge
            $table->unsignedBigInteger('id_activity')->nullable()->after('id_badge');
        });
    }

    public function down()
    {
        Schema::table('user_badge', function (Blueprint $table) {
            $table->dropColumn('id_activity');
        });
    }
};