<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $request->validate([
            'evaluated_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'score' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        // Jangan izinkan menilai diri sendiri
        if ((int) $request->evaluated_id === (int) $user->id) {
            abort(403, 'Anda tidak dapat menilai diri sendiri.');
        }

        // Pastikan orang yang dinilai memang anggota kelompok yang sama
        $isMember = $group->members()
            ->where('id_user', $request->evaluated_id)
            ->exists();

        if (!$isMember) {
            abort(403, 'Siswa yang dinilai bukan anggota kelompok Anda.');
        }

        // Simpan atau update penilaian
        ActivityGroupRating::updateOrCreate(
            [
                'id_activity' => $activity->id,
                'id_group' => $group->id,
                'id_evaluator' => $user->id,
                'id_evaluated' => $request->evaluated_id,
            ],
            [
                'score' => $request->score,
                'comment' => $request->comment,
            ]
        );

        return back()->with(
            'success',
            'Penilaian berhasil disimpan.'
        );
    }
}