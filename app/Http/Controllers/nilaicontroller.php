<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class nilaicontroller extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();

        // 1) ambil id_class yang diaampu guru dari pivot teacher_classes
        $classIds = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->pluck('id_class')
            ->toArray();

        // jika kosong -> return view kosong (tidak ada kelas)
        if (empty($classIds)) {
            $grouped = collect([]);
            return view('guru.datanilai', [
                'grouped' => $grouped
            ]);
        }

        // 2) ambil detail kelas
        // gunakan Eloquent jika ada model Classes, kalau belum ada gunakan DB
        $classes = null;
        if (class_exists(Classes::class)) {
            $classes = Classes::whereIn('id', $classIds)->get();
        } else {
            $classes = DB::table('classes')->whereIn('id', $classIds)->get();
        }

        $resultByClass = collect();

        // 3) untuk tiap kelas ambil siswa, subject, topic, activity, dan hasil
        foreach ($classIds as $classId) {

            // a) siswa di kelas (via student_classes)
            $studentIds = DB::table('student_classes')
                ->where('id_class', $classId)
                ->pluck('id_student')
                ->toArray();

            $students = collect();
            if (!empty($studentIds)) {
                // ambil data user siswa (asumsi tabel users)
                $students = User::whereIn('id', $studentIds)
                    ->select('id', 'name', 'email') // sesuaikan fields
                    ->get();
            }

            // b) subjects di kelas ini
            $subjects = Subject::where('id_class', $classId)
                ->with([
                    'topics' => function ($qTopic) {
                        // load activities for each topic
                        $qTopic->with([
                            'activities' => function ($qAct) {
                                // eager load activity results (if relation ada)
                                $qAct->with([
                                    'activityResults' => function ($qAR) {
                                        $qAR->select('id', 'id_activity', 'id_user', 'nilai_akhir', 'result');
                                    }
                                ]);
                            }
                        ]);
                    }
                ])
                ->get();

            // If Subject model absent or something, fallback to raw queries:
            if ($subjects->isEmpty()) {
                // fallback: ambil topics yang subject.id_class = classId
                $subjects = Subject::where('id_class', $classId)
                    ->with(['topics.activities.activityResults'])
                    ->get();
            }

            // c) susun struktur data per kelas
            $classData = [
                'class_id' => $classId,
                // ambil nama kelas jika ada model Classes
                'class_name' => null,
                'students' => $students,
                'subjects' => []
            ];

            // jika model Classes ada, cari nama
            if (class_exists(Classes::class)) {
                $classModel = Classes::find($classId);
                $classData['class_name'] = $classModel ? $classModel->name : ('Kelas ' . $classId);
            } else {
                $classData['class_name'] = 'Kelas ' . $classId;
            }

            foreach ($subjects as $subject) {
                $subjectItem = [
                    'id' => $subject->id ?? $subject['id'] ?? null,
                    'name' => $subject->name ?? $subject['name'] ?? 'Mata Pelajaran',
                    'topics' => []
                ];

                // topics for subject
                $topics = $subject->topics ?? collect();
                foreach ($topics as $topic) {
                    $topicItem = [
                        'id' => $topic->id ?? null,
                        'title' => $topic->title ?? $topic['title'] ?? 'Topik',
                        'activities' => []
                    ];

                    // ambil activities for topic
                    // prefer Eloquent relation if available
                    $activities = collect();
                    if (isset($topic->activities)) {
                        $activities = $topic->activities;
                    } else {
                        $activities = Activity::where('id_topic', $topic->id)->get();
                    }

                    foreach ($activities as $activity) {
                        // ambil hasil dari activity_result yang cocok dengan siswa di kelas ini
                        // ambil hasil baik dari kolom nilai_akhir (jika tersedia) atau fallback ke 'result'
                        $resultsQuery = DB::table('activity_result')
                            ->where('id_activity', $activity->id)
                            ->whereIn('id_user', $studentIds);

                        // select both possible fields so we can pick later
                        $results = $resultsQuery->select('id', 'id_activity', 'id_user', 'nilai_akhir', 'result')->get();

                        // map results keyed by student id for quick access
                        $resultsByStudent = [];
                        foreach ($results as $r) {
                            // prefer nilai_akhir if not null, else result
                            $nilai = null;
                            if (isset($r->nilai_akhir) && !is_null($r->nilai_akhir)) {
                                $nilai = $r->nilai_akhir;
                            } elseif (isset($r->result) && !is_null($r->result)) {
                                $nilai = $r->result;
                            }
                            $resultsByStudent[$r->id_user] = [
                                'id' => $r->id,
                                'nilai' => $nilai
                            ];
                        }

                        $activityItem = [
                            'id' => $activity->id,
                            'title' => $activity->title ?? ($activity->name ?? 'Aktivitas'),
                            'is_group_activity' => $activity->is_group_activity,
                            'results' => $resultsByStudent,
                            'results_count' => count($resultsByStudent)
                        ];

                        $topicItem['activities'][] = $activityItem;
                    }

                    $subjectItem['topics'][] = $topicItem;
                }

                $classData['subjects'][] = $subjectItem;
            }

            $resultByClass->push($classData);
        }
        

        // kirim ke view: $resultByClass berisi array per kelas
        return view('guru.datanilai', [
            'grouped' => $resultByClass
        ]);
    }
    public function hapusNilai($idActivity)
{
    $teacherId = Auth::id();

    $activity = Activity::with('topic.subject')->findOrFail($idActivity);
    $classId = optional(optional($activity->topic)->subject)->id_class;

    // pastikan guru mengajar kelas terkait aktivitas ini
    $teaches = DB::table('teacher_classes')
        ->where('id_teacher', $teacherId)
        ->where('id_class', $classId)
        ->exists();

    if (!$teaches) {
        return redirect()->back()->with('error', 'Anda tidak berwenang menghapus nilai aktivitas ini.');
    }

    DB::beginTransaction();
    try {
        // Hapus hasil akhir (sumber badge "X Dinilai")
        DB::table('activity_result')->where('id_activity', $idActivity)->delete();

        // Bersihkan juga data terkait supaya siswa bisa mengerjakan ulang dari awal
        DB::table('activity_answers')->where('id_activity', $idActivity)->delete();
        DB::table('activity_group_ratings')->where('id_activity', $idActivity)->delete();
        DB::table('user_badge')->where('id_activity', $idActivity)->delete();

        // Untuk Mode 2 kelompok: hapus paket soal & jawaban kelompok supaya soal di-random ulang
        $packageIds = DB::table('activity_student_packages')
    ->where('id_activity', $idActivity)
    ->pluck('id');

if ($packageIds->isNotEmpty()) {
    DB::table('activity_student_questions')->whereIn('id_package', $packageIds)->delete();
    DB::table('activity_student_packages')->where('id_activity', $idActivity)->delete();
}

        DB::commit();

        return redirect()->back()->with('swal', true)->with([
            'swal.icon' => 'success',
            'swal.title' => 'Berhasil',
            'swal.text' => 'Semua nilai untuk aktivitas ' . $activity->title . ' telah dihapus.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('hapusNilai error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menghapus nilai: ' . $e->getMessage());
    }
}

    /**
     * Tampilkan detail nilai untuk sebuah activity:
     * - pastikan guru mengajar kelas terkait
     * - ambil siswa kelas tersebut dan tampilkan nilai_akhir (atau result)
     */
    public function showActivity(Request $request, $id)
    {
        $teacherId = Auth::id();
        $activity = Activity::with('topic.subject')->findOrFail($id);
        $classId = optional(optional($activity->topic)->subject)->id_class;

        $teaches = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->where('id_class', $classId)
            ->exists();

        if (!$teaches) abort(403, 'Tidak diizinkan melihat data ini.');

        $studentIds = DB::table('student_classes')->where('id_class', $classId)->pluck('id_student')->toArray();
        $students = User::whereIn('id', $studentIds)->get(['id', 'name']);

        $results = DB::table('activity_result')
            ->where('id_activity', $activity->id)
            ->whereIn('id_user', $studentIds)
            ->select('id', 'id_activity', 'id_user', 'nilai_akhir', 'result')
            ->get()
            ->keyBy('id_user');

        // =========================================================================
        // KALKULASI SCI
        // =========================================================================
        $isMode2 = $activity->evaluation_mode === 'mode2';
        $isMode1 = $activity->evaluation_mode === 'mode1' && $activity->is_group_activity === 'yes';
        $isGroupActivity = $activity->is_group_activity === 'yes';

        $groupBaseScores = [];
        $memberSCIs = [];
        $groupMembersList = [];

        if ($isGroupActivity) {
            $activityGroups = DB::table('activity_groups')->where('id_activity', $activity->id)->get()->keyBy('id');
            $allGroupMembers = DB::table('activity_group_members')->whereIn('id_group', $activityGroups->keys())->get();
            $groupedMembers = collect($allGroupMembers)->groupBy('id_group');

            foreach ($groupedMembers as $groupId => $members) {
                $totalPr = 0;
                $userPrScores = [];

                foreach ($members as $m) {
                    $avgRating = DB::table('activity_group_ratings')
                        ->where('id_activity', $activity->id)->where('id_group', $groupId)->where('id_evaluated', $m->id_user)
                        ->avg('score');
                    $pr = $avgRating !== null ? (float) $avgRating : 100;
                    $userPrScores[$m->id_user] = $pr;
                    $totalPr += $pr;
                    $groupMembersList[$m->id_user] = $groupId;
                }

                // =======================================================
                // KEMBALIKAN FITUR ANTI-ERROR (HITUNG MUNDUR) MODE 1
                // =======================================================
                if (!$isMode2) {
                    $groupObj = $activityGroups->get($groupId);
                    $base = $groupObj && isset($groupObj->nilai_kelompok) ? (float) $groupObj->nilai_kelompok : 0;

                    // Jika di database bernilai 0 (karena ini adalah data yang dinilai sebelum ada update sistem)
                    // Maka kita hitung mundur dari Nilai Akhir dibagi SCI
                    if ($base == 0) {
                        $firstMember = $members->first();
                        $resFirst = $results->get($firstMember->id_user);
                        if ($resFirst && isset($resFirst->nilai_akhir) && (float)$resFirst->nilai_akhir > 0) {
                            $myPr = $userPrScores[$firstMember->id_user] ?? 100;
                            $groupPrAvg = count($members) > 0 ? ($totalPr / count($members)) : 100;
                            $sci = $groupPrAvg > 0 ? round($myPr / $groupPrAvg, 2) : 1.00;

                            if ($sci > 0) {
                                $base = round((float)$resFirst->nilai_akhir / $sci, 2);
                            }
                        }
                    }
                    $groupBaseScores[$groupId] = $base;
                }

                $groupPrAvg = count($members) > 0 ? ($totalPr / count($members)) : 100;
                foreach ($members as $m) {
                    $myPr = $userPrScores[$m->id_user];
                    $memberSCIs[$m->id_user] = $groupPrAvg > 0 ? round($myPr / $groupPrAvg, 2) : 1.00;
                }
            }
        }

        $studentBadges = DB::table('user_badge')
            ->join('badge', 'user_badge.id_badge', '=', 'badge.id')
            ->whereIn('user_badge.id_student', $studentIds)
            ->where(function ($q) use ($activity) {
                $q->where('user_badge.id_activity', $activity->id)
                    ->orWhereNull('user_badge.id_activity');
            })
            ->select('user_badge.id_student', 'badge.name', 'badge.id as badge_id')
            ->get()
            ->keyBy('id_student');

// =========================================================================
        // SUSUN DATA UNTUK TABEL
        // =========================================================================
        $studentRows = $students->map(function ($s) use ($results, $isGroupActivity, $groupMembersList, $groupBaseScores, $memberSCIs, $studentBadges, $isMode2) {
            $res = $results->get($s->id);
            $rawScore = $res ? (float) ($res->result ?? 0) : 0; // NILAI MURNI KUIS
            
            $sci = '-';
            $nilaiKolomPertama = '-'; 
            $badgeText = '<span class="no-data">-</span>';
            $nilaiIndividu = null;

            if ($isGroupActivity && isset($groupMembersList[$s->id])) {
                $groupId = $groupMembersList[$s->id];
                $sciValue = $memberSCIs[$s->id] ?? 1.00;
                $sci = $sciValue;

                // =========================================================
                // PERHITUNGAN DINAMIS: Nilai Akhir Langsung Dikalikan SCI
                // =========================================================
                if ($isMode2) {
                    // MODE 2: Nilai Murni x SCI
                    $nilaiKolomPertama = $res && isset($res->result) ? number_format($rawScore, 2, '.', '') : '-';
                    $nilaiIndividu = $res && isset($res->result) ? round($rawScore * $sciValue, 2) : null;
                } else {
                    // MODE 1: Nilai Kelompok x SCI
                    $baseScore = $groupBaseScores[$groupId] ?? 0;
                    $nilaiKolomPertama = $baseScore > 0 ? number_format($baseScore, 2, '.', '') : '-';
                    $nilaiIndividu = $baseScore > 0 ? round($baseScore * $sciValue, 2) : null;
                }

                // Cegah agar Nilai Akhir tidak pernah lebih dari 100
                if ($nilaiIndividu > 100) {
                    $nilaiIndividu = 100;
                }

                $myBadge = $studentBadges->get($s->id);
                if ($myBadge) {
                    $bName = $myBadge->name;
                    $bNameLower = strtolower($bName);

                    if (str_contains($bNameLower, 'carry') || $myBadge->badge_id == 4) {
                        $badgeText = '<span class="badge bg-primary shadow-sm"><i class="fas fa-medal me-1"></i> ' . $bName . '</span>';
                    } elseif (str_contains($bNameLower, 'help') || $myBadge->badge_id == 6) {
                        $badgeText = '<span class="badge bg-danger shadow-sm"><i class="fas fa-life-ring me-1"></i> ' . $bName . '</span>';
                    } else {
                        $badgeText = '<span class="badge bg-success shadow-sm"><i class="fas fa-handshake me-1"></i> ' . $bName . '</span>';
                    }
                }
            } elseif (!$isGroupActivity) {
                $nilaiKolomPertama = 'Indiv.';
                $sci = 'N/A';
                $nilaiIndividu = $res ? (float) ($res->nilai_akhir ?? $rawScore) : null;
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'nilai' => $nilaiIndividu !== null ? number_format((float)$nilaiIndividu, 2, '.', '') : null,
                'sci' => $sci,
                'nilai_kolom_pertama' => $nilaiKolomPertama,
                'badge' => $badgeText,
                'id_group' => $groupMembersList[$s->id] ?? null
            ];
        });

        // =========================================================================
        // EXPORT EXCEL
        // =========================================================================
        if ($request->query('export') === 'xlsx') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Siswa');
            $sheet->setCellValue('C1', $isMode2 ? 'Nilai Murni' : 'Nilai Kelompok');
            $sheet->setCellValue('D1', 'SCI');
            $sheet->setCellValue('E1', 'Nilai Akhir');
            $sheet->setCellValue('F1', 'Badge');

            $row = 2;
            foreach ($studentRows as $index => $stu) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValueExplicit('B' . $row, $stu['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $stu['nilai_kolom_pertama'] ?? '-');
                $sheet->setCellValue('D' . $row, $stu['sci'] ?? '-');

                if (is_numeric($stu['nilai'])) {
                    $sheet->setCellValue('E' . $row, (float) $stu['nilai']);
                } else {
                    $sheet->setCellValueExplicit('E' . $row, $stu['nilai'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }

                $cleanBadgeText = strip_tags($stu['badge']);
                $sheet->setCellValue('F' . $row, $cleanBadgeText);
                $row++;
            }

            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $safeActivityTitle = preg_replace('/[^A-Za-z0-9\-]/', '_', substr($activity->title ?? 'activity', 0, 30));
            $filename = "nilai_{$safeActivityTitle}_{$activity->id}_" . date('Ymd_His') . ".xlsx";

            $writer = new Xlsx($spreadsheet);
            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });
            $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }

        return view('guru.detailnilaisiswa', [
            'activity' => $activity,
            'class_id' => $classId,
            'students' => $studentRows,
            'isMode1' => $isMode1,
            'isMode2' => $isMode2
        ]);
    }

    public function exportClassesExcel(Request $request)
    {
        $teacherId = Auth::id();

        // ambil kelas yang diajar
        $classIds = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->pluck('id_class')
            ->toArray();

        if (empty($classIds)) {
            return redirect()->back()->with('error', 'Tidak ada kelas untuk diexport.');
        }

        // Ambil data students per class
        // dan juga daftar activities untuk masing-masing kelas (mengumpulkan semua activities di semua subject/topic)
        $classesData = [];
        foreach ($classIds as $classId) {
            // siswa di kelas
            $studentIds = DB::table('student_classes')->where('id_class', $classId)->pluck('id_student')->toArray();
            $students = collect();
            if (!empty($studentIds)) {
                $students = User::whereIn('id', $studentIds)
                    ->select('id', 'name', 'email')
                    ->orderBy('name')
                    ->get();
            }

            // kumpulkan semua activities untuk kelas ini
            // asumsi: Subject->topics->activities relasi; fallback ke direct query bila relasi tidak ada
            $activities = collect();
            // coba pakai Subject model jika ada
            if (class_exists(Subject::class)) {
                $subjects = Subject::where('id_class', $classId)->with(['topics.activities'])->get();
                foreach ($subjects as $subj) {
                    foreach ($subj->topics ?? [] as $topic) {
                        foreach ($topic->activities ?? [] as $act) {
                            $activities->push($act);
                        }
                    }
                }
            }

            // unique activities by id (hindari duplikat)
            $activities = $activities->unique('id')->values();

            // ambil semua hasil untuk activities ini dan siswa di kelas
            $activityIds = $activities->pluck('id')->toArray();
            $results = [];
            if (!empty($activityIds) && !empty($studentIds)) {
                $rows = DB::table('activity_result')
                    ->whereIn('id_activity', $activityIds)
                    ->whereIn('id_user', $studentIds)
                    ->select('id_activity', 'id_user', 'nilai_akhir', 'result')
                    ->get();

                foreach ($rows as $r) {
                    $val = null;
                    if (isset($r->nilai_akhir) && !is_null($r->nilai_akhir))
                        $val = $r->nilai_akhir;
                    elseif (isset($r->result) && !is_null($r->result))
                        $val = $r->result;
                    $results[$r->id_activity][$r->id_user] = $val;
                }
            }

            // ambil nama kelas bila tersedia
            $className = 'Kelas ' . $classId;
            if (class_exists(Classes::class)) {
                $c = Classes::find($classId);
                if ($c)
                    $className = $c->name ?? $className;
            }

            $classesData[] = [
                'class_id' => $classId,
                'class_name' => $className,
                'students' => $students,
                'activities' => $activities,
                'results' => $results, // indexed by [activityId][studentId] => nilai
            ];
        }

        // Mulai buat spreadsheet
        // ------------------- START REPLACE FROM HERE -------------------
        /** Mulai buat spreadsheet (safe sheet naming + no duplicate addSheet) */
        $spreadsheet = new Spreadsheet();

        // helper: sanitize and ensure unique sheet title (max 31 chars)
        $getUniqueTitle = function ($baseTitle) use ($spreadsheet) {
            // remove illegal characters and trim to 28 chars (reserve room for suffix)
            $clean = preg_replace('/[\\\|\\/?*\\[\\]:]/', '_', $baseTitle);
            $clean = trim(mb_substr($clean, 0, 28));
            $names = $spreadsheet->getSheetNames();

            $candidate = $clean ?: 'Sheet';
            $suffix = 1;
            while (in_array($candidate, $names)) {
                $suffix++;
                $candidate = mb_substr($clean, 0, max(1, 28 - (strlen((string) $suffix) + 1))) . '_' . $suffix;
            }
            return $candidate;
        };

        $first = true;
        foreach ($classesData as $idx => $cdata) {
            if ($first) {
                $sheet = $spreadsheet->getActiveSheet();
                $first = false;
            } else {
                // createSheet already appends the sheet to workbook
                $sheet = $spreadsheet->createSheet();
            }

            // sheet title: sanitize & ensure unique (<=31 chars)
            $titleBase = $cdata['class_name'] ?? ('Class_' . $cdata['class_id']);
            $title = $getUniqueTitle($titleBase);
            // Excel sheet title limit is 31 characters
            $sheet->setTitle(mb_substr($title, 0, 31));

            // header: No | Student ID | Nama Siswa | aktivitas...
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Student ID');
            $sheet->setCellValue('C1', 'Nama Siswa');

            // aktivitas sebagai header kolom mulai dari D
            $colIndex = 4; // D = 4
            $activityMap = []; // map index -> activity id
            foreach ($cdata['activities'] as $act) {
                $safeTitle = $act->title ?? ($act->name ?? ('Activity_' . $act->id));
                $header = mb_substr($safeTitle, 0, 50) . " ({$act->id})";
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1';
                $sheet->setCellValue($cell, $header);
                $activityMap[$colIndex] = $act->id;
                $colIndex++;
            }

            // isi baris siswa
            $row = 2;
            foreach ($cdata['students'] as $i => $stu) {
                $sheet->setCellValue('A' . $row, ($i + 1));
                $sheet->setCellValueExplicit('B' . $row, (string) $stu->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $row, (string) $stu->name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                foreach ($activityMap as $colIdx => $activityId) {
                    $val = null;
                    if (isset($cdata['results'][$activityId]) && isset($cdata['results'][$activityId][$stu->id])) {
                        $val = $cdata['results'][$activityId][$stu->id];
                    }
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $row;
                    if (is_numeric($val)) {
                        $sheet->setCellValue($cell, (float) $val);
                    } else {
                        $sheet->setCellValueExplicit($cell, $val ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                $row++;
            }

            // auto-size kolom sampai colIndex-1
            for ($ci = 1; $ci <= ($colIndex - 1); $ci++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }
        // ------------------- END REPLACE -------------------

        // Buat filename menampilkan guru dan timestamp
        $teacher = User::find($teacherId);
        $teacherName = $teacher ? preg_replace('/[^A-Za-z0-9]/', '_', mb_substr($teacher->name, 0, 20)) : 'teacher';
        $filename = "nilai_semua_kelas_{$teacherName}_" . date('Ymd_His') . ".xlsx";

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
    // Aktivitas Per kelas
    public function exportClassExcel(Request $request, $classId)
    {
        $teacherId = Auth::id();

        // pastikan guru mengajar kelas ini
        $teaches = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->where('id_class', $classId)
            ->exists();

        if (!$teaches) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengekspor kelas ini.');
        }

        // ambil siswa di kelas
        $studentIds = DB::table('student_classes')->where('id_class', $classId)->pluck('id_student')->toArray();
        $students = collect();
        if (!empty($studentIds)) {
            $students = \App\Models\User::whereIn('id', $studentIds)->select('id', 'name', 'email')->orderBy('name')->get();
        }

        // kumpulkan semua activities untuk kelas ini
        $activities = collect();
        if (class_exists(\App\Models\Subject::class)) {
            $subjects = \App\Models\Subject::where('id_class', $classId)->with(['topics.activities'])->get();
            foreach ($subjects as $subj) {
                foreach ($subj->topics ?? [] as $topic) {
                    foreach ($topic->activities ?? [] as $act) {
                        $activities->push($act);
                    }
                }
            }
        }


        $activities = $activities->unique('id')->values();
        $activityIds = $activities->pluck('id')->toArray();

        // ambil hasil untuk activities dan siswa di kelas
        $results = [];
        if (!empty($activityIds) && !empty($studentIds)) {
            $rows = DB::table('activity_result')
                ->whereIn('id_activity', $activityIds)
                ->whereIn('id_user', $studentIds)
                ->select('id_activity', 'id_user', 'nilai_akhir', 'result')
                ->get();

            foreach ($rows as $r) {
                $val = null;
                if (isset($r->nilai_akhir) && !is_null($r->nilai_akhir))
                    $val = $r->nilai_akhir;
                elseif (isset($r->result) && !is_null($r->result))
                    $val = $r->result;
                $results[$r->id_activity][$r->id_user] = $val;
            }
        }

        // nama kelas (jika model Classes ada)
        $className = 'Kelas ' . $classId;
        if (class_exists(\App\Models\Classes::class)) {
            $c = \App\Models\Classes::find($classId);
            if ($c)
                $className = $c->name ?? $className;
        }

        // buat spreadsheet (satu sheet)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // bersihkan judul sheet (Excel max 31 char)
        $sheetTitle = substr(preg_replace('/[\\\|\\/?*\\[\\]:]/', '_', $className), 0, 31);
        $sheet->setTitle($sheetTitle ?: 'Kelas');

        // header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Student ID');
        $sheet->setCellValue('C1', 'Nama Siswa');

        // aktivitas => kolom mulai D
        $colIndex = 4;
        $activityMap = [];
        foreach ($activities as $act) {
            $safeTitle = $act->title ?? ($act->name ?? ('Activity_' . $act->id));
            $header = mb_substr($safeTitle, 0, 50) . " ({$act->id})";
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValue($cell, $header);
            $activityMap[$colIndex] = $act->id;
            $colIndex++;
        }

        // isi siswa per baris
        $row = 2;
        foreach ($students as $i => $stu) {
            $sheet->setCellValue('A' . $row, ($i + 1));
            $sheet->setCellValueExplicit('B' . $row, (string) $stu->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, (string) $stu->name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            foreach ($activityMap as $colIdx => $activityId) {
                $val = null;
                if (isset($results[$activityId]) && isset($results[$activityId][$stu->id])) {
                    $val = $results[$activityId][$stu->id];
                }
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $row;
                if (is_numeric($val)) {
                    $sheet->setCellValue($cell, (float) $val);
                } else {
                    $sheet->setCellValueExplicit($cell, $val ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }

            $row++;
        }

        // auto-size columns
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        for ($ci = 1; $ci <= ($colIndex - 1); $ci++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // filename
        $teacher = \App\Models\User::find($teacherId);
        $teacherName = $teacher ? preg_replace('/[^A-Za-z0-9]/', '_', mb_substr($teacher->name, 0, 20)) : 'teacher';
        $filename = "nilai_kelas_{$sheetTitle}_{$teacherName}_" . date('Ymd_His') . ".xlsx";

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Tampilkan detail jawaban individu untuk dikoreksi guru (Khusus Mode 2)
     */
    public function koreksiJawabanSiswa($idActivity, $idUser)
    {
        $activity = Activity::findOrFail($idActivity);
        $student = User::findOrFail($idUser);

        // Ambil data hasil kuis siswa (jumlah benar, durasi, dll)
        $result = ActivityResult::where('id_activity', $idActivity)
            ->where('id_user', $idUser)
            ->first();

        // Ambil daftar jawaban yang disubmit siswa, di-join dengan tabel soal
        $answers = DB::table('activity_answers')
            ->join('question', 'activity_answers.id_question', '=', 'question.id')
            ->where('activity_answers.id_activity', $idActivity)
            ->where('activity_answers.id_user', $idUser)
            ->select(
                'activity_answers.id as answer_id',
                'activity_answers.user_answer',
                'activity_answers.is_correct',
                'question.id as question_id',
                'question.type',
                'question.difficulty',
                'question.question as soal_teks',
                'question.MC_answer',
                'question.SA_answer'
            )
            ->get();

        // =========================================================================
        // ARAHKAN KE FILE GABUNGAN & BERIKAN PENANDA MODE 2
        // =========================================================================
        return view('guru.evaluasi.penilaian-kelompok', [
            'activity' => $activity,
            'student' => $student,
            'result' => $result,
            'answers' => $answers,
            'isMode2' => true // <--- PENANDA PENTING AGAR BLADE MENJADI MODE KOREKSI INDIVIDU
        ]);
    }

    /**
     * Menyimpan hasil koreksi manual guru untuk Mode 2
     */
    public function simpanKoreksiSiswa(Request $request, $idActivity, $idUser)
    {
        // Tolak akses jika guru mencoba memaksa submit form di Mode 2
        return redirect('/guru/aktivitas/' . $idActivity . '/monitoring')
            ->with('error', 'Kuis Mode 2 dinilai secara otomatis oleh sistem (Auto-Grading). Anda tidak perlu/tidak dapat menyimpan nilai manual.');
    }
}
