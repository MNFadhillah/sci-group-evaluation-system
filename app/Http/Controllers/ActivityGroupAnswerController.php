<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Ambil soal aktivitas
        $questions = $activity->questions()
            ->orderBy('activity_question.id_question')
            ->get();

        if ($questions->isEmpty()) {
            abort(404, 'Soal untuk aktivitas ini belum tersedia.');
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
        $questionExists = $activity->questions()
            ->where('question.id', $request->question_id)
            ->exists();

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