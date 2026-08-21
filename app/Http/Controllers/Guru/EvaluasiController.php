<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    /**
     * Menampilkan halaman Monitoring Guru (Daftar Kelompok & Progres)
     */
    public function detailJawaban($activityId)
    {
        // 1. Ambil data aktivitas utama
        $activity = Activity::findOrFail($activityId);

        // Pastikan ini aktivitas kelompok
        if ($activity->is_group_activity !== 'yes') {
            return back()->with('swal', [
                'icon' => 'warning',
                'title' => 'Bukan Tugas Kelompok',
                'text' => 'Fitur monitoring ini hanya untuk aktivitas yang dikerjakan secara berkelompok.'
            ]);
        }

        // =========================================================
        // 2. IDENTIFIKASI MODE (SINKRON DENGAN DATABASE)
        // =========================================================
        $isMode2 = $activity->evaluation_mode === 'mode2';
        $isMode1 = $activity->evaluation_mode === 'mode1' && $activity->is_group_activity === 'yes';

        // 3. Tarik data Kelompok beserta relasinya
        $groups = ActivityGroup::with(['members.user', 'answers'])
            ->where('id_activity', $activityId)
            ->orderBy('group_number', 'asc')
            ->get();

        // Ambil data submit untuk Mode 2 (Dari tabel package buatan partner)
        $mode2SubmittedUsers = [];
        if ($isMode2) {
            $mode2SubmittedUsers = DB::table('activity_student_packages')
                ->where('id_activity', $activityId)
                ->whereIn('status', ['submitted', 'late'])
                ->pluck('id_user')
                ->toArray();
        }

        // ==============================================
        // TAMBAHAN: AMBIL DATA NILAI MURNI INDIVIDU
        // ==============================================
        $individualResults = DB::table('activity_result')
            ->where('id_activity', $activityId)
            ->get()
            ->keyBy('id_user');

        // 4. Olah data untuk menghitung persentase progres & status anggota
        foreach ($groups as $group) {
            $totalMembers = $group->members->count();

            // Cek user yang sudah submit berdasarkan Mode
            if ($isMode2) {
                $memberIds = $group->members->pluck('id_user')->toArray();
                $submittedUserIds = array_intersect($memberIds, $mode2SubmittedUsers);
            } else {
                $submittedUserIds = $group->answers->pluck('id_user')->unique()->toArray();
            }

            $submittedCount = count($submittedUserIds);

            // Kalkulasi Persentase (0 - 100%)
            $progressPercentage = $totalMembers > 0 ? round(($submittedCount / $totalMembers) * 100) : 0;

            $group->total_members = $totalMembers;
            $group->submitted_count = $submittedCount;
            $group->progress_percentage = $progressPercentage;

            // Tandai status submit masing-masing anggota
            foreach ($group->members as $member) {
                $member->has_submitted = in_array($member->id_user, $submittedUserIds);
            }

            // =========================================================
            // 5. CEK STATUS PENILAIAN & NILAI KELOMPOK
            // =========================================================
            $group->is_graded = false;
            $group->nilai_kelompok = 0;

            if ($totalMembers > 0) {
                if ($isMode2) {
                    // Mode 2: Nilai kelompok = Rata-rata dari nilai individu di DB
                    $avgScore = DB::table('activity_result')
                        ->where('id_activity', $activityId)
                        ->whereIn('id_user', $group->members->pluck('id_user'))
                        ->avg('nilai_akhir');

                    $group->nilai_kelompok = $avgScore ? round($avgScore, 2) : 0;
                    $group->is_graded = ($progressPercentage == 100); // Mode 2 dianggap selesai/dinilai jika 100% submit
                } else {
                    // Mode 1: Ambil langsung dari tabel activity_groups (yang sudah kita simpan)
                    $groupData = DB::table('activity_groups')->where('id', $group->id)->first();
                    $group->nilai_kelompok = $groupData ? (float) ($groupData->nilai_kelompok ?? 0) : 0;

                    // Jika nilai > 0, berarti guru sudah menilai
                    if ($group->nilai_kelompok > 0) {
                        $group->is_graded = true;
                    }
                }
            }
        }

        // 6. Lempar data beserta penanda Mode ke Blade View
        return view('guru.evaluasi.detail-jawaban', compact('activity', 'groups', 'isMode1', 'isMode2'));
    }

    /**
     * Menampilkan Halaman Lembar Jawaban & Form Penilaian Guru (HANYA MODE 1)
     */
    public function formPenilaian($activityId, $groupId)
    {
        $activity = Activity::findOrFail($activityId);
        $group = ActivityGroup::with(['members.user'])->findOrFail($groupId);

        // Tarik jawaban kelompok dari database (Mode 1 Uraian)
        $answers = DB::table('activity_group_answers')
            ->join('question', 'activity_group_answers.id_question', '=', 'question.id')
            ->join('users', 'activity_group_answers.id_user', '=', 'users.id')
            ->where('activity_group_answers.id_activity', $activityId)
            ->where('activity_group_answers.id_group', $groupId)
            ->select('activity_group_answers.*', 'question.question as soal', 'question.type', 'users.name as penjawab')
            ->get();

        // Tarik hasil rating antar teman (Peer Review)
        $ratings = DB::table('activity_group_ratings')
            ->join('users as evaluator', 'activity_group_ratings.id_evaluator', '=', 'evaluator.id')
            ->join('users as evaluated', 'activity_group_ratings.id_evaluated', '=', 'evaluated.id')
            ->where('activity_group_ratings.id_activity', $activityId)
            ->where('activity_group_ratings.id_group', $groupId)
            ->select('activity_group_ratings.*', 'evaluator.name as evaluator_name', 'evaluated.name as evaluated_name')
            ->get();

        // Beritahu view bahwa ini sedang mengoreksi Mode 1
        $isMode1 = true;
        $isMode2 = false;

        return view('guru.evaluasi.penilaian-kelompok', compact('activity', 'group', 'answers', 'ratings', 'isMode1', 'isMode2'));
    }

    /**
     * Menyimpan nilai guru dan mengkalkulasi SCI (HANYA MODE 1)
     */
    /**
     * Menyimpan nilai guru dan memicu kalkulasi
     */
    public function simpanPenilaian(Request $request, $activityId, $groupId)
    {
        $activity = Activity::findOrFail($activityId);

        // Mode 1: Guru wajib memasukkan angka manual untuk kelompok
        if ($activity->evaluation_mode === 'mode1') {
            $request->validate(['nilai_kelompok' => 'required|numeric|min:0|max:100']);
            DB::table('activity_groups')->where('id', $groupId)->update(['nilai_kelompok' => $request->nilai_kelompok]);
        }

        // Eksekusi Mesin SCI
        $this->kalkulasiSCIKelompok($activityId, $groupId);

        return redirect('/guru/aktivitas/' . $activityId . '/monitoring')
            ->with('success', 'Berhasil! Nilai kelompok disahkan, SCI dikalkulasi, dan Badge otomatis dibagikan.');
    }

    /**
     * MESIN SENTRAL: Menghitung SCI dan Nilai Akhir
     */
    /**
     * MESIN SENTRAL: Menghitung SCI dan Nilai Akhir
     */
    public function kalkulasiSCIKelompok($activityId, $groupId)
    {
        $activity = Activity::with('topic.subject')->findOrFail($activityId);
        $classId = optional(optional($activity->topic)->subject)->id_class;
        $isMode2 = $activity->evaluation_mode === 'mode2';

        $members = DB::table('activity_group_members')->where('id_group', $groupId)->pluck('id_user')->toArray();
        if (empty($members)) return;

        // Cari Nilai Kelompok (HANYA UNTUK MODE 1)
        $nilaiKelompok = 0;
        if (!$isMode2) {
            $groupData = DB::table('activity_groups')->where('id', $groupId)->first();
            $nilaiKelompok = $groupData ? (float)($groupData->nilai_kelompok ?? 0) : 0;
        }

        // Tarik Data Peer Review (SCI)
        $prScores = [];
        $totalPr = 0;
        foreach ($members as $userId) {
            $avgRating = DB::table('activity_group_ratings')
                ->where('id_activity', $activityId)->where('id_group', $groupId)->where('id_evaluated', $userId)->avg('score');
            $pr = $avgRating !== null ? (float) $avgRating : 100;
            $prScores[$userId] = $pr;
            $totalPr += $pr;
        }

        $groupPrAvg = count($members) > 0 ? ($totalPr / count($members)) : 100;

        // Eksekusi Rumus SCI & Bagikan Badge
        foreach ($members as $userId) {

            // =========================================================
            // 🛡️ PROTEKSI ANTI-NYASAR: JANGAN NILAI SISWA YANG BELUM SELESAI
            // =========================================================
            if ($isMode2) {
                // Di Mode 2: Cek apakah siswa ini sudah Kumpul Kuis
                $isSubmitted = DB::table('activity_student_packages')
                    ->where('id_activity', $activityId)
                    ->where('id_user', $userId)
                    ->where('status', 'submitted')
                    ->exists();

                // Jika belum submit kuis, LEWATI! Jangan buatkan nilai akhir.
                if (!$isSubmitted) {
                    continue; 
                }
            } else {
                // Di Mode 1: Pastikan guru sudah memberikan nilai kelompok
                if ($nilaiKelompok <= 0) {
                    continue; 
                }
            }
            // =========================================================

            $memberPr = $prScores[$userId];
            $sci = $groupPrAvg > 0 ? ($memberPr / $groupPrAvg) : 1;

            // 🌟 LOGIKA NILAI AKHIR (DIBEDAKAN BERDASARKAN MODE)
            if ($isMode2) {
                // MODE 2 (KUIS INDIVIDU): Nilai Kuis Murni x SCI
                $rawIndividu = DB::table('activity_result')
                    ->where('id_activity', $activityId)
                    ->where('id_user', $userId)
                    ->value('result') ?? 0;
                $nilaiAkhir = round($rawIndividu * $sci, 2);
            } else {
                // MODE 1 (PROYEK KELOMPOK): Nilai Proyek Kelompok x SCI
                $nilaiAkhir = round($nilaiKelompok * $sci, 2);
            }

            if ($nilaiAkhir > 100) $nilaiAkhir = 100;

            // Logika Badge
            $badgeId = 5; // Solid Partner
            if ($sci >= 1.10) {
                $badgeId = 4; // The Carry
            } elseif ($sci < 0.90) {
                $badgeId = 6; // Need Help
            }

            DB::table('user_badge')->updateOrInsert(
                ['id_student' => $userId, 'id_activity' => $activityId],
                ['id_badge' => $badgeId, 'id_class' => $classId, 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
            );

            $status = $nilaiAkhir >= ($activity->kkm ?? 70) ? 'Pass' : 'Remedial';
            
            DB::table('activity_result')->updateOrInsert(
                ['id_activity' => $activityId, 'id_user' => $userId],
                ['nilai_akhir' => $nilaiAkhir, 'result_status' => $status, 'updated_at' => now()]
            );
        }
    }
}
