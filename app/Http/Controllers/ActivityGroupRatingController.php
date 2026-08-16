<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityResult;
use App\Models\ActivityStudentPackage;
use App\Models\ActivityStudentQuestion;
use App\Models\ActivityGroupAnswer;

class ActivityGroupRatingController extends Controller
{
    /**
     * Menampilkan halaman penilaian antaranggota.
     */
    public function index($id)
    {
        $user = Auth::user();

        // Ambil aktivitas
        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas merupakan aktivitas kelompok
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

        // Ambil penilaian yang sudah diberikan oleh siswa yang sedang login
        $ratings = ActivityGroupRating::where('id_activity', $activity->id)
            ->where('id_group', $group->id)
            ->where('id_evaluator', $user->id)
            ->get()
            ->keyBy('id_evaluated');

        // Hanya tampilkan anggota lain.
        $membersToRate = $group->members
            ->filter(function ($member) use ($user) {
                return $member->id_user != $user->id;
            })
            ->values();

        return view('siswa.activity-group-rating', [
            'activity' => $activity,
            'group' => $group,
            'membersToRate' => $membersToRate,
            'ratings' => $ratings,
            'currentUser' => $user,
        ]);
    }


    /**
     * Menyimpan penilaian anggota.
     */
    /**
     * Menyimpan penilaian anggota (Masal/Array)
     */
    public function save(Request $request, $id)
    {
        $user = Auth::user();
        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas kelompok
        if ($activity->is_group_activity !== 'yes') {
            abort(403, 'Aktivitas ini bukan aktivitas kelompok.');
        }

        // Cari kelompok siswa yang sedang login
        $group = ActivityGroup::where('id_activity', $activity->id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })
            ->first();

        if (!$group) {
            abort(403, 'Anda belum terdaftar dalam kelompok aktivitas ini.');
        }

        // 1. Validasi input array 'ratings' (0-100 poin)
        $request->validate([
            'ratings' => 'required|array',
            'ratings.*.score' => 'required|numeric|min:0|max:100',
            'ratings.*.comment' => 'nullable|string|max:1000',
        ], [
            'ratings.required' => 'Data penilaian tidak boleh kosong.',
            'ratings.*.score.required' => 'Pastikan semua poin kontribusi telah diisi.',
            'ratings.*.score.min' => 'Poin minimal adalah 0.',
            'ratings.*.score.max' => 'Poin maksimal adalah 100.',
        ]);

        // 2. Looping array dan simpan semua penilaian ke database
        // (Aturan 'tidak boleh menilai diri sendiri' sudah DIHAPUS agar sesuai konsep SCI)
        foreach ($request->ratings as $evaluatedId => $data) {

            // Pastikan orang yang dinilai memang anggota kelompok yang sama
            $isMember = $group->members()
                ->where('id_user', $evaluatedId)
                ->exists();

            if ($isMember) {
                // Simpan atau update penilaian
                ActivityGroupRating::updateOrCreate(
                    [
                        'id_activity' => $activity->id,
                        'id_group' => $group->id,
                        'id_evaluator' => $user->id,
                        'id_evaluated' => $evaluatedId,
                    ],
                    [
                        'score' => $data['score'],
                        'comment' => $data['comment'] ?? null,
                    ]
                );
            }
        }

        return back()->with(
            'success',
            'Sip! Penilaian kinerja kelompok berhasil disimpan.'
        );
    }
    /**
 * Menyelesaikan aktivitas Mode 2 kelompok.
 */
public function finish($id)
{
    $user = Auth::user();

    // Ambil aktivitas
    $activity = Activity::findOrFail($id);

    // Pastikan aktivitas adalah Mode 2
    if ($activity->evaluation_mode !== 'mode2') {
        abort(403, 'Aktivitas ini bukan Mode 2.');
    }

    // Pastikan aktivitas adalah aktivitas kelompok
    if ($activity->is_group_activity !== 'yes') {
        abort(403, 'Aktivitas ini bukan aktivitas kelompok.');
    }

    // Cari kelompok siswa
    $group = ActivityGroup::where('id_activity', $activity->id)
        ->whereHas('members', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })
        ->first();

    if (!$group) {
        abort(403, 'Anda belum terdaftar dalam kelompok aktivitas ini.');
    }

    // Cari package Mode 2 milik siswa
    $package = ActivityStudentPackage::where('id_activity', $activity->id)
        ->where('id_user', $user->id)
        ->first();

    if (!$package) {
        return back()->with(
            'error',
            'Package soal belum dibuat. Silakan mulai aktivitas terlebih dahulu.'
        );
    }

    // Jika sudah pernah submit, jangan proses ulang
    if ($package->status === 'submitted') {
        return back()->with(
            'success',
            'Aktivitas ini sudah selesai.'
        );
    }

    // Ambil soal yang memang diberikan kepada siswa ini
    $packageQuestions = ActivityStudentQuestion::with('question')
        ->where('id_package', $package->id)
        ->orderBy('question_order')
        ->get();

    $jumlahSoal = $packageQuestions->count();

    if ($jumlahSoal === 0) {
        return back()->with(
            'error',
            'Soal dalam package belum tersedia.'
        );
    }

    // Ambil jawaban siswa untuk aktivitas dan kelompok ini
    $answers = ActivityGroupAnswer::where('id_activity', $activity->id)
        ->where('id_group', $group->id)
        ->where('id_user', $user->id)
        ->get()
        ->keyBy('id_question');

    // Pastikan seluruh soal sudah dijawab
    $jumlahDijawab = $answers->count();

    if ($jumlahDijawab < $jumlahSoal) {

        $belumDijawab = $jumlahSoal - $jumlahDijawab;

        return back()->with(
            'error',
            "Masih ada {$belumDijawab} soal yang belum dijawab. Silakan lengkapi semua jawaban terlebih dahulu."
        );
    }

    // ==========================================
    // HITUNG JAWABAN BENAR
    // ==========================================

    $totalBenar = 0;

    foreach ($packageQuestions as $packageQuestion) {

        $question = $packageQuestion->question;

        if (!$question) {
            continue;
        }

        $answerRecord = $answers->get($question->id);

        if (!$answerRecord) {
            continue;
        }

        $userAnswer = trim((string) $answerRecord->answer);
        $correct = false;

        // Multiple Choice
        if ($question->type === 'MultipleChoice') {

            $correctAnswer = trim((string) $question->MC_answer);

            $correct = strtolower($userAnswer)
                === strtolower($correctAnswer);
        }

        // Short Answer
elseif ($question->type === 'ShortAnswer') {

    $answersRaw = $question->SA_answer;

    if (is_string($answersRaw)) {

        $decoded = json_decode($answersRaw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $acceptedAnswers = $decoded;
        } else {
            $acceptedAnswers = [$answersRaw];
        }

    } elseif (is_array($answersRaw)) {

        $acceptedAnswers = $answersRaw;

    } else {

        $acceptedAnswers = [];
    }

    // Normalisasi jawaban siswa
    $normalizedUserAnswer = strtolower(
        trim(
            preg_replace(
                '/\s+/',
                ' ',
                $userAnswer
            )
        )
    );

    $correct = false;

    foreach ($acceptedAnswers as $acceptedAnswer) {

        $normalizedAcceptedAnswer = strtolower(
            trim(
                preg_replace(
                    '/\s+/',
                    ' ',
                    (string) $acceptedAnswer
                )
            )
        );

        if ($normalizedAcceptedAnswer === '') {
            continue;
        }

        /*
        |--------------------------------------------------------------
        | Jawaban dianggap benar jika:
        | 1. Sama persis dengan kunci
        | 2. Jawaban siswa mengandung salah satu jawaban yang diterima
        |--------------------------------------------------------------
        */

        if (
            $normalizedUserAnswer === $normalizedAcceptedAnswer ||
            str_contains(
                $normalizedUserAnswer,
                $normalizedAcceptedAnswer
            )
        ) {
            $correct = true;
            break;
        }
    }
}

        if ($correct) {
            $totalBenar++;
        }
    }

    // ==========================================
    // HITUNG NILAI
    // ==========================================

    $nilaiAkhir = round(
        ($totalBenar / $jumlahSoal) * 100,
        2
    );

    $kkm = (int) ($activity->kkm ?? 70);

    $status = $nilaiAkhir >= $kkm
        ? 'Pass'
        : 'Remedial';

    $statusBenar = $totalBenar === $jumlahSoal;

    // ==========================================
    // HITUNG DURASI
    // ==========================================

    $start = $package->started_at
        ? \Carbon\Carbon::parse($package->started_at)
        : \Carbon\Carbon::now();

    $end = \Carbon\Carbon::now();

    $durationSeconds = max(
        0,
        $end->getTimestamp() - $start->getTimestamp()
    );

    // ==========================================
    // SIMPAN HASIL AKHIR
    // ==========================================

    ActivityResult::updateOrCreate(
        [
            'id_activity' => $activity->id,
            'id_user' => $user->id,
        ],
        [
            'result' => $nilaiAkhir,
            'bonus_poin' => 0,
            'real_poin' => 0,
            'result_status' => $status,
            'waktu_mengerjakan' => $durationSeconds,
            'total_benar' => $totalBenar,
            'start_time' => $start,
            'end_time' => $end,
            'status_benar' => $statusBenar,
            'nilai_akhir' => $nilaiAkhir,
        ]
    );

    // ==========================================
    // UPDATE STATUS PACKAGE
    // ==========================================

    $package->update([
        'submitted_at' => $end,
        'status' => 'submitted',
    ]);

    return redirect()
        ->route('siswa.aktivitas')
        ->with(
            'success',
            "Aktivitas berhasil diselesaikan. Nilai Anda: {$nilaiAkhir}"
        );
}
}
