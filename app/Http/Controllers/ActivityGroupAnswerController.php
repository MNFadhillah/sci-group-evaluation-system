<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\aktivitasController;
use App\Models\ActivityStudentPackage;
use App\Models\ActivityStudentQuestion;

class ActivityGroupAnswerController extends Controller
{
    /**
     * Menampilkan halaman pengerjaan aktivitas kelompok.
     */
    public function show($id)
    {
        $user = Auth::user();
        

        // Ambil aktivitas
        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas memang aktivitas kelompok
        if ($activity->is_group_activity !== 'yes') {
            abort(403, 'Aktivitas ini bukan aktivitas kelompok.');
        }

        // Cari kelompok tempat siswa berada
        $group = ActivityGroup::with([
            'members.user',
        ])
            ->where('id_activity', $activity->id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })
            ->first();
        

        if (!$group) {
            abort(403, 'Anda belum terdaftar dalam kelompok aktivitas ini.');
        }

        // ======================================================
// MODE 2
// ======================================================
// Jika aktivitas menggunakan Mode 2, pastikan package
// soal siswa sudah dibuat terlebih dahulu.

if ($activity->evaluation_mode === 'mode2') {

    // Jalankan generator package Mode 2.
    // Jika package sudah ada, fungsi ini tidak membuat ulang.
    app(aktivitasController::class)->startMode2($activity->id);

    // Ambil package milik siswa ini.
    $package = ActivityStudentPackage::where('id_activity', $activity->id)
        ->where('id_user', $user->id)
        ->first();

    if (!$package) {
        abort(404, 'Package soal siswa belum tersedia.');
    }

    // Ambil soal berdasarkan urutan package siswa.
    $packageQuestions = ActivityStudentQuestion::with('question')
        ->where('id_package', $package->id)
        ->orderBy('question_order')
        ->get();

    if ($packageQuestions->isEmpty()) {
        abort(404, 'Soal untuk package ini belum tersedia.');
    }

    // Ubah menjadi collection Question agar Blade
    // tetap bisa menggunakan $question->id, $question->type, dll.
    $questions = $packageQuestions
        ->map(function ($item) {
            return $item->question;
        })
        ->filter()
        ->values();

} else {

    // ==================================================
    // MODE 1 / SISTEM LAMA
    // ==================================================

    // Ambil soal sesuai mode evaluasi
if ($activity->evaluation_mode === 'mode2') {

    // Ambil package milik siswa yang sedang login
    $package = ActivityStudentPackage::where('id_activity', $activity->id)
        ->where('id_user', $user->id)
        ->where('status', 'in_progress')
        ->first();

    if (!$package) {
        abort(404, 'Paket soal Mode 2 belum tersedia.');
    }

    // Ambil soal berdasarkan urutan package
    $questions = $package->studentQuestions()
        ->with('question')
        ->orderBy('question_order')
        ->get()
        ->map(function ($item) {
            return $item->question;
        });

} else {

    // Mode 1 tetap menggunakan soal aktivitas biasa
    $questions = $activity->questions()
        ->orderBy('activity_question.id_question')
        ->get();
}

if ($questions->isEmpty()) {
    abort(404, 'Soal untuk aktivitas ini belum tersedia.');
}
}

        // Ambil jawaban yang sudah tersimpan
        $answers = ActivityGroupAnswer::where('id_activity', $activity->id)
            ->where('id_group', $group->id)
            ->get()
            ->keyBy(function ($answer) {
                return $answer->id_question . '_' . $answer->id_user;
            });

        return view('siswa.activity-group-answer', [
            'activity' => $activity,
            'group' => $group,
            'questions' => $questions,
            'answers' => $answers,
            'currentUser' => $user,
        ]);
    }


    /**
     * Menyimpan jawaban anggota kelompok.
     */
    public function save(Request $request, $id)
    {
        $user = Auth::user();

        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas kelompok
        if ($activity->is_group_activity !== 'yes') {
            abort(403, 'Aktivitas ini bukan aktivitas kelompok.');
        }

        // Pastikan siswa memang anggota kelompok aktivitas
        $group = ActivityGroup::where('id_activity', $activity->id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })
            ->first();

        if (!$group) {
            abort(403, 'Anda belum terdaftar dalam kelompok aktivitas ini.');
        }

        $request->validate([
            'question_id' => ['required', 'integer', 'exists:question,id'],
            'answer' => ['nullable', 'string'],
        ]);

        // Pastikan soal memang bagian dari aktivitas
if ($activity->evaluation_mode === 'mode2') {

    // Untuk Mode 2, soal harus berasal dari package siswa
    $package = ActivityStudentPackage::where('id_activity', $activity->id)
        ->where('id_user', $user->id)
        ->where('status', 'in_progress')
        ->latest('id')
        ->first();

    if (!$package) {
        abort(403, 'Paket soal Mode 2 belum tersedia.');
    }

    $questionExists = $package->questions()
        ->where('id_question', $request->question_id)
        ->exists();

} else {

    // Mode 1 tetap menggunakan soal aktivitas biasa
    $questionExists = $activity->questions()
        ->where('question.id', $request->question_id)
        ->exists();
}

if (!$questionExists) {
    abort(403, 'Soal tidak termasuk dalam aktivitas ini.');
}

        // Simpan atau update jawaban siswa
        ActivityGroupAnswer::updateOrCreate(
            [
                'id_activity' => $activity->id,
                'id_group' => $group->id,
                'id_question' => $request->question_id,
                'id_user' => $user->id,
            ],
            [
                'answer' => $request->answer,
            ]
        );

        return back()->with('success', 'Jawaban berhasil disimpan.');
    }
}