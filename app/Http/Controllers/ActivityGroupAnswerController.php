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
use App\Models\ActivityResult;
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
        $members = $group->members;

        return view('siswa.activity-group-answer', compact('activity', 'group', 'questions', 'answers', 'user', 'isMode1', 'isMode2', 'members'));
    }

    public function save(Request $request, $id)
{
    $user = Auth::user();

    try {

        $activity = Activity::findOrFail($id);

        // Pastikan aktivitas memang kelompok
        if ($activity->is_group_activity !== 'yes') {
            return response()->json([
                'status' => 'error',
                'message' => 'Aktivitas ini bukan aktivitas kelompok.'
            ], 403);
        }

        // Cari kelompok user
        $group = ActivityGroup::where('id_activity', $activity->id)
            ->whereHas('members', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            })
            ->first();

        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan anggota kelompok pada aktivitas ini.'
            ], 403);
        }

        // Ambil jawaban dari AJAX
        $jawabanArray = $request->input('jawaban', []);

        if (!is_array($jawabanArray)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format jawaban tidak valid.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | MODE 2
        |--------------------------------------------------------------------------
        */
        if ($activity->evaluation_mode === 'mode2') {

            $package = ActivityStudentPackage::where(
                'id_activity',
                $activity->id
            )
                ->where('id_user', $user->id)
                ->latest('id')
                ->first();

            if (!$package) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Paket soal belum tersedia.'
                ], 404);
            }

            $totalSoal = count($jawabanArray);
            $jumlahBenar = 0;

            foreach ($jawabanArray as $item) {

                if (
                    !isset($item['soal_id']) ||
                    !array_key_exists('jawaban', $item)
                ) {
                    continue;
                }

                $jawaban = $item['jawaban'];

                if ($jawaban === null) {
                    continue;
                }

                $isCorrect = 0;

                $jawabanSiswa = strtolower(
                    trim((string) $jawaban)
                );

                $soal = \App\Models\Question::find(
                    $item['soal_id']
                );

                if ($soal) {

                    /*
                    |--------------------------------------------------------------------------
                    | PILIHAN GANDA
                    |--------------------------------------------------------------------------
                    */

                    if ($soal->type === 'MultipleChoice') {

                        $kunciMC = strtolower(
                            trim((string) $soal->MC_answer)
                        );

                        if ($jawabanSiswa === $kunciMC) {
                            $isCorrect = 1;
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ISIAN SINGKAT
                    |--------------------------------------------------------------------------
                    */

                    elseif ($soal->type === 'ShortAnswer') {

                        $kunciSA = json_decode(
                            $soal->SA_answer,
                            true
                        );

                        if (is_array($kunciSA)) {

                            foreach ($kunciSA as $kunci) {

                                $kunciBersih = strtolower(
                                    trim((string) $kunci)
                                );

                                if (
                                    $kunciBersih !== '' &&
                                    (
                                        str_contains(
                                            $jawabanSiswa,
                                            $kunciBersih
                                        ) ||
                                        $jawabanSiswa === $kunciBersih
                                    )
                                ) {
                                    $isCorrect = 1;
                                    break;
                                }
                            }

                        } elseif (is_string($soal->SA_answer)) {

                            $kunciBersih = strtolower(
                                trim((string) $soal->SA_answer)
                            );

                            if (
                                $kunciBersih !== '' &&
                                (
                                    str_contains(
                                        $jawabanSiswa,
                                        $kunciBersih
                                    ) ||
                                    $jawabanSiswa === $kunciBersih
                                )
                            ) {
                                $isCorrect = 1;
                            }
                        }
                    }
                }

                if ($isCorrect === 1) {
                    $jumlahBenar++;
                }

                /*
                |--------------------------------------------------------------------------
                | SIMPAN JAWABAN MODE 2
                |--------------------------------------------------------------------------
                */

                ActivityAnswer::updateOrCreate(
                    [
                        'id_activity' => $activity->id,
                        'id_question' => $item['soal_id'],
                        'id_user' => $user->id,
                    ],
                    [
                        'user_answer' => $jawaban,
                        'is_correct' => $isCorrect,
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | HITUNG NILAI MODE 2
            |--------------------------------------------------------------------------
            */

            $nilaiMurni = $totalSoal > 0
                ? round(
                    ($jumlahBenar / $totalSoal) * 100,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | TENTUKAN STATUS BERDASARKAN KKM
            |--------------------------------------------------------------------------
            */

            $status = $nilaiMurni >= $activity->kkm
                ? 'Pass'
                : 'Remedial';


            /*
            |--------------------------------------------------------------------------
            | SIMPAN HASIL
            |--------------------------------------------------------------------------
            */

            ActivityResult::updateOrCreate(
                [
                    'id_activity' => $activity->id,
                    'id_user' => $user->id,
                ],
                [
                    'result' => $nilaiMurni,
                    'total_benar' => $jumlahBenar,
                    'result_status' => $status,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | TANDAI PACKAGE SUDAH SUBMIT
            |--------------------------------------------------------------------------
            */

            $package->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MODE 1
        |--------------------------------------------------------------------------
        */
        else {

            foreach ($jawabanArray as $item) {

                if (
                    !isset($item['soal_id']) ||
                    !array_key_exists('jawaban', $item)
                ) {
                    continue;
                }

                ActivityGroupAnswer::updateOrCreate(
                    [
                        'id_activity' => $activity->id,
                        'id_group' => $group->id,
                        'id_question' => $item['soal_id'],
                        'id_user' => $user->id,
                    ],
                    [
                        'answer' => $item['jawaban'] ?? '',
                    ]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSE SUKSES
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan.',
            'next_url' => route(
                'activity.group.rating',
                $activity->id
            ),
        ]);


    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | CATAT ERROR
        |--------------------------------------------------------------------------
        */

        \Log::error(
            'GAGAL SUBMIT AKTIVITAS KELOMPOK',
            [
                'activity_id' => $id,
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]
        );

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server.',
            'debug' => config('app.debug')
                ? $e->getMessage()
                : null,
        ], 500);
    }
}
}
