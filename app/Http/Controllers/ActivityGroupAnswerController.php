<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\ActivityGroupAnswer;
use App\Models\ActivityAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\aktivitasController;
use App\Models\ActivityStudentPackage;
use App\Models\ActivityStudentQuestion;

class ActivityGroupAnswerController extends Controller
{
    public function show($id)
    {
        $user = Auth::user();
        $activity = Activity::findOrFail($id);

        if ($activity->is_group_activity !== 'yes') abort(403, 'Bukan aktivitas kelompok.');

        $group = ActivityGroup::with(['members.user'])
            ->where('id_activity', $activity->id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->first();

        if (!$group) abort(403, 'Anda belum terdaftar.');

        $isMode2 = $activity->evaluation_mode === 'mode2';
        $isMode1 = $activity->evaluation_mode === 'mode1';

        // ======================================================
        // TARIK SOAL DAN JAWABAN BERDASARKAN MODE
        // ======================================================
        if ($isMode2) {
            app(aktivitasController::class)->startMode2($activity->id);

            $package = ActivityStudentPackage::where('id_activity', $activity->id)->where('id_user', $user->id)->first();
            if (!$package) abort(404, 'Package soal belum tersedia.');

            $questions = ActivityStudentQuestion::with('question')
                ->where('id_package', $package->id)->orderBy('question_order')
                ->get()->map(function ($item) {
                    return $item->question;
                })->filter()->values();

            $answers = ActivityAnswer::where('id_activity', $activity->id)->where('id_user', $user->id)
                ->get()->keyBy(function ($ans) {
                    return $ans->id_question . '_' . $ans->id_user;
                })
                ->map(function ($ans) {
                    $ans->answer = $ans->user_answer; // Samakan format kolom
                    return $ans;
                });
        } else {
            $questions = $activity->questions()->orderBy('activity_question.id_question')->get();

            $answers = ActivityGroupAnswer::where('id_activity', $activity->id)->where('id_group', $group->id)
                ->get()->keyBy(function ($ans) {
                    return $ans->id_question . '_' . $ans->id_user;
                });
        }

        if ($questions->isEmpty()) abort(404, 'Soal belum tersedia.');

        return view('siswa.activity-group-answer', compact('activity', 'group', 'questions', 'answers', 'user', 'isMode1', 'isMode2'));
    }

    public function save(Request $request, $id)
    {
        $user = Auth::user();
        $activity = Activity::findOrFail($id);
        $group = ActivityGroup::where('id_activity', $activity->id)->whereHas('members', function ($q) use ($user) {
            $q->where('id_user', $user->id);
        })->first();

        if (!$group) return response()->json(['error' => 'Bukan anggota kelompok'], 403);

        // AMBIL ARRAY JAWABAN DARI FETCH AJAX
        $jawabanArray = $request->input('jawaban', []);

        // ======================================================
        // BATCH SAVE & AUTO-GRADING SESUAI MODE
        // ======================================================
        if ($activity->evaluation_mode === 'mode2') {
            $package = ActivityStudentPackage::where('id_activity', $activity->id)->where('id_user', $user->id)->latest('id')->first();

            $totalSoal = count($jawabanArray);
            $jumlahBenar = 0;

            foreach ($jawabanArray as $item) {
                if ($item['jawaban'] !== null) {

                    $isCorrect = 0; // Default salah
                    $jawabanSiswa = strtolower(trim((string)$item['jawaban']));

                    // Ambil data soal untuk mencocokkan kunci jawaban
                    $soal = \App\Models\Question::find($item['soal_id']);

                    if ($soal) {
                        // 1. KOREKSI PILIHAN GANDA
                        if ($soal->type === 'MultipleChoice') {
                            $kunciMC = strtolower(trim((string)$soal->MC_answer));
                            if ($jawabanSiswa === $kunciMC) {
                                $isCorrect = 1;
                            }
                        }
                        // 2. KOREKSI ISIAN SINGKAT (KEYWORD MATCHING)
                        elseif ($soal->type === 'ShortAnswer') {
                            $kunciSA = json_decode($soal->SA_answer, true);

                            if (is_array($kunciSA)) {
                                foreach ($kunciSA as $kunci) {
                                    $kunciBersih = strtolower(trim((string)$kunci));
                                    // Jika jawaban siswa mengandung salah satu kata kunci
                                    if ($kunciBersih !== '' && (str_contains($jawabanSiswa, $kunciBersih) || $jawabanSiswa === $kunciBersih)) {
                                        $isCorrect = 1;
                                        break; // Langsung break jika sudah nemu 1 kecocokan
                                    }
                                }
                            } elseif (is_string($soal->SA_answer)) {
                                // Fallback jika SA_answer tersimpan sebagai string biasa
                                $kunciBersih = strtolower(trim((string)$soal->SA_answer));
                                if ($kunciBersih !== '' && (str_contains($jawabanSiswa, $kunciBersih) || $jawabanSiswa === $kunciBersih)) {
                                    $isCorrect = 1;
                                }
                            }
                        }
                    }

                    if ($isCorrect === 1) {
                        $jumlahBenar++;
                    }

                    // Simpan jawaban individu beserta status benar/salahnya
                    ActivityAnswer::updateOrCreate(
                        ['id_activity' => $activity->id, 'id_question' => $item['soal_id'], 'id_user' => $user->id],
                        ['user_answer' => $item['jawaban'], 'id_package' => $package->id, 'is_correct' => $isCorrect]
                    );
                }
            }

            // ==================================================
            // SIMPAN NILAI MURNI LANGSUNG KE ACTIVITY_RESULT
            // ==================================================
            $nilaiMurni = $totalSoal > 0 ? round(($jumlahBenar / $totalSoal) * 100, 2) : 0;

            \App\Models\ActivityResult::updateOrCreate(
                ['id_activity' => $activity->id, 'id_user' => $user->id],
                [
                    'result' => $nilaiMurni, // INI ADALAH NILAI KUIS (MURNI)
                    'total_benar' => $jumlahBenar
                    // CATATAN: 'nilai_akhir' JANGAN DIISI! Biarkan NULL.
                ]
            );

            // Update status paket soal menjadi submitted
            if ($package) {
                $package->update([
                    'status' => 'submitted',
                    'submitted_at' => now()
                ]);
            }
        } else {
            // MODE 1: Uraian Kelompok (Tanpa Auto-Grading)
            foreach ($jawabanArray as $item) {
                if ($item['jawaban'] !== null) {
                    ActivityGroupAnswer::updateOrCreate(
                        ['id_activity' => $activity->id, 'id_group' => $group->id, 'id_question' => $item['soal_id'], 'id_user' => $user->id],
                        ['answer' => $item['jawaban']]
                    );
                }
            }
        }

        // Response untuk Javascript
        return response()->json([
            'status' => 'success',
            'next_url' => route('activity.group.rating', $activity->id) // Arahkan ke SCI!
        ]);
    }
}
