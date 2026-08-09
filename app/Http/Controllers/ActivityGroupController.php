<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityGroupController extends Controller
{
    /**
     * Menampilkan halaman pembentukan kelompok.
     */
    public function index($activityId)
    {
        $activity = Activity::with([
            'groups.members.user'
        ])->findOrFail($activityId);

        // Ambil kelas aktivitas melalui:
        // Activity -> Topic -> Subject -> Class
        $topic = $activity->topic()->with('subject')->first();

        if (!$topic || !$topic->subject) {
            abort(404, 'Kelas aktivitas tidak ditemukan.');
        }

        $classId = $topic->subject->id_class;

        // Pastikan guru yang login mengajar kelas tersebut
        $isTeacherInClass = DB::table('teacher_classes')
            ->where('id_teacher', Auth::id())
            ->where('id_class', $classId)
            ->exists();

        if (!$isTeacherInClass) {
            abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
        }

        // Semua siswa yang terdaftar pada kelas aktivitas
        $students = DB::table('student_classes')
            ->join('users', 'users.id', '=', 'student_classes.id_student')
            ->where('student_classes.id_class', $classId)
            ->select(
                'users.id',
                'users.name'
            )
            ->orderBy('users.name')
            ->get();

        // ID siswa yang sudah masuk kelompok aktivitas ini
        $assignedStudentIds = ActivityGroupMember::whereHas(
            'group',
            function ($query) use ($activityId) {
                $query->where('id_activity', $activityId);
            }
        )->pluck('id_user');

        return view('guru.activity-groups', compact(
            'activity',
            'students',
            'assignedStudentIds',
            'classId'
        ));
    }

    /**
     * Menyimpan kelompok manual.
     */
    public function store(Request $request, $activityId)
    {
        $activity = Activity::findOrFail($activityId);

        $topic = $activity->topic()->with('subject')->first();

        if (!$topic || !$topic->subject) {
            abort(404, 'Kelas aktivitas tidak ditemukan.');
        }

        $classId = $topic->subject->id_class;

        // Pastikan guru memiliki akses ke kelas
        $isTeacherInClass = DB::table('teacher_classes')
            ->where('id_teacher', Auth::id())
            ->where('id_class', $classId)
            ->exists();

        if (!$isTeacherInClass) {
            abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
        }

        $validated = $request->validate([
            'group_name' => [
                'required',
                'string',
                'max:100'
            ],
            'student_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'student_ids.*' => [
                'integer',
                'distinct'
            ]
        ]);

        // Pastikan semua siswa benar-benar berasal dari kelas aktivitas
        $validStudentIds = DB::table('student_classes')
            ->where('id_class', $classId)
            ->whereIn('id_student', $validated['student_ids'])
            ->pluck('id_student')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (
            count($validStudentIds) !==
            count($validated['student_ids'])
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_ids' =>
                        'Terdapat siswa yang bukan anggota kelas aktivitas.'
                ]);
        }

        // Cek siswa yang sudah berada dalam kelompok aktivitas ini
        $alreadyAssigned = ActivityGroupMember::whereIn(
            'id_user',
            $validStudentIds
        )
            ->whereHas('group', function ($query) use ($activityId) {
                $query->where('id_activity', $activityId);
            })
            ->pluck('id_user');

        if ($alreadyAssigned->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_ids' =>
                        'Ada siswa yang sudah berada dalam kelompok aktivitas ini.'
                ]);
        }

        DB::transaction(function () use (
            $activityId,
            $validated,
            $validStudentIds
        ) {
            // Tentukan nomor kelompok berikutnya
            $nextGroupNumber =
                ((int) ActivityGroup::where(
                    'id_activity',
                    $activityId
                )->max('group_number')) + 1;

            $group = ActivityGroup::create([
                'id_activity' => $activityId,
                'group_number' => $nextGroupNumber,
                'name' => $validated['group_name'],
                'formation_method' => 'manual',
            ]);

            foreach ($validStudentIds as $studentId) {
                ActivityGroupMember::create([
                    'id_group' => $group->id,
                    'id_user' => $studentId,
                ]);
            }
        });

        return redirect()
            ->route('guru.activity.groups', $activityId)
            ->with('success', 'Kelompok berhasil dibuat.');
    }
    /**
 * Membentuk kelompok secara acak.
 */
public function random(Request $request, $activityId)
{
    $activity = Activity::findOrFail($activityId);

    // Ambil kelas aktivitas:
    // Activity -> Topic -> Subject -> Class
    $topic = $activity->topic()->with('subject')->first();

    if (!$topic || !$topic->subject) {
        abort(404, 'Kelas aktivitas tidak ditemukan.');
    }

    $classId = $topic->subject->id_class;

    // Pastikan guru yang login mengajar kelas tersebut
    $isTeacherInClass = DB::table('teacher_classes')
        ->where('id_teacher', Auth::id())
        ->where('id_class', $classId)
        ->exists();

    if (!$isTeacherInClass) {
        abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
    }

    // Validasi jumlah anggota per kelompok
    $validated = $request->validate([
        'members_per_group' => [
            'required',
            'integer',
            'min:1',
            'max:100'
        ]
    ]);

    $membersPerGroup = (int) $validated['members_per_group'];

    // Ambil seluruh siswa yang terdaftar di kelas aktivitas
    $students = DB::table('student_classes')
        ->where('id_class', $classId)
        ->pluck('id_student')
        ->map(fn ($id) => (int) $id)
        ->toArray();

    // Ambil siswa yang sudah memiliki kelompok
    $assignedStudentIds = ActivityGroupMember::whereHas(
        'group',
        function ($query) use ($activityId) {
            $query->where('id_activity', $activityId);
        }
    )
        ->pluck('id_user')
        ->map(fn ($id) => (int) $id)
        ->toArray();

    // Hanya siswa yang belum mempunyai kelompok
    $availableStudentIds = array_values(
        array_diff($students, $assignedStudentIds)
    );

    if (empty($availableStudentIds)) {
        return back()->withErrors([
            'members_per_group' =>
                'Semua siswa di kelas ini sudah memiliki kelompok.'
        ]);
    }

    // Acak siswa
    shuffle($availableStudentIds);

    DB::transaction(function () use (
        $activityId,
        $availableStudentIds,
        $membersPerGroup
    ) {
        $nextGroupNumber =
            ((int) ActivityGroup::where(
                'id_activity',
                $activityId
            )->max('group_number')) + 1;

        $chunks = array_chunk(
            $availableStudentIds,
            $membersPerGroup
        );

        foreach ($chunks as $index => $studentIds) {

            $groupNumber = $nextGroupNumber + $index;

            $group = ActivityGroup::create([
                'id_activity' => $activityId,
                'group_number' => $groupNumber,
                'name' => 'Kelompok ' . $groupNumber,
                'formation_method' => 'random',
            ]);

            foreach ($studentIds as $studentId) {
                ActivityGroupMember::create([
                    'id_group' => $group->id,
                    'id_user' => $studentId,
                ]);
            }
        }
    });

    return redirect()
        ->route('guru.activity.groups', $activityId)
        ->with('success', 'Kelompok random berhasil dibuat.');
}
/**
 * Menghapus kelompok dari suatu aktivitas.
 */
public function destroy($activityId, $groupId)
{
    $activity = Activity::findOrFail($activityId);

    // Pastikan aktivitas mempunyai kelas yang valid
    $topic = $activity->topic()->with('subject')->first();

    if (!$topic || !$topic->subject) {
        abort(404, 'Kelas aktivitas tidak ditemukan.');
    }

    $classId = $topic->subject->id_class;

    // Pastikan guru mengajar kelas tersebut
    $isTeacherInClass = DB::table('teacher_classes')
        ->where('id_teacher', Auth::id())
        ->where('id_class', $classId)
        ->exists();

    if (!$isTeacherInClass) {
        abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
    }

    // Pastikan kelompok memang milik aktivitas tersebut
    $group = ActivityGroup::where('id', $groupId)
        ->where('id_activity', $activityId)
        ->firstOrFail();

    DB::transaction(function () use ($group) {
        $group->delete();
    });

    return redirect()
        ->route('guru.activity.groups', $activityId)
        ->with('success', 'Kelompok berhasil dihapus.');
}
/**
 * Halaman edit anggota kelompok.
 */
public function edit($activityId, $groupId)
{
    $activity = Activity::findOrFail($activityId);

    // Ambil kelas dari aktivitas
    $topic = $activity->topic()->with('subject')->first();

    if (!$topic || !$topic->subject) {
        abort(404, 'Kelas aktivitas tidak ditemukan.');
    }

    $classId = $topic->subject->id_class;

    // Pastikan guru mengajar kelas tersebut
    $isTeacherInClass = DB::table('teacher_classes')
        ->where('id_teacher', Auth::id())
        ->where('id_class', $classId)
        ->exists();

    if (!$isTeacherInClass) {
        abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
    }

    // Pastikan kelompok memang milik aktivitas tersebut
    $group = ActivityGroup::with('members.user')
        ->where('id', $groupId)
        ->where('id_activity', $activityId)
        ->firstOrFail();

    // Ambil seluruh siswa dalam kelas
    $students = DB::table('student_classes')
        ->join('users', 'users.id', '=', 'student_classes.id_student')
        ->where('student_classes.id_class', $classId)
        ->select(
            'users.id',
            'users.name'
        )
        ->orderBy('users.name')
        ->get();

    // Ambil ID anggota kelompok yang sedang diedit
    $currentMemberIds = $group->members
        ->pluck('id_user')
        ->map(fn ($id) => (int) $id)
        ->toArray();

    // Ambil siswa yang sudah berada di kelompok LAIN
    $assignedToOtherGroups = ActivityGroupMember::whereHas(
        'group',
        function ($query) use ($activityId, $groupId) {
            $query->where('id_activity', $activityId)
                  ->where('id', '!=', $groupId);
        }
    )
        ->pluck('id_user')
        ->map(fn ($id) => (int) $id)
        ->toArray();

    return view('guru.activity-group-edit', compact(
        'activity',
        'group',
        'students',
        'currentMemberIds',
        'assignedToOtherGroups'
    ));
}
/**
 * Menyimpan perubahan anggota kelompok.
 */
public function updateMembers(Request $request, $activityId, $groupId)
{
    $activity = Activity::findOrFail($activityId);

    // Ambil kelas dari aktivitas
    $topic = $activity->topic()->with('subject')->first();

    if (!$topic || !$topic->subject) {
        abort(404, 'Kelas aktivitas tidak ditemukan.');
    }

    $classId = $topic->subject->id_class;

    // Pastikan guru mengajar kelas tersebut
    $isTeacherInClass = DB::table('teacher_classes')
        ->where('id_teacher', Auth::id())
        ->where('id_class', $classId)
        ->exists();

    if (!$isTeacherInClass) {
        abort(403, 'Anda tidak memiliki akses ke aktivitas ini.');
    }

    // Pastikan kelompok memang milik aktivitas
    $group = ActivityGroup::where('id', $groupId)
        ->where('id_activity', $activityId)
        ->firstOrFail();

    // Minimal harus ada satu anggota
    $validated = $request->validate([
        'student_ids' => 'required|array|min:1',
        'student_ids.*' => 'integer|exists:users,id',
    ]);

    $studentIds = collect($validated['student_ids'])
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    /*
     * Pastikan semua siswa memang berasal dari kelas
     * aktivitas ini.
     */
    $classStudentIds = DB::table('student_classes')
        ->where('id_class', $classId)
        ->pluck('id_student')
        ->map(fn ($id) => (int) $id);

    $invalidStudents = $studentIds
        ->diff($classStudentIds);

    if ($invalidStudents->isNotEmpty()) {
        return back()
            ->withErrors([
                'student_ids' =>
                    'Terdapat siswa yang bukan anggota kelas aktivitas ini.'
            ])
            ->withInput();
    }

    /*
     * Cek apakah ada siswa yang sudah berada
     * di kelompok lain pada aktivitas yang sama.
     */
    $assignedToOtherGroup = ActivityGroupMember::whereIn(
        'id_user',
        $studentIds
    )
        ->whereHas(
            'group',
            function ($query) use ($activityId, $groupId) {
                $query->where('id_activity', $activityId)
                      ->where('id', '!=', $groupId);
            }
        )
        ->exists();

    if ($assignedToOtherGroup) {
        return back()
            ->withErrors([
                'student_ids' =>
                    'Salah satu siswa sudah tergabung dalam kelompok lain.'
            ])
            ->withInput();
    }

    DB::transaction(function () use ($group, $studentIds) {

        // Hapus anggota lama
        ActivityGroupMember::where(
            'id_group',
            $group->id
        )->delete();

        // Simpan anggota baru
        foreach ($studentIds as $studentId) {

            ActivityGroupMember::create([
                'id_group' => $group->id,
                'id_user' => $studentId,
            ]);

        }
    });

    return redirect()
        ->route(
            'guru.activity.groups',
            $activityId
        )
        ->with(
            'success',
            'Anggota kelompok berhasil diperbarui.'
        );
}
}