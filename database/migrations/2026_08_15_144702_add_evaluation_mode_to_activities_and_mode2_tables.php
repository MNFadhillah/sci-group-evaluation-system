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
        /*
        |--------------------------------------------------------------------------
        | 1. TAMBAHKAN KONFIGURASI MODE 2 KE ACTIVITIES
        |--------------------------------------------------------------------------
        |
        | Aktivitas lama otomatis menggunakan mode1.
        | Jadi Mode 1 yang sudah berjalan tetap aman.
        |
        */

        Schema::table('activities', function (Blueprint $table) {

            // Mode evaluasi:
            // mode1 = sistem pengerjaan lama
            // mode2 = distribusi soal per siswa
            $table->string('evaluation_mode')
                ->default('mode1')
                ->after('type');

            // Tipe soal yang diperbolehkan pada Mode 2.
            // Contoh:
            // ["MultipleChoice", "ShortAnswer"]
            $table->json('mode2_question_types')
                ->nullable()
                ->after('jumlah_soal');

            // Apakah pemilihan soal dilakukan secara acak
            $table->boolean('mode2_random_questions')
                ->default(true)
                ->after('mode2_question_types');

            // Apakah urutan soal pada paket siswa diacak
            $table->boolean('mode2_random_order')
                ->default(true)
                ->after('mode2_random_questions');

            // Sumber soal.
            // Untuk tahap awal kita gunakan bank soal.
            $table->string('mode2_question_source')
                ->default('bank')
                ->after('mode2_random_order');
        });


        /*
        |--------------------------------------------------------------------------
        | 2. PAKET SOAL SETIAP SISWA
        |--------------------------------------------------------------------------
        |
        | Satu aktivitas + satu siswa = satu paket.
        |
        | Contoh:
        |
        | Evaluasi Graf
        | ├── Doni  -> package 1
        | ├── Dodi  -> package 2
        | └── Andi  -> package 3
        |
        */

        Schema::create('activity_student_packages', function (Blueprint $table) {

            $table->id();

            // Aktivitas yang dikerjakan
            $table->foreignId('id_activity')
                ->constrained('activities')
                ->cascadeOnDelete();

            // Siswa yang menerima paket
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnDelete();

            // Waktu siswa mulai mengerjakan
            $table->timestamp('started_at')
                ->nullable();

            // Waktu siswa melakukan submit
            $table->timestamp('submitted_at')
                ->nullable();

            // Batas waktu khusus paket siswa
            $table->timestamp('deadline_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status pengerjaan
            |--------------------------------------------------------------------------
            |
            | not_started = belum mulai
            | in_progress = sedang mengerjakan
            | submitted   = sudah submit
            | late        = melewati batas waktu
            |
            */

            $table->enum('status', [
                'not_started',
                'in_progress',
                'submitted',
                'late',
            ])->default('not_started');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Satu siswa hanya boleh memiliki satu paket
            | untuk satu aktivitas.
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'id_activity',
                'id_user'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | 3. SOAL YANG MASUK KE PAKET SISWA
        |--------------------------------------------------------------------------
        |
        | Tabel ini menyimpan soal yang benar-benar diberikan
        | kepada masing-masing siswa.
        |
        | Contoh:
        |
        | package Doni
        | ├── question 5
        | ├── question 8
        | ├── question 12
        | ├── question 15
        | └── dst...
        |
        */

        Schema::create('activity_student_questions', function (Blueprint $table) {

    $table->id();

    // Paket soal milik siswa
    $table->foreignId('id_package')
        ->constrained('activity_student_packages')
        ->cascadeOnDelete();

    // Soal dari bank soal
    $table->foreignId('id_question')
        ->constrained('question')
        ->cascadeOnDelete();

    // Urutan soal dalam paket siswa
    $table->unsignedInteger('question_order');

    $table->timestamps();

    // Satu soal tidak boleh muncul dua kali
    // dalam paket siswa yang sama
    $table->unique([
        'id_package',
        'id_question'
    ]);

    // Nomor urutan juga unik dalam satu paket
    $table->unique([
        'id_package',
        'question_order'
    ]);
});


        /*
        |--------------------------------------------------------------------------
        | 4. HUBUNGKAN ACTIVITY_ANSWERS DENGAN PAKET SISWA
        |--------------------------------------------------------------------------
        |
        | Mode 1:
        | id_package = NULL
        |
        | Mode 2:
        | id_package = ID paket siswa
        |
        | Nullable sangat penting agar data Mode 1 lama
        | tidak rusak.
        |
        */

        Schema::table('activity_answers', function (Blueprint $table) {

            $table->foreignId('id_package')
                ->nullable()
                ->after('id_user')
                ->constrained('activity_student_packages')
                ->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Hapus hubungan activity_answers terlebih dahulu
        |--------------------------------------------------------------------------
        */

        Schema::table('activity_answers', function (Blueprint $table) {

            $table->dropForeign([
                'id_package'
            ]);

            $table->dropColumn('id_package');
        });


        /*
        |--------------------------------------------------------------------------
        | Hapus tabel soal paket
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('activity_student_questions');


        /*
        |--------------------------------------------------------------------------
        | Hapus tabel paket siswa
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('activity_student_packages');


        /*
        |--------------------------------------------------------------------------
        | Hapus konfigurasi Mode 2
        |--------------------------------------------------------------------------
        */

        Schema::table('activities', function (Blueprint $table) {

            $table->dropColumn([
                'evaluation_mode',
                'mode2_question_types',
                'mode2_random_questions',
                'mode2_random_order',
                'mode2_question_source',
            ]);
        });
    }
};