<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ActivityMatrixController extends Controller
{
    /**
     * HALAMAN LIST (pilih kelas & aktivitas)
     */
    public function list()
    {
        $classes = DB::table('teacher_classes as tc')
            ->join('classes as c', 'c.id', '=', 'tc.id_class')
            ->where('tc.id_teacher', auth()->id())
            ->select('c.id', 'c.name')
            ->get();

        return view('guru.matrixlist', compact('classes'));
    }

    /**
     * HALAMAN MATRIX
     * Baris = soal
     * Kolom = siswa
     */
    public function index($activityId, $classId)
    {
        // ======================
        // SOAL DALAM AKTIVITAS
        // ======================
        $questions = DB::table('activity_question as aq')
            ->join('question as q', 'q.id', '=', 'aq.id_question')
            ->where('aq.id_activity', $activityId)
            ->select('q.id', 'q.question')
            ->orderBy('q.id')
            ->get();

        // ======================
        // SISWA DALAM KELAS
        // ======================
        $students = DB::table('student_classes as sc')
            ->join('users as u', 'u.id', '=', 'sc.id_student')
            ->where('sc.id_class', $classId)
            ->select('u.id', 'u.name')
            ->orderBy('u.name')
            ->get();

        // ======================
        // JAWABAN PER SOAL
        // ======================
        $answers = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->select('id_question', 'id_user', 'is_correct')
            ->get();

        $matrix = [];
        foreach ($answers as $a) {
            $matrix[$a->id_question][$a->id_user] = $a->is_correct;
        }

        // ======================
        // TOTAL BENAR PER SOAL
        // ======================
        $totalCorrectPerQuestion = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->where('is_correct', 1)
            ->select('id_question', DB::raw('COUNT(*) as total'))
            ->groupBy('id_question')
            ->pluck('total', 'id_question');
        // [id_question => total_benar]

        // ======================
        // TOTAL BENAR PER SISWA
        // ======================
        $totalCorrectPerStudent = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->where('is_correct', 1)
            ->select('id_user', DB::raw('COUNT(*) as total'))
            ->groupBy('id_user')
            ->pluck('total', 'id_user');
        // [id_user => total_benar]

        // ======================
        // NILAI AKHIR
        // ======================
        $finalScores = DB::table('activity_result')
            ->where('id_activity', $activityId)
            ->pluck('nilai_akhir', 'id_user');

        return view('guru.rekaphasil', compact(
            'questions',
            'students',
            'matrix',
            'finalScores',
            'totalCorrectPerQuestion',
            'totalCorrectPerStudent'
        ))->with([
                    'activityId' => $activityId,
                    'classId' => $classId
                ]);
    }

    public function exportExcel($activityId, $classId)
    {
        // ======================
        // AMBIL DATA
        // ======================
        $questions = DB::table('activity_question as aq')
            ->join('question as q', 'q.id', '=', 'aq.id_question')
            ->where('aq.id_activity', $activityId)
            ->select('q.id', 'q.question')
            ->orderBy('q.id')
            ->get();

        $students = DB::table('student_classes as sc')
            ->join('users as u', 'u.id', '=', 'sc.id_student')
            ->where('sc.id_class', $classId)
            ->select('u.id', 'u.name')
            ->orderBy('u.name')
            ->get();

        $answers = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->select('id_question', 'id_user', 'is_correct')
            ->get();

        $matrix = [];
        foreach ($answers as $a) {
            $matrix[$a->id_question][$a->id_user] = $a->is_correct;
        }

        $totalCorrectPerQuestion = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->where('is_correct', 1)
            ->select('id_question', DB::raw('COUNT(*) as total'))
            ->groupBy('id_question')
            ->pluck('total', 'id_question');

        $totalCorrectPerStudent = DB::table('activity_answers')
            ->where('id_activity', $activityId)
            ->where('is_correct', 1)
            ->select('id_user', DB::raw('COUNT(*) as total'))
            ->groupBy('id_user')
            ->pluck('total', 'id_user');

        $finalScores = DB::table('activity_result')
            ->where('id_activity', $activityId)
            ->pluck('nilai_akhir', 'id_user');

        // ======================
        // BUAT EXCEL
        // ======================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriks Aktivitas');

        $row = 1;
        $col = 1;

        // ======================
        // HEADER
        // ======================
        $sheet->setCellValue([$col++, $row], 'Soal');

        foreach ($students as $s) {
            $sheet->setCellValue([$col++, $row], $s->name);
        }

        $sheet->setCellValue([$col++, $row], 'Total Benar');

        // Style header
        $lastColLetter = Coordinate::stringFromColumnIndex($col - 1);
        $sheet->getStyle("A1:{$lastColLetter}1")
            ->getFont()->setBold(true);

        // ======================
        // ISI SOAL
        // ======================
        foreach ($questions as $q) {
            $row++;
            $col = 1;

            $text = data_get(json_decode($q->question, true), 'text', 'Soal');
            $sheet->setCellValue([$col++, $row], strip_tags($text));

            foreach ($students as $s) {
                $val = $matrix[$q->id][$s->id] ?? null;
                $sheet->setCellValue(
                    [$col++, $row],
                    $val === 1 ? 'Benar' : ($val === 0 ? 'Salah' : '-')
                );
            }

            $sheet->setCellValue(
                [$col++, $row],
                $totalCorrectPerQuestion[$q->id] ?? 0
            );
        }

        // ======================
        // TOTAL BENAR PER SISWA
        // ======================
        $row++;
        $col = 1;
        $sheet->setCellValue([$col++, $row], 'Total Benar Siswa');

        foreach ($students as $s) {
            $sheet->setCellValue(
                [$col++, $row],
                $totalCorrectPerStudent[$s->id] ?? 0
            );
        }

        // ======================
        // NILAI AKHIR
        // ======================
        $row++;
        $col = 1;
        $sheet->setCellValue([$col++, $row], 'Nilai Akhir');

        foreach ($students as $s) {
            $sheet->setCellValue(
                [$col++, $row],
                $finalScores[$s->id] ?? '-'
            );
        }

        // ======================
        // AUTO WIDTH
        // ======================
        foreach (range(1, $col) as $i) {
            $sheet->getColumnDimension(
                Coordinate::stringFromColumnIndex($i)
            )->setAutoSize(true);
        }

        // ======================
        // DOWNLOAD
        // ======================
        $writer = new Xlsx($spreadsheet);
        $filename = "Matriks_Aktivitas_{$activityId}_Kelas_{$classId}.xlsx";

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
