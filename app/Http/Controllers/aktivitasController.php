<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\nilai;
use App\Models\Question;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityAnswer;
use App\Models\ActivityGroup;


class aktivitasController extends Controller
{
    public function aktivitasSiswa()
    {
        $user = Auth::user();

        // 🔹 Ambil data badge
        $badge = DB::table('user_badge')
            ->join('badge', 'user_badge.id_badge', '=', 'badge.id')
            ->where('user_badge.id_student', $user->id)
            ->select('badge.name', 'badge.description')
            ->first();

        // 🔹 Ambil daftar kelas siswa
        $kelasList = DB::table('student_classes')
            ->join('classes', 'student_classes.id_class', '=', 'classes.id')
            ->where('student_classes.id_student', $user->id)
            ->select('classes.id', 'classes.name', 'classes.level', 'classes.token')
            ->get();

        // 🔹 Ambil aktivitas + nilai
        $rawActivities = DB::table('activities')
            ->join('topics', 'activities.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->join('student_classes', 'classes.id', '=', 'student_classes.id_class')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->leftJoin('activity_result', function ($join) use ($user) {
                $join->on('activities.id', '=', 'activity_result.id_activity')
                    ->where('activity_result.id_user', '=', $user->id);
            })
            ->where('users.id', $user->id)
            ->whereIn('classes.token', $kelasList->pluck('token'))
            ->select(
                'activities.id as id_activity',
                'activities.id_topic',
                'activities.title as aktivitas',
                'activities.status',
                'activities.is_group_activity',
                'topics.title as topik',
                'subject.name as mapel',
                'classes.id as id_class',
                'classes.name as nama_kelas',
                'classes.level as level_kelas',
                'activities.created_at',
                // 🔹 pastikan kolom deadline ini ada, kalau beda nama ganti di sini
                'activities.deadline',
                DB::raw('COALESCE(activity_result.nilai_akhir, "-") as result'),
                DB::raw('COALESCE(activity_result.result_status, "Belum Dikerjakan") as result_status')
            )
            ->get();

        // 🔹 List paling atas: semua yang Belum Dikerjakan, urut deadline terdekat
        $belumDikerjakan = $rawActivities
            ->where('result_status', 'Belum Dikerjakan')
            ->sortBy(function ($item) {
                return $item->deadline ?? $item->created_at;
            })
            ->values();

        // 🔹 Activities per kelas
        $activitiesByClass = $rawActivities
            ->groupBy('id_class')
            ->map(function ($group) {
                // urutkan di dalam kelas:
                // 1) Belum Dikerjakan
                // 2) Remedial
                // 3) Pass
                // 4) lainnya
                $sortedList = $group->sortBy(function ($item) {
                    $status = $item->result_status;

                    if ($status === 'Belum Dikerjakan') {
                        $order = 0;
                    } elseif ($status === 'Remedial') {
                        $order = 1;
                    } elseif ($status === 'Pass') {
                        $order = 2;
                    } else {
                        $order = 3;
                    }

                    $tanggal = $item->deadline ?? $item->created_at;

                    return $order . '|' . $tanggal;
                })->values();

                return (object) [
                    'id_class' => $group->first()->id_class,
                    'nama_kelas' => $group->first()->nama_kelas,
                    'level_kelas' => $group->first()->level_kelas,
                    'list' => $sortedList,
                ];
            })
            // urutkan kelas: level lalu nama
            ->sortBy(function ($kelas) {
                return $kelas->level_kelas . '|' . $kelas->nama_kelas;
            })
            ->values();

        // 🔹 Statistik
        $jumlahAktivitas = $rawActivities->count();
        $jumlahRemedial = $rawActivities->where('result_status', 'Remedial')->count();

        // 🔹 Kirim ke view
        return view('siswa.aktivitas', [
            'user' => $user,
            'badge' => $badge,
            'kelasList' => $kelasList,
            'belumDikerjakan' => $belumDikerjakan,
            'activitiesByClass' => $activitiesByClass,
            'jumlahAktivitas' => $jumlahAktivitas,
            'jumlahRemedial' => $jumlahRemedial
        ]);
    }



    public function show($id)
    {
        $activity = Activity::findOrFail($id);

        // Ambil relasi lengkap berdasarkan id_topic
        $info = DB::table('topics')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->where('topics.id', $activity->id_topic)
            ->select(
                'topics.title as topik',
                'subject.name as mapel',
                'classes.name as kelas'
            )
            ->first();

        return view('siswa.menjawabSoal', [
            'judul' => $activity->title,
            'kelas' => $info->kelas,
            'mapel' => $info->mapel,
            'topik' => $info->topik,
            'id_activity' => $activity->id,
            'addaptive' => $activity->addaptive,
            'durasi' => $activity->durasi_pengerjaan,
            'jumlah_soal' => $activity->jumlah_soal,
        ]);
    }
    public function group($id)
    {
        $user = Auth::user();

        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas memang aktivitas kelompok
        if ($activity->is_group_activity !== 'yes') {
            abort(403, 'Aktivitas ini bukan aktivitas kelompok.');
        }

        // Cari kelompok siswa pada aktivitas ini
        $group = ActivityGroup::with('members.user')
            ->where('id_activity', $activity->id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })
            ->first();

        // Jika siswa belum masuk kelompok
        if (!$group) {
            abort(403, 'Kamu belum terdaftar dalam kelompok aktivitas ini.');
        }

        return view('siswa.activity-group', [
            'activity' => $activity,
            'group' => $group,
        ]);
    }

    public function start($id)
    {
        // 1️⃣ reset session lama
        session()->forget("activity.$id");

        $activity = Activity::findOrFail($id);

        // 2️⃣ hitung total soal real di DB
        $totalDB = $activity->questions()->count();

        if ($totalDB === 0) {
            return response()->json([
                'totalQuestions' => 0,
                'message' => 'Soal belum tersedia'
            ], 422);
        }

        // 3️⃣ mode adaptive?
        $adaptive = ($activity->addaptive === 'yes');

        // 4️⃣ tentukan jumlah soal yang akan dipakai
        $jumlahSoal = $activity->jumlah_soal !== null
            ? (int) $activity->jumlah_soal
            : $totalDB;

        // proteksi tambahan
        if ($jumlahSoal <= 0) {
            return response()->json([
                'totalQuestions' => 0,
                'message' => 'Jumlah soal tidak valid'
            ], 422);
        }

        // batasi agar tidak melebihi soal tersedia
        $jumlahSoal = min($jumlahSoal, $totalDB);

        // 5️⃣ inisialisasi session (BARU dibuat setelah valid)
        session([
            "activity.$id.current" => 0,
            "activity.$id.streak_correct" => 0,
            "activity.$id.streak_wrong" => 0,
            "activity.$id.difficulty" => "sedang",
            "activity.$id.totalQuestions" => $jumlahSoal,
            "activity.$id.used_questions" => [],
            "activity.$id.total_correct" => 0,
            "activity.$id.total_base_point" => 0,
            "activity.$id.total_real_point" => 0
        ]);

        // 6️⃣ simpan waktu mulai
        $startTime = Carbon::now();
        session(["activity.$id.start_time" => $startTime->toDateTimeString()]);

        // 7️⃣ simpan / update result
        $userId = Auth::id();
        ActivityResult::updateOrCreate(
            ['id_activity' => $id, 'id_user' => $userId],
            [
                'start_time' => $startTime,
                'waktu_mengerjakan' => null,
                'end_time' => null,
                'total_benar' => null
            ]
        );

        // 8️⃣ durasi
        $durasiMenit = $activity->durasi_pengerjaan
            ? (int) $activity->durasi_pengerjaan
            : null;

        // 9️⃣ response sukses
        return response()->json([
            'mode' => $adaptive ? 'adaptive' : 'normal',
            'level' => session("activity.$id.difficulty"),
            'totalQuestions' => $jumlahSoal,
            'started_at' => $startTime->toDateTimeString(),
            'durasi_pengerjaan' => $durasiMenit
        ]);
    }



    public function getQuestion(Request $req, $id)
    {
        $activity = Activity::findOrFail($id);
        $adaptive = $activity->addaptive === 'yes';
        $index = $req->query('index');

        // Ambil daftar soal yang sudah digunakan
        $used = session("activity.$id.used_questions", []);

        if ($adaptive) {

            $difficulty = session("activity.$id.difficulty", "sedang");

            // Ambil soal sesuai difficulty yang belum pernah dipakai
            $question = $activity->questions()
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $used)
                ->inRandomOrder()
                ->first();

            // Jika soal untuk difficulty ini habis → fallback difficulty lain
            if (!$question) {
                $question = $activity->questions()
                    ->whereNotIn('id', $used)
                    ->inRandomOrder()
                    ->first();
            }
        } else {
            // Mode normal urut biasa
            $question = $activity->questions()
                ->orderBy('id')
                ->skip($index)
                ->first();
        }

        // Jika benar-benar habis (seharusnya jarang terjadi)
        if (!$question) {
            return response()->json([
                'end' => true,
                'message' => 'Tidak ada soal tersisa.'
            ]);
        }

        // ========================
        // HANYA ADAPTIVE yang pakai used_questions
        // ========================
        if ($adaptive) {
            $used[] = $question->id;
            session(["activity.$id.used_questions" => $used]);
        }

        return response()->json([
            'question_id' => $question->id,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'question' => json_decode($question->question),
            'options' => json_decode($question->MC_option),
        ]);
    }

    public function submitAnswer(Request $req, $id)
    {
        $question = Question::findOrFail($req->question_id);
        $adaptive = Activity::find($id)->addaptive === 'yes';

        // ====================================================
        // TAHAP 1: KOREKSI JAWABAN (PG & ISIAN) + NORMALISASI
        // ====================================================
        $correct = false;

        if ($question->type === 'MultipleChoice') {
            // Pilihan ganda: Langsung cocokkan huruf (case-insensitive)
            $correct = strtolower(trim($req->user_answer)) === strtolower(trim($question->MC_answer));
        } else if ($question->type === 'ShortAnswer') {
            $answersRaw = $question->SA_answer;
            $answers = is_string($answersRaw) ? json_decode($answersRaw, true) : $answersRaw;
            if (!is_array($answers)) {
                $answers = [];
            }

            // NORMALISASI JAWABAN SISWA: 
            // 1. Huruf kecil semua 
            // 2. Hapus spasi di awal/akhir 
            // 3. Ubah spasi ganda di tengah menjadi 1 spasi saja (preg_replace)
            $userAnsNormalized = preg_replace('/\s+/', ' ', strtolower(trim($req->user_answer)));

            // Lakukan normalisasi yang sama persis pada array Kunci Jawaban
            $keysNormalized = array_map(function ($ans) {
                return preg_replace('/\s+/', ' ', strtolower(trim($ans)));
            }, $answers);

            // Cek apakah jawaban siswa ada di dalam daftar kunci
            $correct = in_array($userAnsNormalized, $keysNormalized);
        }

        // ====================================================
        // SIMPAN JAWABAN SISWA KE DATABASE (Update / Insert)
        // ====================================================
        ActivityAnswer::updateOrCreate(
            [
                'id_activity' => $id,
                'id_user' => Auth::id(),
                'id_question' => $question->id,
            ],
            [
                'user_answer' => $req->user_answer,
                'is_correct' => $correct, // Menyimpan 1 (True) atau 0 (False)
            ]
        );

        // ====================================================
        // LOGIKA ADAPTIVE (LEVEL & POIN) - Tetap Pakai Logika Lama
        // ====================================================
        if ($adaptive) {
            $correctStreak = session("activity.$id.streak_correct", 0);
            $wrongStreak = session("activity.$id.streak_wrong", 0);
            $level = session("activity.$id.difficulty", "sedang");

            if ($correct) {
                $correctStreak++;
                $wrongStreak = 0;
            } else {
                $wrongStreak++;
                $correctStreak = 0;
            }

            if ($level === 'sedang') {
                if ($correctStreak >= 2) $level = 'sulit';
                if ($wrongStreak >= 2) $level = 'mudah';
            } else if ($level === 'mudah') {
                if ($correctStreak >= 2) $level = 'sedang';
            } else if ($level === 'sulit') {
                if ($wrongStreak >= 2) $level = 'sedang';
            }

            session([
                "activity.$id.difficulty" => $level,
                "activity.$id.streak_correct" => $correctStreak,
                "activity.$id.streak_wrong" => $wrongStreak,
            ]);

            // Hitung Poin Adaptif
            $pointEasy = (int) Settings::where('name', 'soal_mudah')->value('value');
            $pointMedium = (int) Settings::where('name', 'soal_sedang')->value('value');
            $pointHard = (int) Settings::where('name', 'soal_sulit')->value('value');

            $basePoint = $question->difficulty === 'mudah' ? $pointEasy : ($question->difficulty === 'sedang' ? $pointMedium : $pointHard);
            if (!$correct) $basePoint = 0;

            $prevBase = session("activity.$id.total_base_point", 0);
            session(["activity.$id.total_base_point" => $prevBase + $basePoint]);

            $bonus = 0;
            if ($correct) {
                if ($correctStreak == 2) $bonus = 5;
                else if ($correctStreak == 3) $bonus = 10;
                else if ($correctStreak >= 4) $bonus = 15;
            }

            $prevReal = session("activity.$id.total_real_point", 0);
            session(["activity.$id.total_real_point" => $prevReal + ($basePoint + $bonus)]);
        }

        $saOptions = [];
        if ($question->type === 'ShortAnswer') {
            $saOptions = is_array($question->SA_answer) ? $question->SA_answer : json_decode($question->SA_answer, true);
            if (!is_array($saOptions)) $saOptions = [];
        }

        return response()->json([
            'correct' => $correct,
            'correct_answer' => $question->type === 'MultipleChoice' ? strtoupper($question->MC_answer) : implode(', ', $saOptions),
            'explanation' => $question->explanation ?? null,
            'new_level' => session("activity.$id.difficulty"),
            'streak_correct' => session("activity.$id.streak_correct")
        ]);
    }

    public function finishTest(Request $req, $id)
    {
        $userId = Auth::id();
        $activity = Activity::findOrFail($id);

        $totalBase = session("activity.$id.total_base_point", 0);
        $totalReal = session("activity.$id.total_real_point", 0);
        $bonusPoint = $totalReal - $totalBase;

        // Ambil waktu mulai & hitung durasi
        $activityResult = ActivityResult::where('id_activity', $id)->where('id_user', $userId)->first();
        if ($activityResult && $activityResult->start_time) {
            $start = Carbon::parse($activityResult->start_time);
        } else {
            $startString = session("activity.$id.start_time", null);
            $start = $startString ? Carbon::parse($startString) : Carbon::now();
        }
        $end = Carbon::now();
        $durationSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());

        // ====================================================
        // HITUNG TOTAL BENAR (ANTI-CHEAT: Tarik Langsung dari DB)
        // ====================================================
        $totalCorrect = ActivityAnswer::where('id_activity', $id)
            ->where('id_user', $userId)
            ->where('is_correct', 1)
            ->count();

        // Jumlah soal yang dikerjakan
        $jumlahSoal = session("activity.$id.totalQuestions", null);
        if ($jumlahSoal === null) {
            $jumlahSoal = $activity->questions()->count();
        }
        $jumlahSoal = (int) $jumlahSoal;

        $statusBenar = ($totalCorrect === $jumlahSoal) ? true : false;

        // ====================================================
        // TAHAP 1: PERHITUNGAN NILAI INDIVIDUAL 1 - 100
        // ====================================================
        $pointEasy = (float) (Settings::where('name', 'soal_mudah')->value('value') ?? 0);
        $pointMedium = (float) (Settings::where('name', 'soal_sedang')->value('value') ?? 0);
        $pointHard = (float) (Settings::where('name', 'soal_sulit')->value('value') ?? 0);

        if ($activity->addaptive === 'yes') {
            // Logika Adaptif
            $mediumBest = min(2, $jumlahSoal);
            $hardBest = max(0, $jumlahSoal - $mediumBest);
            $bestCase = ($mediumBest * $pointMedium) + ($hardBest * $pointHard);
            if ($bestCase <= 0) $bestCase = 1;

            $nilaiAkhir = round(($totalBase / $bestCase) * 100, 2);
        } else {
            // Logika Non-Adaptif Biasa (Contoh: Benar 8 / 10 * 100 = Nilai 80)
            if ($jumlahSoal > 0) {
                $nilaiAkhir = round(($totalCorrect / $jumlahSoal) * 100, 2);
            } else {
                $nilaiAkhir = 0;
            }
        }

        // Batasi nilai agar tidak menembus batas aneh
        if ($nilaiAkhir > 100) $nilaiAkhir = 100;

        $status = $nilaiAkhir >= ($activity->kkm ?? 70) ? 'Pass' : 'Remedial';

        // ====================================================
        // SIMPAN HASIL AKHIR KE DATABASE
        // ====================================================
        ActivityResult::updateOrCreate(
            ['id_activity' => $id, 'id_user' => $userId],
            [
                'result' => $totalReal,
                'bonus_poin' => $bonusPoint,
                'real_poin' => $totalBase,
                'result_status' => $status,
                'waktu_mengerjakan' => $durationSeconds,
                'total_benar' => $totalCorrect,
                'start_time' => $start,
                'end_time' => $end,
                'status_benar' => $statusBenar,
                'nilai_akhir' => $nilaiAkhir,
            ]
        );

        $activityResult = ActivityResult::where('id_activity', $id)->where('id_user', $userId)->first();

        // Bersihkan session
        session()->forget("activity.$id");

        return response()->json([
            'status' => 'saved',
            'duration_seconds' => $durationSeconds,
            'total_correct' => $totalCorrect,
            'jumlah_soal' => $jumlahSoal,
            'result_db' => $activityResult ? [
                'result' => $activityResult->result,
                'bonus_poin' => $activityResult->bonus_poin,
                'real_poin' => $activityResult->real_poin,
                'result_status' => $activityResult->result_status,
                'waktu_mengerjakan' => $activityResult->waktu_mengerjakan,
                'total_benar' => $activityResult->total_benar,
                'start_time' => optional($activityResult->start_time)->toDateTimeString(),
                'end_time' => optional($activityResult->end_time)->toDateTimeString(),
                'status_benar' => (bool) $activityResult->status_benar,
                'nilai_akhir' => $activityResult->nilai_akhir,
            ] : null,
        ]);
    }
}
