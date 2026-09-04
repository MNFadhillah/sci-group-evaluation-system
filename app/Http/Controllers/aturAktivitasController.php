<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class aturAktivitasController extends Controller
{
    public function halamanAturSoal($idAktivitas, Request $request)
    {
        // ambil aktivitas (Eloquent)
        $aktivitas = Activity::findOrFail($idAktivitas);

        // ambil id_topic dari query (utk safety kita fallback ke aktivitas->id_topic)
        $idTopic = $request->query('topic') ?? $aktivitas->id_topic;

        // ambil topic & subject (dengan relasi)
        $topic = Topic::with('subject')->find($idTopic);
        if (!$topic) {
            abort(404, 'Topik tidak ditemukan.');
        }

        $subject = $topic->subject;
        if (!$subject) {
            abort(404, 'Subject untuk topik ini tidak ditemukan.');
        }

        // pastikan guru tergabung di kelas subject ini
        $classId = $subject->id_class;
        $idGuru = Auth::id();
        $isTeacherInClass = DB::table('teacher_classes')
            ->where('id_teacher', $idGuru)
            ->where('id_class', $classId)
            ->exists();

        if (!$isTeacherInClass) {
            // Jika bukan anggota kelas -> larang akses
            abort(403, 'Anda tidak memiliki akses ke kelas/topik ini.');
        }

        // Ambil semua soal yang punya id_topic = $idTopic (tidak dibatasi created_by)
        // Karena kamu mau soal walau dibuat guru lain tetap tersedia selama topiknya sama
        $questionsQuery = Question::where('id_topic', $idTopic);

        if ($aktivitas->evaluation_mode === 'mode1' && $aktivitas->is_group_activity === 'yes') {
    $questionsQuery->where('type', 'Essay');
} else {
    $questionsQuery->whereIn('type', ['MultipleChoice', 'ShortAnswer']);
}

        $questions = $questionsQuery->orderBy('created_at', 'desc')->get();

        // Ambil selected ids dari pivot (activity_question)
        $selectedIds = DB::table('activity_question')
            ->where('id_activity', $idAktivitas)
            ->pluck('id_question')
            ->toArray();

        $selectedQuestions = Question::whereIn('id', $selectedIds)->get();
        if (
    $aktivitas->evaluation_mode === 'mode2'
    && empty($selectedIds)
    && !empty($aktivitas->jumlah_soal)
) {
    $n = (int) $aktivitas->jumlah_soal;

    $types = $aktivitas->mode2_question_types ?? ['MultipleChoice', 'ShortAnswer'];
    if (!is_array($types) || empty($types)) {
        $types = ['MultipleChoice', 'ShortAnswer'];
    }

    $baseAuto = Question::where('id_topic', $idTopic)->whereIn('type', $types);

    if ($aktivitas->addaptive === 'yes') {
        // ikuti rumus adaptif yang sama seperti ambilSoalAjax()
        $easyCount   = max(0, $n - 2);
        $hardCount   = max(0, $n - 2);
        $mediumCount = $n;

        $autoSelected = (clone $baseAuto)->where('difficulty', 'mudah')->inRandomOrder()->take($easyCount)->get()
            ->merge((clone $baseAuto)->where('difficulty', 'sedang')->inRandomOrder()->take($mediumCount)->get())
            ->merge((clone $baseAuto)->where('difficulty', 'sulit')->inRandomOrder()->take($hardCount)->get());
    } else {
        $autoSelected = (clone $baseAuto)->inRandomOrder()->take($n)->get();
    }

    if ($autoSelected->isNotEmpty()) {
        $now = now();
        $rows = $autoSelected->map(fn($q) => [
            'id_activity' => $idAktivitas,
            'id_question' => $q->id,
            'created_at'  => $now,
            'updated_at'  => $now,
        ])->toArray();

        DB::table('activity_question')->insert($rows);

        $selectedIds       = $autoSelected->pluck('id')->toArray();
        $selectedQuestions = $autoSelected;
    }
}


        return view('guru.atursoal', compact(
    'aktivitas',
    'questions',
    'selectedIds',
    'selectedQuestions',
    'topic',
    'subject'
));
    }

    
    public function ambilSoalAjax(Request $request, $idAktivitas)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:1'
        ]);

        $aktivitas = Activity::findOrFail($idAktivitas);
        $idTopic = $aktivitas->id_topic;

        // pastikan guru tergabung di kelas topik ini (security)
        $topic = Topic::with('subject')->find($idTopic);
        if (!$topic || !$topic->subject) {
            return response()->json(['success' => false, 'message' => 'Topik/subject tidak ditemukan.'], 404);
        }

        $classId = $topic->subject->id_class;
        $idGuru = Auth::id();
        $isTeacherInClass = DB::table('teacher_classes')
            ->where('id_teacher', $idGuru)
            ->where('id_class', $classId)
            ->exists();

        if (!$isTeacherInClass) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke kelas/topik ini.'], 403);
        }

        $isAdaptive = ($aktivitas->addaptive === 'yes');
        $n = intval($request->jumlah);

        // Base query: semua soal yang punya topik sama (tanpa membatasi created_by)
        $baseQuery = Question::where('id_topic', $idTopic);

        if ($aktivitas->evaluation_mode === 'mode1' && $aktivitas->is_group_activity === 'yes') {
    $baseQuery->where('type', 'Essay');
} else {
    $baseQuery->whereIn('type', ['MultipleChoice', 'ShortAnswer']);
}

        if ($isAdaptive) {
            $easyCount = max(0, $n - 2);
            $hardCount = max(0, $n - 2);
            $mediumCount = max(0, $n);

            // Ambil bagian dari masing-masing difficulty secara acak
            $easyPool = (clone $baseQuery)->where('difficulty', 'mudah')
                ->inRandomOrder()->take($easyCount)->get();

            $mediumPool = (clone $baseQuery)->where('difficulty', 'sedang')
                ->inRandomOrder()->take($mediumCount)->get();

            $hardPool = (clone $baseQuery)->where('difficulty', 'sulit')
                ->inRandomOrder()->take($hardCount)->get();

            $final = $easyPool->merge($mediumPool)->merge($hardPool);

            return response()->json([
                'success' => true,
                'adaptive' => true,
                'easy' => $easyCount,
                'medium' => $mediumCount,
                'hard' => $hardCount,
                'total' => $final->count(),
                'data' => $final->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'difficulty' => $q->difficulty,
                        'type' => $q->type,
                        'text' => optional(json_decode($q->question))->text ?? '-'
                    ];
                })->values()
            ]);
        }

        // Non-adaptive: ambil n soal acak dari semua soal topik ini
        $final = $baseQuery->inRandomOrder()->take($n)->get();

        return response()->json([
            'success' => true,
            'adaptive' => false,
            'total' => $final->count(),
            'data' => $final->map(function ($q) {
                return [
                    'id' => $q->id,
                    'difficulty' => $q->difficulty,
                    'type' => $q->type,
                    'text' => optional(json_decode($q->question))->text ?? '-'
                ];
            })->values()
        ]);
    }
    public function simpanAturSoal(Request $request, $idAktivitas)
{
    $request->validate([
        'id_question' => 'nullable|array',
        'id_question.*' => 'integer',
        'jumlah' => 'nullable|integer|min:0'
    ]);

    $ids = $request->input('id_question', []);
    $jumlah = $request->input('jumlah', null);

    // ===== VALIDASI SESUAI MODE =====
    $aktivitas = Activity::findOrFail($idAktivitas);
    $questions = Question::whereIn('id', array_map('intval', $ids))->get();

    if ($aktivitas->evaluation_mode === 'mode1' && $aktivitas->is_group_activity === 'yes') {
    if (count($ids) !== 1) {
        return response()->json([
            'success' => false,
            'message' => 'Mode 1 (Kelompok) hanya boleh memiliki tepat 1 soal.'
        ], 422);
    }
    if ($questions->isEmpty() || $questions->first()->type !== 'Essay') {
        return response()->json([
            'success' => false,
            'message' => 'Mode 1 (Kelompok) hanya menerima soal bertipe Essay.'
        ], 422);
    }
} else {
    $invalidTypes = $questions->pluck('type')->diff(['MultipleChoice', 'ShortAnswer']);
    if ($invalidTypes->isNotEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Hanya menerima soal Pilihan Ganda atau Isian Singkat (Essay tidak diperbolehkan).'
        ], 422);
    }
}

        DB::beginTransaction();
        try {
            // Hapus dulu yang lama untuk aktivitas ini
            DB::table('activity_question')->where('id_activity', $idAktivitas)->delete();

            // Simpan yang baru (jika ada)
            if (!empty($ids)) {
                $insert = [];
                $now = now();
                foreach ($ids as $qid) {
                    // skip non-int guard
                    $qid = intval($qid);
                    if ($qid <= 0)
                        continue;
                    $insert[] = [
                        'id_activity' => $idAktivitas,
                        'id_question' => $qid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($insert)) {
                    DB::table('activity_question')->insert($insert);
                }
            }

            // Jika dikirim jumlah, update kolom jumlah_soal pada activities
            if (!is_null($jumlah)) {
                // pastikan kolom ada — jika tidak ada, query akan gagal -> exception
                DB::table('activities')->where('id', $idAktivitas)->update([
                    'jumlah_soal' => intval($jumlah),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tersimpan', 'jumlah' => $jumlah]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(message: 'simpanAturSoal error: ' . $e->getMessage() . ' -- trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function tambahSoalManual(Request $req, $idAktivitas)
{
    $aktivitas = Activity::findOrFail($idAktivitas);
    $question = Question::find($req->id_question);

    if (!$question) {
        return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan.'], 404);
    }

    if ($aktivitas->evaluation_mode === 'mode1' && $aktivitas->is_group_activity === 'yes') {
    $existingCount = DB::table('activity_question')->where('id_activity', $idAktivitas)->count();

    if ($existingCount >= 1) {
        return response()->json(['success' => false, 'message' => 'Mode 1 (Kelompok) hanya boleh memiliki 1 soal.'], 422);
    }
    if ($question->type !== 'Essay') {
        return response()->json(['success' => false, 'message' => 'Mode 1 (Kelompok) hanya menerima soal Essay.'], 422);
    }
} else {
    if (!in_array($question->type, ['MultipleChoice', 'ShortAnswer'])) {
        return response()->json(['success' => false, 'message' => 'Soal Essay tidak diperbolehkan.'], 422);
    }
}

    DB::table('activity_question')->insert([
        'id_activity' => $idAktivitas,
        'id_question' => $req->id_question,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => true]);
}

    public function hapusSoalManual(Request $req, $idAktivitas)
    {
        DB::table('activity_question')
            ->where('id_activity', $idAktivitas)
            ->where('id_question', $req->id_question)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getQuestion($id)
    {
        $q = Question::find($id);

        $qData = json_decode($q->question);

        return response()->json([
            'id' => $q->id,
            'difficulty' => $q->difficulty,
            'type' => $q->type,
            'text' => $qData->text ?? '-',
        ]);
    }
    public function clearAll($id)
    {
        DB::table('activity_question')
            ->where('id_activity', $id)
            ->delete();

        return response()->json(['success' => true]);
    }




}
