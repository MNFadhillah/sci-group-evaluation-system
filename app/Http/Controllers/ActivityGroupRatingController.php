<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityResult;
use App\Models\ActivityStudentPackage;
use App\Models\ActivityStudentQuestion;
use App\Models\ActivityGroupAnswer;
use App\Models\ActivityAnswer;

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

        // 1. Validasi input array 'ratings' (0-100 poin per input)
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

        // 2. VALIDASI TOTAL POIN HARUS TEPAT 100
        $totalScore = 0;
        foreach ($request->ratings as $data) {
            $totalScore += (int) $data['score'];
        }

        if ($totalScore !== 100) {
            return back()
                ->withErrors(['score_total' => "Total poin kontribusi harus tepat 100 Poin! (Total Anda saat ini: $totalScore Poin)"])
                ->withInput();
        }

        // 3. Simpan semua penilaian ke database
        foreach ($request->ratings as $evaluatedId => $data) {
            // Pastikan orang yang dinilai memang anggota kelompok yang sama
            $isMember = $group->members()->where('id_user', $evaluatedId)->exists();

            if ($isMember) {
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

        // =======================================================
        // TAHAP 2: TRIGGER MESIN SENTRAL SCI (AUTO-CALCULATE)
        // =======================================================
        // Kita panggil fungsi kalkulasiSCIKelompok yang ada di EvaluasiController
        // Mesin ini akan otomatis mengalikan Nilai Murni dengan SCI dan menyimpan Nilai Akhir!
        app(\App\Http\Controllers\Guru\EvaluasiController::class)->kalkulasiSCIKelompok($activity->id, $group->id);

        // UBAH RETURN REDIRECTNYA JADI LANGSUNG KE DASHBOARD
        return redirect()->route('siswa.aktivitas')->with(
            'success',
            'Sip! Penilaian kinerja kelompok berhasil disimpan. Aktivitas telah selesai sepenuhnya!'
        );
    }
    /**
     * Menyelesaikan aktivitas Mode 2 kelompok.
     */
    public function finish($id)
    {
        $user = Auth::user();
        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas adalah Mode 2
        if ($activity->evaluation_mode !== 'mode2') {
            abort(403, 'Aktivitas ini bukan Mode 2.');
        }

        // Cari package Mode 2 milik siswa
        $package = ActivityStudentPackage::where('id_activity', $activity->id)
            ->where('id_user', $user->id)
            ->latest('id')
            ->first();

        if (!$package) {
            return redirect()->route('siswa.aktivitas')->with('error', 'Package soal belum dibuat.');
        }

        // Ambil Nilai Akhir yang sudah dihitungkan oleh Mesin SCI (di Tahap 2)
        $resultData = ActivityResult::where('id_activity', $activity->id)
            ->where('id_user', $user->id)
            ->first();

        $nilaiAkhir = $resultData ? $resultData->nilai_akhir : 0;

        // Tutup sesi pengerjaan (jika belum tertutup)
        if ($package->status !== 'submitted') {
            $package->update([
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

        // Arahkan pulang ke Dashboard Siswa
        return redirect()->route('siswa.aktivitas')->with(
            'success',
            "Aktivitas berhasil diselesaikan secara menyeluruh. Nilai Akhir Anda: {$nilaiAkhir}"
        );
    }
}
