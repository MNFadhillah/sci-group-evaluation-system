<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // =======================================================
        // TAMBAHAN: VALIDASI TOTAL POIN HARUS TEPAT 100
        // =======================================================
        $totalScore = 0;
        foreach ($request->ratings as $data) {
            $totalScore += (int) $data['score'];
        }

        if ($totalScore !== 100) {
            return back()
                ->withErrors(['score_total' => "Total poin kontribusi harus tepat 100 Poin! (Total Anda saat ini: $totalScore Poin)"])
                ->withInput();
        }

        // 2. Looping array dan simpan semua penilaian ke database

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

        // =======================================================
        // PERBAIKAN: HITUNG SCI & SIMPAN BADGE SECARA PERMANEN
        // =======================================================
        // 1. Ambil semua anggota di kelompok ini
        $groupMembers = $group->members()->pluck('id_user')->toArray();
        $userPrScores = [];
        $totalPr = 0;

        // 2. Hitung rata-rata rating (PR) yang didapat tiap anggota
        foreach ($groupMembers as $memberId) {
            $avgRating = ActivityGroupRating::where('id_activity', $activity->id)
                ->where('id_group', $group->id)
                ->where('id_evaluated', $memberId)
                ->avg('score');

            $pr = $avgRating !== null ? (float) $avgRating : 100;
            $userPrScores[$memberId] = $pr;
            $totalPr += $pr;
        }

        // 3. Rata-rata PR Kelompok
        $groupAvg = count($groupMembers) > 0 ? ($totalPr / count($groupMembers)) : 100;

        // 4. Hitung SCI tiap anggota & Berikan Badge
        foreach ($groupMembers as $memberId) {
            $myPr = $userPrScores[$memberId];
            $sci = $groupAvg > 0 ? round($myPr / $groupAvg, 2) : 1.00;

            // Aturan Badge Mode 2
            $badgeId = 2; // Default (Solid Partner)
            if ($sci >= 1.10) {
                $badgeId = 1; // The Carry
            } elseif ($sci < 0.90) {
                $badgeId = 3; // Need Help
            }

            // Simpan Badge ke database (Pastikan siswa dapat badge)
            DB::table('user_badge')->updateOrCreate(
                [
                    'id_student' => $memberId,
                    'id_activity' => $activity->id,
                ],
                [
                    'id_badge' => $badgeId,
                    'id_class' => $group->id_class ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return back()->with(
            'success',
            'Sip! Penilaian kinerja kelompok berhasil disimpan.'
        );
    }
}
