<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityGroup;
use Illuminate\Http\Request;

class EvaluasiController extends Controller
{
    /**
     * Menampilkan halaman Monitoring Guru (Daftar Kelompok & Progres)
     */
    public function detailJawaban($activityId)
    {
        // 1. Ambil data aktivitas utama
        $activity = Activity::findOrFail($activityId);

        // Pastikan ini aktivitas kelompok (opsional, sebagai validasi tambahan)
        if ($activity->is_group_activity !== 'yes') {
            return back()->with('swal', [
                'icon' => 'warning',
                'title' => 'Bukan Tugas Kelompok',
                'text' => 'Fitur monitoring ini hanya untuk aktivitas yang dikerjakan secara berkelompok.'
            ]);
        }

        // 2. Tarik data Kelompok beserta relasinya (Anggota + User + Jawaban)
        $groups = ActivityGroup::with(['members.user', 'answers'])
            ->where('id_activity', $activityId)
            ->orderBy('group_number', 'asc')
            ->get();

        // 3. Olah data untuk menghitung persentase progres & status anggota
        foreach ($groups as $group) {
            $totalMembers = $group->members->count();

            // Ambil daftar id_user yang sudah memiliki jawaban di tabel activity_group_answers
            $submittedUserIds = $group->answers->pluck('id_user')->unique()->toArray();
            $submittedCount = count($submittedUserIds);

            // Kalkulasi Persentase (0 - 100%)
            $progressPercentage = $totalMembers > 0
                ? round(($submittedCount / $totalMembers) * 100)
                : 0;

            // Tempelkan hasil kalkulasi ke object $group agar mudah dipanggil di Blade View
            $group->total_members = $totalMembers;
            $group->submitted_count = $submittedCount;
            $group->progress_percentage = $progressPercentage;

            // Tandai status submit masing-masing anggota kelompok
            foreach ($group->members as $member) {
                $member->has_submitted = in_array($member->id_user, $submittedUserIds);
            }

            // =========================================================
            // 4. CEK STATUS PENILAIAN & NILAI KELOMPOK (REVERSE SCI)
            // =========================================================
            $group->is_graded = false;
            $group->nilai_kelompok = null;

            if ($totalMembers > 0) {
                // Cek apakah anggota pertama di kelompok ini sudah memiliki nilai di database
                $firstMemberId = $group->members->first()->id_user;
                $result = \Illuminate\Support\Facades\DB::table('activity_result')
                    ->where('id_activity', $activityId)
                    ->where('id_user', $firstMemberId)
                    ->first();

                // Jika ada datanya, artinya kelompok ini TELAH DINILAI
                if ($result) {
                    $group->is_graded = true;

                    // Lakukan perhitungan mundur (Nilai Akhir / SCI) untuk mendapat Nilai Mentah Kelompok
                    $totalPr = 0;
                    $userPrScores = [];

                    foreach ($group->members as $m) {
                        $avgRating = \Illuminate\Support\Facades\DB::table('activity_group_ratings')
                            ->where('id_activity', $activityId)
                            ->where('id_group', $group->id)
                            ->where('id_evaluated', $m->id_user)
                            ->avg('score');

                        $pr = $avgRating !== null ? (float) $avgRating : 100;
                        $userPrScores[$m->id_user] = $pr;
                        $totalPr += $pr;
                    }

                    $groupAvg = $totalPr / $totalMembers;
                    $myPr = $userPrScores[$firstMemberId];

                    // Rumus menemukan SCI
                    $sci = $groupAvg > 0 ? ($myPr / $groupAvg) : 1;

                    // Tarik nilai akhir dari database
                    $nilaiAkhir = $result->nilai_akhir ?? $result->result;

                    // Kembalikan ke Nilai Mentah Guru
                    if ($sci > 0) {
                        $nilaiKelompok = round($nilaiAkhir / $sci);
                        $group->nilai_kelompok = $nilaiKelompok > 100 ? 100 : $nilaiKelompok;
                    } else {
                        $group->nilai_kelompok = $nilaiAkhir;
                    }
                }
            }
        }

        // 5. Lempar data yang sudah matang ke Blade View
        return view('guru.evaluasi.detail-jawaban', compact('activity', 'groups'));
    }

    /**
     * Menampilkan Halaman Lembar Jawaban & Form Penilaian Guru
     */
    public function formPenilaian($activityId, $groupId)
    {
        $activity = Activity::findOrFail($activityId);
        $group = ActivityGroup::with(['members.user'])->findOrFail($groupId);

        // Tarik jawaban kelompok dari database
        $answers = \Illuminate\Support\Facades\DB::table('activity_group_answers')
            ->join('question', 'activity_group_answers.id_question', '=', 'question.id')
            ->join('users', 'activity_group_answers.id_user', '=', 'users.id')
            ->where('activity_group_answers.id_activity', $activityId)
            ->where('activity_group_answers.id_group', $groupId)
            ->select('activity_group_answers.*', 'question.question as soal', 'question.type', 'users.name as penjawab')
            ->get();

        // Tarik hasil rating antar teman (Peer Review)
        $ratings = \Illuminate\Support\Facades\DB::table('activity_group_ratings')
            ->join('users as evaluator', 'activity_group_ratings.id_evaluator', '=', 'evaluator.id')
            ->join('users as evaluated', 'activity_group_ratings.id_evaluated', '=', 'evaluated.id')
            ->where('activity_group_ratings.id_activity', $activityId)
            ->where('activity_group_ratings.id_group', $groupId)
            ->select('activity_group_ratings.*', 'evaluator.name as evaluator_name', 'evaluated.name as evaluated_name')
            ->get();

        return view('guru.evaluasi.penilaian-kelompok', compact('activity', 'group', 'answers', 'ratings'));
    }

    /**
     * Menyimpan nilai guru dan mengkalkulasi SCI (Student Contribution Index)
     */
    public function simpanPenilaian(Request $request, $activityId, $groupId)
    {
        // 1. Validasi input nilai kelompok (0-100)
        $request->validate([
            'nilai_kelompok' => 'required|numeric|min:0|max:100'
        ]);

        $nilaiKelompok = $request->nilai_kelompok;

        // Ambil data activity untuk mengetahui class_id (Opsional, untuk disimpan di user_badge)
        $activity = Activity::with('topic.subject')->findOrFail($activityId);
        $classId = optional(optional($activity->topic)->subject)->id_class;

        // 2. Ambil daftar user/anggota di kelompok tersebut
        $members = \Illuminate\Support\Facades\DB::table('activity_group_members')
            ->where('id_group', $groupId)
            ->pluck('id_user')
            ->toArray();

        if (empty($members)) {
            return back()->with('error', 'Gagal: Kelompok ini tidak memiliki anggota.');
        }

        $prScores = [];
        $totalPr = 0;

        // 3. Hitung PR (Peer Review) Individu
        foreach ($members as $userId) {
            $avgRating = \Illuminate\Support\Facades\DB::table('activity_group_ratings')
                ->where('id_activity', $activityId)
                ->where('id_group', $groupId)
                ->where('id_evaluated', $userId)
                ->avg('score');

            $pr = $avgRating !== null ? (float) $avgRating : 100;

            $prScores[$userId] = $pr;
            $totalPr += $pr;
        }

        // 4. Hitung Rata-rata PR Kelompok
        $groupPrAvg = $totalPr / count($members);

        // 5. Hitung Indeks SCI, Finalisasi Nilai, dan Beri Badge!
        foreach ($members as $userId) {
            $memberPr = $prScores[$userId];

            // Rumus SCI = PR Individu dibagi PR Kelompok
            $sci = $groupPrAvg > 0 ? ($memberPr / $groupPrAvg) : 1;

            $nilaiAkhir = round($nilaiKelompok * $sci);
            if ($nilaiAkhir > 100) {
                $nilaiAkhir = 100;
            }

            // ============================================
            // 🏅 LOGIKA OTOMATISASI BADGE SCI
            // ============================================
            $badgeId = 5; // Default: Solid Partner SEKARANG ID: 5

            if ($sci >= 1.10) {
                $badgeId = 4; // The Carry SEKARANG ID: 4
            } elseif ($sci < 0.90) {
                $badgeId = 6; // Need Help SEKARANG ID: 6
            }

            // Simpan / Timpa Badge untuk tugas ini
            \Illuminate\Support\Facades\DB::table('user_badge')->updateOrInsert(
                [
                    'id_student'  => $userId,
                    'id_activity' => $activityId
                ],
                [
                    'id_badge'   => $badgeId,
                    'id_class'   => $classId,
                    'updated_at' => now(),
                    // Update created_at hanya saat insert baru
                    'created_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(created_at, NOW())')
                ]
            );

            // ============================================
            // Simpan nilai akhir
            // ============================================
            \Illuminate\Support\Facades\DB::table('activity_result')->updateOrInsert(
                [
                    'id_activity' => $activityId,
                    'id_user' => $userId
                ],
                [
                    'nilai_akhir' => $nilaiAkhir,
                    'result' => $nilaiAkhir,
                    'updated_at' => now()
                ]
            );
        }

        return redirect()->route('guru.monitoring', $activityId)
            ->with('success', 'Berhasil! Nilai kelompok disimpan, SCI dikalkulasi, dan Badge otomatis dibagikan.');
    }
}
