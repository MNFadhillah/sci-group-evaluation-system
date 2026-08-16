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

    /**
     * Tampilkan detail nilai untuk sebuah activity:
     * - pastikan guru mengajar kelas terkait
     * - ambil siswa kelas tersebut dan tampilkan nilai_akhir (atau result)
     */
    public function showActivity(Request $request, $id)
    {
        $teacherId = Auth::id();

        $activity = Activity::with('topic.subject')->findOrFail($id);

        // ambil class id dari activity -> topic -> subject -> id_class
        $classId = optional(optional($activity->topic)->subject)->id_class;

        // pastikan guru mengampu kelas ini
        $teaches = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->where('id_class', $classId)
            ->exists();

        if (!$teaches) {
            abort(403, 'Tidak diizinkan melihat data ini.');
        }

        // ambil siswa kelas ini
        $studentIds = DB::table('student_classes')
            ->where('id_class', $classId)
            ->pluck('id_student')
            ->toArray();

        $students = User::whereIn('id', $studentIds)->get(['id', 'name']);

        // ambil hasil activity untuk siswa-siswa ini
        $results = DB::table('activity_result')
            ->where('id_activity', $activity->id)
            ->whereIn('id_user', $studentIds)
            ->select('id', 'id_activity', 'id_user', 'nilai_akhir', 'result')
            ->get()
            ->keyBy('id_user');

        // =========================================================================
        // MODE 2 YANG BENAR: Nilai Murni, Rata-rata Kelompok, SCI & Badge
        // =========================================================================
        $isGroupActivity = $activity->is_group_activity === 'yes';
        $groupBaseScores = []; // Untuk simpan Nilai Asli Kelompok (Rata-rata)
        $memberSCIs = [];      // Untuk simpan angka SCI

        if ($isGroupActivity) {
            $activityGroups = DB::table('activity_groups')
                ->where('id_activity', $activity->id)
                ->get()
                ->keyBy('id');

            $allGroupMembers = DB::table('activity_group_members')
                ->whereIn('id_group', $activityGroups->keys())
                ->get();

            $groupedMembers = collect($allGroupMembers)->groupBy('id_group');

            foreach ($groupedMembers as $groupId => $members) {
                $sumNilaiIndividu = 0;
                $validMembers = 0;
                $totalPr = 0;

                // 1. Kumpulkan data untuk Nilai Kelompok & SCI
                foreach ($members as $m) {
                    // Ambil Nilai Individu (Skor Murni Pengerjaan)
                    $res = $results->get($m->id_user);
                    if ($res) {
                        $nilaiIndividu = isset($res->nilai_akhir) ? $res->nilai_akhir : (isset($res->result) ? $res->result : 0);
                        $sumNilaiIndividu += $nilaiIndividu;
                        $validMembers++;
                    }

                    // Ambil Rating Teman untuk SCI
                    $avgRating = DB::table('activity_group_ratings')
                        ->where('id_activity', $activity->id)->where('id_group', $groupId)->where('id_evaluated', $m->id_user)
                        ->avg('score');
                    $pr = $avgRating !== null ? (float) $avgRating : 100;
                    $userPrScores[$m->id_user] = $pr;
                    $totalPr += $pr;
                    $groupMembersList[$m->id_user] = $groupId;
                }

                // 2. Tentukan Nilai Asli Kelompok (Rata-rata Nilai Individu)
                $groupBaseScores[$groupId] = $validMembers > 0 ? round($sumNilaiIndividu / $validMembers, 2) : 0;

                // 3. Kalkulasi Angka SCI
                $groupPrAvg = count($members) > 0 ? ($totalPr / count($members)) : 100;
                foreach ($members as $m) {
                    $myPr = $userPrScores[$m->id_user];
                    $memberSCIs[$m->id_user] = $groupPrAvg > 0 ? round($myPr / $groupPrAvg, 2) : 1.00;
                }
            }
        }

        // Ambil Data Badge yang sudah dikunci di database
        $studentBadges = DB::table('user_badge')
            ->join('badge', 'user_badge.id_badge', '=', 'badge.id')
            ->whereIn('user_badge.id_student', $studentIds)
            ->where('user_badge.id_activity', $activity->id)
            ->select('user_badge.id_student', 'badge.name', 'badge.id as badge_id')
            ->get()
            ->keyBy('id_student');

        // =========================================================================
        // SUSUN DATA UNTUK TABEL & LEADERBOARD
        // =========================================================================
        $studentRows = $students->map(function ($s) use ($results, $isGroupActivity, $groupMembersList, $groupBaseScores, $memberSCIs, $studentBadges) {
            $res = $results->get($s->id);
            $nilaiIndividu = null;

            if ($res) {
                $nilaiIndividu = isset($res->nilai_akhir) ? $res->nilai_akhir : (isset($res->result) ? $res->result : 0);
            }

            $sci = '-';
            $nilaiKelompok = '-';
            $badgeText = '<span class="no-data">-</span>';

            if ($isGroupActivity && isset($groupMembersList[$s->id])) {
                $groupId = $groupMembersList[$s->id];
                $nilaiKelompok = number_format($groupBaseScores[$groupId] ?? 0, 2, '.', '');
                $sci = $memberSCIs[$s->id] ?? 1.00;

                // Format Tampilan Badge
                $myBadge = $studentBadges->get($s->id);
                if ($myBadge) {
                    $bId = $myBadge->badge_id;
                    $bName = $myBadge->name;
                    if ($bId == 1) $badgeText = '<span class="badge bg-primary shadow-sm"><i class="fas fa-medal me-1"></i> ' . $bName . '</span>';
                    elseif ($bId == 2) $badgeText = '<span class="badge bg-success shadow-sm"><i class="fas fa-handshake me-1"></i> ' . $bName . '</span>';
                    elseif ($bId == 3) $badgeText = '<span class="badge bg-danger shadow-sm"><i class="fas fa-life-ring me-1"></i> ' . $bName . '</span>';
                }
            } elseif (!$isGroupActivity) {
                $nilaiKelompok = 'Indiv.';
                $sci = 'N/A';
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'nilai' => $nilaiIndividu !== null ? number_format((float)$nilaiIndividu, 2, '.', '') : null, // MURNI NILAI INDIVIDU
                'sci' => $sci,
                'nilai_kelompok' => $nilaiKelompok, // MURNI RATA-RATA
                'badge' => $badgeText,
                'id_group' => $groupMembersList[$s->id] ?? null
            ];
        });

        // =========================================================================
        // SUSUN LEADERBOARD (Diurutkan dari Nilai Kelompok Tertinggi)
        // =========================================================================
        $leaderboard = collect();
        if ($isGroupActivity) {
            $groupedStudents = collect($studentRows)->groupBy('id_group');
            foreach ($groupedStudents as $groupId => $members) {
                if (!$groupId) continue;
                $groupName = isset($activityGroups) && $activityGroups->has($groupId) ? $activityGroups->get($groupId)->name : 'Kelompok ' . $groupId;
                $leaderboard->push([
                    'id_group' => $groupId,
                    'name' => $groupName,
                    'nilai_kelompok' => (float) ($groupBaseScores[$groupId] ?? 0),
                    'members' => $members->sortByDesc('sci')->values() // Urut dari The Carry ke bawah
                ]);
            }
            $leaderboard = $leaderboard->sortByDesc('nilai_kelompok')->values();
        }

        return view('guru.detailnilaisiswa', [
            'activity' => $activity,
            'class_id' => $classId,
            'students' => $studentRows,
            'leaderboard' => $leaderboard
        ]);

        // Bagian Export Excel
        if ($request->query('export') === 'xlsx') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Siswa');
            $sheet->setCellValue('C1', 'Nilai Kelompok');
            $sheet->setCellValue('D1', 'SCI');
            $sheet->setCellValue('E1', 'Nilai Akhir');
            $sheet->setCellValue('F1', 'Badge'); // Tambahan kolom excel

            $row = 2;
            foreach ($studentRows as $index => $stu) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValueExplicit('B' . $row, $stu['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->setCellValue('C' . $row, $stu['nilai_kelompok'] ?? '-');
                $sheet->setCellValue('D' . $row, $stu['sci'] ?? '-');

                if (is_numeric($stu['nilai'])) {
                    $sheet->setCellValue('E' . $row, (float) $stu['nilai']);
                } else {
                    $sheet->setCellValueExplicit('E' . $row, $stu['nilai'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }

                // Bersihkan tag HTML agar di excel hanya tampil teks namanya saja
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

            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );

            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }

        // =========================================================================
        // TAMBAHAN: Buat Leaderboard & Data Rekap Kelompok (Mode 2)
        // =========================================================================
        $leaderboard = collect();

        if ($isGroupActivity) {
            // Ambil nama-nama kelompok dari database
            $activityGroups = DB::table('activity_groups')
                ->where('id_activity', $activity->id)
                ->get()
                ->keyBy('id');

            // Kelompokkan data siswa ($studentRows) berdasarkan id_group
            $groupedStudents = collect($studentRows)->groupBy(function ($item) use ($groupMembers) {
                return $groupMembers[$item['id']] ?? 'Tanpa Kelompok';
            });

            foreach ($groupedStudents as $groupId => $members) {
                if ($groupId === 'Tanpa Kelompok') continue;

                $groupName = $activityGroups->has($groupId) ? $activityGroups->get($groupId)->name : 'Kelompok ' . $groupId;

                // Ambil nilai kelompok dari member pertama (karena semua member di kelompok yang sama nilai kelompoknya sama)
                $nilaiKel = $members->first()['nilai_kelompok'];

                $leaderboard->push([
                    'id_group' => $groupId,
                    'name' => $groupName,
                    'nilai_kelompok' => (float) $nilaiKel,
                    'members' => $members->sortByDesc('sci')->values() // Urutkan anggota dari SCI tertinggi (The Carry di atas)
                ]);
            }

            // Urutkan kelompok dari nilai tertinggi ke terendah (Peringkat 1 di paling atas)
            $leaderboard = $leaderboard->sortByDesc('nilai_kelompok')->values();
        }

        // =========================================================================
        // VIEW RETURN
        // =========================================================================
        return view('guru.detailnilaisiswa', [
            'activity' => $activity,
            'class_id' => $classId,
            'students' => $studentRows,
            'leaderboard' => $leaderboard // <--- Tambahkan variabel ini ke view
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

        return view('guru.koreksi-mode2', [
            'activity' => $activity,
            'student' => $student,
            'result' => $result,
            'answers' => $answers
        ]);
    }
}
