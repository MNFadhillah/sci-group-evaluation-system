<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoalController extends Controller
{
  public function showGenerator()
{
    $guruId = Auth::id();

    $topics = DB::table('topics')
        ->join('subject', 'topics.id_subject', '=', 'subject.id')
        ->join('classes', 'subject.id_class', '=', 'classes.id')
        ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
        ->where('teacher_classes.id_teacher', $guruId)
        ->where('topics.created_by', $guruId)
        ->select('topics.id', 'topics.title', 'classes.level as jenjang')
        ->orderBy('topics.title')
        ->get();

    $jenjangList = DB::table('classes')
        ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
        ->where('teacher_classes.id_teacher', $guruId)
        ->select('classes.level')
        ->distinct()
        ->orderBy('classes.level')
        ->pluck('classes.level');

    $subjects = DB::table('subject')
        ->join('classes', 'subject.id_class', '=', 'classes.id')
        ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
        ->where('teacher_classes.id_teacher', $guruId)
        ->select('subject.id', 'subject.name')
        ->distinct()
        ->orderBy('subject.name')
        ->get();

    return view('guru.generateSoal', compact('topics', 'jenjangList', 'subjects'));
}

  public function generateAI(Request $request)
{
    $guruId = Auth::id();

    $request->validate([
        'topic' => 'nullable|integer|exists:topics,id',
        'jenjang' => 'required|string',
        'jumlah' => 'required|integer|min:3|max:30',
        'new_topic_title' => 'nullable|string|max:255',
        'new_topic_subject' => 'nullable|exists:subject,id',
    ]);

    // ===== Tentukan topik: yang sudah ada, atau buat baru =====
    if ($request->filled('topic')) {
        $topic = Topic::findOrFail($request->topic);
    } elseif ($request->filled('new_topic_title')) {
        if (!$request->filled('new_topic_subject')) {
            return back()->withErrors(['new_topic_subject' => 'Pilih Mata Pelajaran untuk topik baru.'])->withInput();
        }

        $subject = DB::table('subject')->where('id', $request->new_topic_subject)->first();
        if (!$subject) {
            return back()->withErrors(['new_topic_subject' => 'Mata pelajaran tidak ditemukan.'])->withInput();
        }

        $teaches = DB::table('teacher_classes')
            ->where('id_teacher', $guruId)
            ->where('id_class', $subject->id_class)
            ->exists();

        if (!$teaches) {
            return back()->withErrors(['new_topic_subject' => 'Anda tidak berwenang membuat topik pada mata pelajaran ini.'])->withInput();
        }

        $topic = Topic::firstOrCreate(
            [
                'title' => trim($request->new_topic_title),
                'id_subject' => $request->new_topic_subject,
            ],
            [
                'created_by' => $guruId,
            ]
        );
    } else {
        return back()->withErrors(['topic' => 'Topik wajib dipilih atau dibuat.'])->withInput();
    }

    $topics = Topic::where('created_by', $guruId)->orderBy('title', 'asc')->get();

    $subjects = DB::table('subject')
        ->join('classes', 'subject.id_class', '=', 'classes.id')
        ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
        ->where('teacher_classes.id_teacher', $guruId)
        ->select('subject.id', 'subject.name')
        ->distinct()
        ->orderBy('subject.name')
        ->get();

    $jenjangList = [$request->jenjang];
    $selectedJenjang = $request->jenjang;
    $jumlah = (int) $request->jumlah;

    // prompt sesuai instruksi kamu
    $prompt = <<<PROMPT
Tolong buatkan soal dan jawaban untuk topik {$topic->title} jenjang {$selectedJenjang} dengan catatan:
- format JSON
- Total ada {$jumlah} soal setiap tingkat kesulitan mudah, sedang, dan sulit., 
- format pertanyaan terdiri dari URL (gambar) dan teks. Pertanyaan tanpa gambar dapat mengisi URL dengan null.
- Pertahankan id_topic sesuai dengan ID topik yang diberikan: {$topic->id}
- MC_option (multiple choice option) format: [{\"a\": {url, teks}, ..., \"e\": {url, teks}}]. Pilihan ganda memiliki 5 opsi (a sampai e)
- SA_option (shortanswer option) berisi 3 pilihan jawaban isian singkat dengan format [jawaban1, jawaban2, jawaban3].
- Selain pilihan ganda dan isian singkat, buat juga beberapa soal Essay (uraian).
- Soal Essay TIDAK memerlukan kunci jawaban (SA_option dikosongkan/tidak perlu disertakan), karena akan diperiksa manual oleh guru.
- Distribusikan jenis soal secara merata: campuran MultipleChoice, ShortAnswer, dan Essay di setiap tingkat kesulitan.

ikuti contoh dibawah ini :

[
  {
    "id_topic":"{$topic->id}",
    "difficulty": "mudah",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Diagram ini menunjukkan komponen-komponen dasar sistem operasi. Komponen inti (core) yang berada di pusat dan bertugas mengelola sulitware secara langsung disebut...",
      "url": "https://i.imgur.com/eYf0k7w.png"
    },
    "MC_option": [
      {"a": {"teks": "Kernel", "url": null}},
      {"b": {"teks": "GUI (Graphical User Interface)", "url": null}},
      {"c": {"teks": "Shell", "url": null}},
      {"d": {"teks": "API (Application Programming Interface)", "url": null}},
      {"e": {"teks": "Driver Perangkat", "url": null}}
    ],
    "MC_Answer": "a"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "mudah",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Dalam terminologi sistem operasi, apa perbedaan fundamental antara 'Program' dan 'Proses'?",
      "url": null
    },
    "MC_option": [
      {"a": {"teks": "Program adalah perangkat lunak, Proses adalah perangkat keras.", "url": null}},
      {"b": {"teks": "Program adalah file di disk, Proses adalah program yang sedang dieksekusi di memori.", "url": null}},
      {"c": {"teks": "Program ditulis dalam bahasa tingkat tinggi, Proses dalam bahasa mesin.", "url": null}},
      {"d": {"teks": "Program memiliki banyak proses, tetapi proses hanya memiliki satu program.", "url": null}},
      {"e": {"teks": "Tidak ada perbedaan, keduanya adalah istilah yang sinonim.", "url": null}}
    ],
    "MC_Answer": "b"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "mudah",
    "type": "ShortAnswer",
    "pertanyaan": {
      "text": "Gambar ini menunjukkan antarmuka pengguna yang umum. Apa nama mode interaksi di mana pengguna mengetikkan perintah teks alih-alih mengklik ikon?",
      "url": "https://i.imgur.com/1aW9oXp.png"
    },
    "SA_option": ["CLI", "Command Line Interface", "Terminal"]
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sedang",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Diagram ini menunjukkan transisi state sebuah proses. Transisi dari 'Running' ke 'Waiting' (atau 'Blocked') biasanya terjadi ketika sebuah proses...",
      "url": "https://i.imgur.com/GzB1vNq.png"
    },
    "MC_option": [
      {"a": {"teks": "Selesai dieksekusi.", "url": null}},
      {"b": {"teks": "Meminta operasi I/O (misalnya, membaca file).", "url": null}},
      {"c": {"teks": "Dipilih oleh CPU scheduler untuk berjalan.", "url": null}},
      {"d": {"teks": "Waktu 'quantum'-nya habis (pada Round Robin).", "url": null}},
      {"e": {"teks": "Membuat proses anak (child process) baru.", "url": null}}
    ],
    "MC_Answer": "b"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sedang",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Manakah di antara algoritma penjadwalan CPU berikut yang bersifat 'non-preemptive', yang berarti sekali proses mendapatkan CPU, proses tersebut akan berjalan sampai selesai atau sampai ia melepaskannya secara sukarela (misal, untuk I/O)?",
      "url": null
    },
    "MC_option": [
      {"a": {"teks": "Round Robin (RR)", "url": null}},
      {"b": {"teks": "Shortest Remaining Time First (SRTF)", "url": null}},
      {"c": {"teks": "First-Come, First-Served (FCFS)", "url": null}},
      {"d": {"teks": "Multilevel Feedback Queue", "url": null}},
      {"e": {"teks": "Priority Scheduling (Preemptive version)", "url": null}}
    ],
    "MC_Answer": "c"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sedang",
    "type": "ShortAnswer",
    "pertanyaan": {
      "text": "Dalam manajemen memori virtual, apa istilah untuk kondisi di mana sistem menghabiskan sebagian besar waktunya untuk memindahkan halaman (pages) antara RAM dan disk (swapping) sehingga kinerja sistem menurun drastis?",
      "url": null
    },
    "SA_option": ["Thrashing", "thrashing", "Trashing"]
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sulit",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Perhatikan Resource Allocation Graph (RAG) pada gambar. Panah dari proses ke resource berarti 'request', dan panah dari resource ke proses berarti 'held'. Kondisi apa yang paling tepat digambarkan oleh graf ini?",
      "url": "https://i.imgur.com/8pZqjR1.png"
    },
    "MC_option": [
      {"a": {"teks": "Sistem dalam 'Safe State' (Aman).", "url": null}},
      {"b": {"teks": "Terjadi 'Starvation' pada P1.", "url": null}},
      {"c": {"teks": "Terjadi 'Deadlock' yang melibatkan P1 dan P2.", "url": null}},
      {"d": {"teks": "Ini adalah contoh 'Race Condition'.", "url": null}},
      {"e": {"teks": "Semua proses akan selesai tanpa masalah.", "url": null}}
    ],
    "MC_Answer": "c"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sulit",
    "type": "MultipleChoice",
    "pertanyaan": {
      "text": "Dalam konteks sinkronisasi proses, sebuah 'Semaphore' biner (juga dikenal sebagai 'mutex') memiliki dua operasi atomik: wait(S) dan signal(S). Operasi wait(S) (atau P(S)) berfungsi untuk...",
      "url": null
    },
    "MC_option": [
      {"a": {"teks": "Menambah nilai S dan membangunkan satu proses yang menunggu (jika ada).", "url": null}},
      {"b": {"teks": "Selalu membuat proses pemanggil tidur (block) selama S detik.", "url": null}},
      {"c": {"teks": "Memeriksa S. Jika S > 0, menguranginya (S--). Jika S <= 0, proses pemanggil menunggu (block).", "url": null}},
      {"d": {"teks": "Memeriksa S. Jika S > 0, proses pemanggil menunggu (block). Jika S <= 0, melanjutkannya.", "url": null}},
      {"e": {"teks": "Mereset nilai S kembali ke 1, tidak peduli kondisi sebelumnya.", "url": null}}
    ],
    "MC_Answer": "c"
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sulit",
    "type": "ShortAnswer",
    "pertanyaan": {
      "text": "Diagram Gantt ini menunjukkan eksekusi tiga proses (P1, P2, P3) menggunakan algoritma Round Robin (RR). Berdasarkan pola eksekusi yang terlihat (P1, P2, P3, lalu P1 lagi, dst.), berapa 'time quantum' yang digunakan?",
      "url": "https://i.imgur.com/kRjA7zG.png"
    },
    "SA_option": ["4", "4 unit", "4ms"]
  },
    

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sedang",
    "type": "Essay",
    "pertanyaan": {
      "text": "Jelaskan perbedaan antara proses (process) dan thread dalam sistem operasi, serta berikan satu contoh kasus penggunaan multithreading.",
      "url": null
    }
  },

  {
    "id_topic":"{$topic->id}",
    "difficulty": "sulit",
    "type": "Essay",
    "pertanyaan": {
      "text": "Sebuah sistem memiliki 3 proses yang saling meminta resource sehingga terjadi deadlock. Jelaskan dua strategi (selain pencegahan) yang dapat digunakan sistem operasi untuk menangani situasi ini, lengkap dengan mekanismenya.",
      "url": null
    }
  }
]

PROMPT;


    return view('guru.generateSoal', [
        'topics' => $topics,
        'subjects' => $subjects,
        'prompt' => $prompt,
        'selectedTopic' => $topic->id,
        'selectedJenjang' => $selectedJenjang,
        'jenjangList' => $jenjangList,
        'jumlahInput' => $jumlah,
    ]);

  }
  public function importQuestionJson(Request $request)
  {
    // Pastikan mode upload dipilih
    $mode = $request->upload_mode;
    $json = null;

    if ($mode === "paste") {

      $request->validate([
        'json_text' => 'required'
      ]);

      $json = json_decode($request->json_text, true);

    } elseif ($mode === "file") {

      $request->validate([
        'file' => 'required|file|mimes:json,txt'
      ]);

      $json = json_decode(file_get_contents($request->file('file')), true);

    } else {
      return back()->with('error', 'Metode import tidak dikenali.');
    }

    // Validasi JSON
    if (!is_array($json)) {
      return back()->with('error', 'Format JSON tidak valid.');
    }

    // ===== SIMPAN DATA KE DATABASE =====
    $importedCount = 0;

    foreach ($json as $item) {

      // Validasi minimal per item
      if (
        !isset($item['id_topic']) ||
        !isset($item['type']) ||
        !isset($item['difficulty']) ||
        !isset($item['pertanyaan'])
      ) {
        continue; // lewati item yang rusak
      }

      DB::table('question')->insert([
        'id_topic' => $item['id_topic'],
        'type' => $item['type'],
        'difficulty' => strtolower($item['difficulty']),
        'question' => json_encode($item['pertanyaan']),
        'MC_option' => isset($item['MC_option']) ? json_encode($item['MC_option']) : null,
        'SA_answer' => isset($item['SA_option']) ? json_encode($item['SA_option']) : null,
        'MC_answer' => $item['MC_Answer'] ?? null,
        'created_by' => Auth::id(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      $importedCount++;
    }

    // Jika tidak ada yang berhasil diimport
    if ($importedCount === 0) {
      return back()->with('error', 'Tidak ada soal yang berhasil diimpor. Periksa struktur JSON.');
    }

    return back()->with([
      'success' => 'Soal berhasil diimpor ke database',
      'imported_count' => $importedCount
    ]);
  }




}
