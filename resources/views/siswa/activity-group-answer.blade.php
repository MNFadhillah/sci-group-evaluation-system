<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengerjaan Aktivitas</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome (Untuk Ikon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* =======================================
           GLOBAL & NAVBAR STANDALONE
           ======================================= */
        body {
            background-color: #f4f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .top-navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .btn-back {
            color: #4e73df;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s ease;
        }

        .btn-back:hover {
            color: #2e59d9;
        }

        /* =======================================
           CUSTOM UI/UX DASHBOARD STYLE (Asli)
           ======================================= */
        .quiz-container {
            width: 100%;
            margin: 0 auto;
        }

        /* Styling Pilihan Ganda (Mode 2) */
        .quiz-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            background-color: #ffffff;
        }

        .quiz-option:hover {
            border-color: #b6d4fe;
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }

        .quiz-option.selected {
            border-color: #0d6efd;
            background-color: #f0f7ff;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
        }

        .quiz-option .option-letter {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
            margin-right: 12px;
            transition: all 0.2s;
        }

        .quiz-option.selected .option-letter {
            background-color: #0d6efd;
            color: white;
        }

        /* Styling Grid Navigasi (Palet) */
        .nav-palette {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
            gap: 10px;
            max-height: 250px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .num-btn {
            aspect-ratio: 1;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-weight: bold;
            background: white;
            color: #495057;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .num-btn:hover {
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .num-btn.answered {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .num-btn.current {
            border-color: #0dcaf0;
            box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.25);
            color: #0dcaf0;
        }

        .num-btn.flagged {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        /* Styling Jawaban Teman (Mode 1) */
        .peer-answer-card {
            border-left: 4px solid #0dcaf0;
            background-color: #f8f9fa;
            border-radius: 0 12px 12px 0;
        }

        .top-navbar,
        nav.navbar {
            padding: 10px 24px !important;
        }

        /* Instruction Page baru */
        .instruction-icon-badge {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .instruction-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .instruction-item:last-child {
            border-bottom: none;
        }

        .instruction-mode-banner {
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            margin-bottom: 28px;
        }

        .instruction-mode-banner.mode-individu {
            background: linear-gradient(135deg, #eaf1ff, #f5f9ff);
            color: #0d6efd;
            border: 1px solid #cfe2ff;
        }

        .instruction-mode-banner.mode-kelompok {
            background: linear-gradient(135deg, #e6fbff, #f3feff);
            color: #087990;
            border: 1px solid #b6effa;
        }
    </style>
</head>

<body>
    <!-- =======================================
         NAVBAR MANDIRI (HEADER KUIS)
         ======================================= -->
    <nav class="navbar sticky-top bg-white shadow-sm py-3 mb-4 border-bottom z-3">
        <div class="container-fluid px-4 px-xl-5">
            <!-- Menggunakan Grid System agar lebar kiri, tengah, kanan sama rata. Timer pasti presisi di tengah layar -->
            <div class="row w-100 align-items-center m-0">

                <!-- KIRI: Judul & Info Kelompok (Porsi 1/3 Kiri) -->
                <div class="col-12 col-lg-4 d-flex flex-column justify-content-center mb-3 mb-lg-0 px-0">
                    <h4 class="fw-bolder text-dark mb-1 text-center text-lg-start">{{ $activity->title }}</h4>
                    <div
                        class="text-muted small d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                        <span>
                            <i class="fas fa-users text-primary me-1"></i> Kelompok: <strong
                                class="text-dark">{{ $group->name }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-list-ol text-primary me-1"></i> Total: <strong
                                class="text-dark">{{ $questions->count() }} Soal</strong>
                        </span>
                    </div>
                </div>

                <!-- TENGAH: UI Timer (Porsi 1/3 Tengah) -->
                <div class="col-12 col-lg-4 d-flex justify-content-center mb-3 mb-lg-0 px-0">
                    <div id="timerContainer"
                        class="fw-bold text-danger d-none bg-white border border-danger px-4 py-2 rounded-pill shadow-sm"
                        style="font-size: 1.1rem; letter-spacing: 1px;">
                        <i class="fas fa-stopwatch me-1"></i> <span id="countdownTimer">00:00:00</span>
                    </div>
                </div>

                <!-- KANAN: Badge Mode (Porsi 1/3 Kanan) -->
                <div class="col-12 col-lg-4 d-flex justify-content-center justify-content-lg-end px-0">
                    @if ($isMode2)
                        <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.9rem;">
                            <i class="fas fa-user-edit me-1"></i> Mode Kuis Individu
                        </span>
                    @else
                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm"
                            style="font-size: 0.9rem;">
                            <i class="fas fa-users-cog me-1"></i> Mode Tugas Kelompok
                        </span>
                    @endif
                </div>

            </div>
        </div>
    </nav>

    <!-- =======================================
         KONTEN UTAMA
         ======================================= -->
    <div class="container-fluid py-2 quiz-container px-4 px-xl-5">

        <!-- HALAMAN PETUNJUK -->
        <div id="instructionPage" class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
                        style="width:64px;height:64px;">
                        <i class="fas fa-clipboard-list text-primary" style="font-size:1.6rem;"></i>
                    </div>
                    <h3 class="fw-bolder text-dark mb-1">Petunjuk Pengerjaan</h3>
                    <p class="text-muted mb-0">Baca dulu sebelum mulai, biar jawabanmu tersimpan dengan aman.</p>
                </div>

                <div class="mx-auto" style="max-width: 720px;">

                    @if ($isMode2)
                        <div class="instruction-mode-banner mode-individu">
                            <i class="fas fa-user-edit fs-4"></i>
                            <div>
                                <div>Mode Kuis Individu</div>
                                <div class="fw-normal small opacity-75">Paket soal ini milik kamu sendiri, nilai
                                    digabung jadi rata-rata kelompok.</div>
                            </div>
                        </div>
                    @else
                        <div class="instruction-mode-banner mode-kelompok">
                            <i class="fas fa-users-cog fs-4"></i>
                            <div>
                                <div>Mode Tugas Kelompok</div>
                                <div class="fw-normal small opacity-75">Kamu bisa lihat progres jawaban teman secara
                                    real-time.</div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-light p-4 rounded-4 mb-4">

                        <div class="instruction-item">
                            <div class="instruction-icon-badge bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="text-dark">
                                Aktivitas ini terdiri dari <strong>{{ $questions->count() }} butir soal</strong>.
                            </div>
                        </div>

                        @if ($isMode2)
                            <div class="instruction-item">
                                <div class="instruction-icon-badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="text-dark">
                                    Nilai akan digabung menjadi <strong>rata-rata kelompok</strong>.
                                </div>
                            </div>
                        @else
                            <div class="instruction-item">
                                <div class="instruction-icon-badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="text-dark">
                                    Progres jawaban anggota lain tampil <strong>real-time</strong> di bawah kolom
                                    jawabanmu.
                                </div>
                            </div>
                        @endif

                        <div class="instruction-item">
                            <div class="instruction-icon-badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-save"></i>
                            </div>
                            <div class="text-dark">
                                Setiap jawaban otomatis <strong>tersimpan sementara</strong> (Draft).
                            </div>
                        </div>

                        <div class="instruction-item">
                            <div class="instruction-icon-badge bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="text-dark">
                                Tekan <strong>Kumpulkan Jawaban</strong> di akhir untuk lanjut ke tahap Penilaian Teman
                                (SCI).
                            </div>
                        </div>

                    </div>
                </div>

                <div class="text-center">
                    <button id="startBtn" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold rounded-pill">
                        <i class="fas fa-play me-2"></i> Mulai Mengerjakan
                    </button>
                </div>
            </div>
        </div>

        <!-- HALAMAN KUIS UTAMA -->
        <div id="quizPage" class="d-none">
            <div class="row g-4">

                <!-- SISI KIRI: LEMBAR SOAL -->
                <div class="{{ $isMode2 ? 'col-lg-8' : 'col-lg-12' }}">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <!-- Header Soal -->
                        <div
                            class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-primary mb-0">Soal <span id="qIndex">1</span></h5>
                            @if ($isMode2)
                                <div>
                                    <button id="flagBtn"
                                        class="btn btn-sm btn-outline-warning rounded-pill fw-bold text-dark shadow-sm px-3">
                                        <i class="fas fa-flag"></i> Tandai Ragu
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Area Konten Soal -->
                        <div class="card-body p-4 d-flex flex-column">
                            <div id="questionArea" class="flex-grow-1">
                                <!-- Injeksi JS ada di sini -->
                            </div>
                        </div>

                        <!-- Footer Soal (Tombol Prev/Next & Submit Mode 1) -->
                        <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4 rounded-bottom-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                @if ($isMode2)
                                    <div>
                                        <button id="prevBtn"
                                            class="btn btn-outline-secondary px-4 fw-bold rounded-pill" disabled>
                                            <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                                        </button>
                                        <button id="nextBtn"
                                            class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                                            Berikutnya <i class="fas fa-chevron-right ms-1"></i>
                                        </button>
                                    </div>
                                @else
                                    <div></div>
                                @endif

                                @if (!$isMode2)
                                    <div>
                                        <button id="finishBtnMode1"
                                            class="btn btn-success px-4 fw-bold rounded-pill shadow-sm">
                                            <i class="fas fa-paper-plane me-1"></i> Kumpulkan Jawaban
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SISI KANAN: NAVIGASI PALET (HANYA MUNCUL DI MODE 2) -->
                @if ($isMode2)
                    <div class="col-lg-4">
                        <div class="position-sticky" style="top: 100px;">
                            <!-- Disesuaikan agar tidak tertutup navbar -->
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4">
                                    <h6 class="fw-bold text-dark mb-0 text-center">Navigasi Soal</h6>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Legenda Palet -->
                                    <div
                                        class="d-flex flex-wrap justify-content-center gap-3 mb-4 small fw-semibold text-secondary">
                                        <div class="d-flex align-items-center"><span class="rounded-circle border"
                                                style="width:12px;height:12px;background:#fff;margin-right:6px;"></span>
                                            Belum</div>
                                        <div class="d-flex align-items-center"><span class="rounded-circle"
                                                style="width:12px;height:12px;background:#0d6efd;margin-right:6px;"></span>
                                            Dijawab</div>
                                        <div class="d-flex align-items-center"><span class="rounded-circle"
                                                style="width:12px;height:12px;background:#ffc107;margin-right:6px;"></span>
                                            Ragu</div>
                                    </div>

                                    <!-- Grid Palet -->
                                    <div id="palette" class="nav-palette mb-4"></div>

                                    <hr class="text-muted opacity-25 my-4">

                                    <!-- Tombol Kumpul Mode 2 -->
                                    <button id="finishBtnMode2"
                                        class="btn btn-success btn-lg w-100 fw-bold shadow-sm rounded-pill">
                                        <i class="fas fa-paper-plane me-2"></i> Kumpulkan Jawaban
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- =======================================
         DATA BRIDGE (PHP TO JAVASCRIPT)
         ======================================= -->
    <div id="quizConfig" class="d-none" data-ismode2="{{ $isMode2 ? 'true' : 'false' }}"
        data-userid="{{ $user->id }}" data-activityid="{{ $activity->id }}"
        data-duration="{{ $activity->durasi_pengerjaan ?? 0 }}"
        data-submiturl="{{ route('activity.group.answer.save', $activity->id) }}"
        data-csrftoken="{{ csrf_token() }}">
    </div>


    <!-- =======================================
         LIBRARY TAMBAHAN & SCRIPTS
         ======================================= -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div id="quizDataBridge" class="d-none" data-questions="{{ base64_encode(json_encode($questions ?? [])) }}"
        data-answers="{{ base64_encode(json_encode($answers ?? [])) }}"
        data-members="{{ base64_encode(json_encode($members ?? [])) }}">
    </div>

    <script>
        /* =======================================
                           1. DEKLARASI VARIABEL DARI SERVER
                           ======================================= */
        const configEl = document.getElementById('quizConfig');

        const IS_MODE_2 = configEl.dataset.ismode2 === 'true';
        const MY_USER_ID = parseInt(configEl.dataset.userid);
        const ACTIVITY_ID = parseInt(configEl.dataset.activityid);
        const DURATION_MINUTES = parseInt(configEl.dataset.duration);
        const SUBMIT_URL = configEl.dataset.submiturl;
        const CSRF_TOKEN = configEl.dataset.csrftoken;

        const dataBridge = document.getElementById('quizDataBridge');
        const rawQuestions = JSON.parse(atob(dataBridge.dataset.questions));
        const existingAnswers = JSON.parse(atob(dataBridge.dataset.answers));
        const groupMembers = JSON.parse(atob(dataBridge.dataset.members));

        let timerInterval;

        // PINDAH KE SINI — supaya sudah siap sebelum auto-resume (startBtn.click()) mungkin terpanggil
        const prevBtnEl = document.getElementById('prevBtn');
        const nextBtnEl = document.getElementById('nextBtn');
        const flagBtnEl = document.getElementById('flagBtn');

        function updateFlagButtonUI() {
            if (!flagBtnEl) return;
            flagBtnEl.classList.toggle('btn-warning', !!flagged[idx]);
            flagBtnEl.classList.toggle('btn-outline-warning', !flagged[idx]);
        }

        if (flagBtnEl) {
            flagBtnEl.onclick = () => {
                flagged[idx] = !flagged[idx];
                updatePalette();
                updateFlagButtonUI();
            };
        }


        // =======================================
        // 2. PARSING DATA SUPER AMAN (ANTI CRASH)
        // =======================================
        function safeJSONParse(data, fallback) {
            if (!data) return fallback;
            if (typeof data !== 'string') return data;
            try {
                return JSON.parse(data);
            } catch (e) {
                console.warn("Ditemukan JSON cacat di Database, menggunakan fallback.");
                return fallback;
            }
        }

        let questions = rawQuestions.map((q, index) => {
            let parsedText = safeJSONParse(q.question, {
                text: q.question || 'Teks soal tidak terbaca',
                url: null
            });
            let parsedOpts = safeJSONParse(q.MC_option, null);
            let finalOptions = null;

            if (parsedOpts) {
                finalOptions = {};
                try {
                    if (Array.isArray(parsedOpts)) {
                        parsedOpts.forEach((optObj, idx) => {
                            if (typeof optObj === 'object' && optObj !== null) {
                                let letter = Object.keys(optObj)[0];
                                let content = optObj[letter];
                                if (typeof content === 'string') {
                                    finalOptions[letter] = {
                                        text: content,
                                        url: null
                                    };
                                } else {
                                    finalOptions[letter] = {
                                        text: content.teks || content.text || '',
                                        url: content.url || null
                                    };
                                }
                            } else {
                                let letter = String.fromCharCode(97 + idx);
                                finalOptions[letter] = {
                                    text: String(optObj),
                                    url: null
                                };
                            }
                        });
                    } else {
                        Object.entries(parsedOpts).forEach(([letter, content]) => {
                            if (typeof content === 'string') {
                                finalOptions[letter] = {
                                    text: content,
                                    url: null
                                };
                            } else {
                                finalOptions[letter] = {
                                    text: content.teks || content.text || '',
                                    url: content.url || null
                                };
                            }
                        });
                    }
                } catch (err) {
                    console.error("Format opsi kacau pada soal:", q.id);
                }
            }

            let finalSoalText = typeof parsedText === 'string' ? parsedText : (parsedText.text || parsedText.teks ||
                '');

            return {
                id: q.id,
                type: q.type,
                text: finalSoalText,
                image: parsedText.url || parsedText.image || '',
                options: finalOptions
            };
        });

        let answers = new Array(questions.length).fill(null);
        let flagged = new Array(questions.length).fill(false);
        let idx = 0;

        questions.forEach((q, i) => {
            let key = q.id + '_' + MY_USER_ID;
            if (existingAnswers[key]) answers[i] = existingAnswers[key].answer;
        });

        /* =======================================
           3. FUNGSI TIMER (HITUNG MUNDUR)
           ======================================= */
        function startTimer() {
            // Jika durasi 0 atau tidak diatur, abaikan timer
            if (!DURATION_MINUTES || DURATION_MINUTES <= 0) return;

            const timerKey = `quiz_endtime_${MY_USER_ID}_${ACTIVITY_ID}`;
            let endTime = sessionStorage.getItem(timerKey);

            // Jika belum ada batas waktu di session (baru pertama kali klik mulai)
            if (!endTime) {
                endTime = new Date().getTime() + (DURATION_MINUTES * 60 * 1000);
                sessionStorage.setItem(timerKey, endTime);
            }

            // Tampilkan UI Timer
            document.getElementById('timerContainer').classList.remove('d-none');

            timerInterval = setInterval(() => {
                let now = new Date().getTime();
                let distance = endTime - now;

                if (distance <= 0) {
                    // Waktu Habis!
                    clearInterval(timerInterval);
                    document.getElementById('countdownTimer').innerHTML = "WAKTU HABIS!";
                    sessionStorage.removeItem(timerKey);

                    Swal.fire({
                        title: 'Waktu Habis!',
                        text: 'Jawaban Anda akan dikumpulkan secara otomatis.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        submitQuizAction(true); // true = auto submit
                    });
                } else {
                    // Kalkulasi jam, menit, detik
                    let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Format angka agar selalu 2 digit (misal 09:05)
                    let m = minutes < 10 ? "0" + minutes : minutes;
                    let s = seconds < 10 ? "0" + seconds : seconds;

                    let timeString = (hours > 0 ? hours + ":" : "") + m + ":" + s;
                    document.getElementById('countdownTimer').innerHTML = timeString;
                }
            }, 1000);
        }

        /* =======================================
           4. KONTROL TAMPILAN (UI LOGIC)
           ======================================= */
        document.getElementById('startBtn').onclick = () => {
            document.getElementById('instructionPage').classList.add('d-none');
            document.getElementById('quizPage').classList.remove('d-none');
            renderQuestion(0);
            updatePalette();
            startTimer(); // Mulai timer saat tombol Mulai ditekan
        };

        // Otomatis langsung masuk jika user reload halaman dan waktu masih berjalan di session
        if (sessionStorage.getItem(`quiz_endtime_${MY_USER_ID}_${ACTIVITY_ID}`)) {
            document.getElementById('startBtn').click();
        }

        function renderQuestion(i) {
            if (!questions || questions.length === 0 || !questions[i]) {
                document.getElementById('questionArea').innerHTML =
                    '<div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>Gagal memuat soal. Pastikan data soal tersedia atau silakan refresh halaman.</div>';
                return;
            }

            idx = i;
            document.getElementById('qIndex').textContent = i + 1;
            const q = questions[i];
            const area = document.getElementById('questionArea');

            let html = ``;
            if (q.image) {
                html +=
                    `<div class="text-center mb-4"><img src="${q.image}" class="img-fluid rounded-3 shadow-sm border" style="max-height: 280px;"></div>`;
            }
            if (q.text) {
                html += `<div class="mb-4 fs-5 text-dark" style="line-height: 1.7;">${q.text.replace(/\n/g, '<br>')}</div>`;
            }

            html += `<div class="mt-4 pt-2 border-top">${renderOptions(q)}</div>`;
            area.innerHTML = html;

            if (IS_MODE_2 && q.type === 'MultipleChoice') {
                document.querySelectorAll('.quiz-option').forEach(item => {
                    item.onclick = () => {
                        answers[idx] = item.dataset.key;
                        renderQuestion(idx);
                        updatePalette();
                    };
                });
            }



            if (prevBtnEl) prevBtnEl.disabled = i === 0;
            if (nextBtnEl) nextBtnEl.disabled = i === questions.length - 1;
            updateFlagButtonUI();
        }

        function renderOptions(q) {
            let html = '';

            if (IS_MODE_2) {
                if (q.type === 'MultipleChoice') {
                    html += `<div class="row g-3">`;
                    Object.entries(q.options || {}).forEach(([key, opt]) => {
                        let isSelected = answers[idx] === key;
                        let className = isSelected ? 'quiz-option p-2 px-3 h-100 selected shadow-sm' :
                            'quiz-option p-2 px-3 h-100';
                        html += `
                            <div class="col-md-6">
                                <div class="${className}" data-key="${key}">
                                    <div class="d-flex align-items-center h-100">
                                        <span class="option-letter mb-0 flex-shrink-0">${key}</span>
                                        <span class="fs-6 text-dark">${opt.text}</span>
                                    </div>
                                </div>
                            </div>`;
                    });
                    html += `</div>`;
                } else {
                    html += `
                        <label class="form-label fw-bold text-primary mb-2"><i class="fas fa-pen-alt me-1"></i> Jawaban Singkat Anda:</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 shadow-sm" placeholder="Ketik jawaban di sini..." 
                        value="${answers[idx] || ''}" oninput="answers[idx] = this.value; updatePalette();">
                    `;
                }
            } else {
                html += `
                    <div class="mb-5">
                        <label class="form-label fw-bold text-primary mb-2"><i class="fas fa-edit me-1"></i> Area Jawaban Anda:</label>
                        <textarea class="form-control bg-primary bg-opacity-10 border-primary border-opacity-25 shadow-sm" rows="4" placeholder="Tuliskan analisis atau pemikiran Anda di sini..." 
                        oninput="answers[idx] = this.value; updatePalette();">${answers[idx] || ''}</textarea>
                    </div>
                `;

                html +=
                    `<h6 class="fw-bold text-secondary mb-3"><i class="fas fa-users me-1"></i> Progres Jawaban Anggota Lain:</h6>`;
                let hasPeers = false;

                groupMembers.forEach(member => {
                    if (member.id_user !== MY_USER_ID) {
                        hasPeers = true;
                        let key = q.id + '_' + member.id_user;
                        let peerAns = existingAnswers[key] ? existingAnswers[key].answer :
                            '<span class="text-muted fst-italic">Belum menuliskan jawaban...</span>';
                        let name = member.user ? member.user.name : 'Anggota';

                        html += `
                        <div class="peer-answer-card p-3 mb-3 shadow-sm">
                            <div class="fw-bold text-info mb-2 small text-uppercase"><i class="fas fa-user-circle me-1"></i> ${name}</div>
                            <div class="text-dark fs-6" style="line-height:1.6;">${peerAns}</div>
                        </div>`;
                    }
                });

                if (!hasPeers) {
                    html += `
                    <div class="alert alert-light border-0 text-muted small shadow-sm">
                        <i class="fas fa-info-circle me-1"></i> Anda tidak memiliki rekan lain dalam kelompok ini.
                    </div>`;
                }
            }
            return html;
        }

        function updatePalette() {
            const palette = document.getElementById('palette');
            if (!palette) return; // Mode 1 tidak punya palet

            palette.innerHTML = '';
            questions.forEach((_, i) => {
                const btn = document.createElement('button');
                let className = 'num-btn shadow-sm';

                if (i === idx) className += ' current';
                if (answers[i] !== null && String(answers[i]).trim() !== '') className += ' answered';
                if (flagged[i]) className += ' flagged';

                btn.className = className;
                btn.textContent = i + 1;
                btn.onclick = () => renderQuestion(i);
                palette.appendChild(btn);
            });
        }



        if (prevBtnEl) prevBtnEl.onclick = () => {
            idx > 0 && renderQuestion(idx - 1);
            updatePalette();
        };
        if (nextBtnEl) nextBtnEl.onclick = () => {
            idx < questions.length - 1 && renderQuestion(idx + 1);
            updatePalette();
        };

        /* =======================================
           5. LOGIKA PENGUMPULAN (AJAX BATCH)
           ======================================= */
        // Parameter isAutoSubmit digunakan agar saat waktu habis tidak muncul popup konfirmasi
        const submitQuizAction = async (isAutoSubmit = false) => {

            if (!isAutoSubmit) {
                const belum = answers.filter(a => a === null || String(a).trim() === '').length;

                if (belum > 0) {
                    const res = await Swal.fire({
                        title: 'Yakin Kumpulkan?',
                        html: `Masih ada <strong class="text-danger">${belum} soal</strong> yang belum Anda jawab.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tetap Kumpulkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33'
                    });
                    if (!res.isConfirmed) return;
                }
            }

            Swal.fire({
                title: 'Menyimpan Jawaban...',
                text: 'Jangan tutup halaman ini.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const payload = questions.map((q, i) => ({
                soal_id: q.id,
                jawaban: answers[i]
            }));

            fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        jawaban: payload
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    // Hapus session storage timer agar bersih
                    sessionStorage.removeItem(`quiz_endtime_${MY_USER_ID}_${ACTIVITY_ID}`);

                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Semua jawaban Anda telah tersimpan dengan aman.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.next_url;
                    });
                })
                .catch(err => {
                    Swal.fire('Gagal Menyimpan!',
                        'Terjadi masalah pada server. Periksa koneksi internet Anda lalu coba lagi.',
                        'error');
                });
        };

        const btnMode2 = document.getElementById('finishBtnMode2');
        const btnMode1 = document.getElementById('finishBtnMode1');
        if (btnMode2) btnMode2.onclick = () => submitQuizAction(false);
        if (btnMode1) btnMode1.onclick = () => submitQuizAction(false);

        /* =======================================
           FITUR PROTEKSI TOMBOL BACK BROWSER
           ======================================= */
        // Mendorong state kosong ke history saat halaman dimuat
        history.pushState(null, null, location.href);

        window.addEventListener('popstate', function(event) {
            // Begitu tombol back ditekan, kita dorong lagi state-nya agar tidak benar-benar kembali
            history.pushState(null, null, location.href);

            Swal.fire({
                title: 'Ingin meninggalkan kuis?',
                text: "Selesaikan kuis terlebih dahulu agar jawaban Anda tersimpan dengan aman.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, arahkan ke route aktivitas
                    window.location.href = "{{ route('siswa.aktivitas') }}";
                }
            });
        });
    </script>
</body>

</html>
