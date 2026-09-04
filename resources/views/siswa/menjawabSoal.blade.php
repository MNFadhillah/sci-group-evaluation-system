<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }} - Kuis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html, body {
            height: 100%;
        }

        body {
            background: linear-gradient(160deg, #f0f3ff 0%, #f8f9fa 60%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: stretch;
            justify-content: center;
            overflow: auto;
        }

        .container-fluid {
            width: 100%;
            max-width: 1100px;
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
        }

        .container-fluid > h3 {
            flex: 0 0 auto;
            margin-bottom: 1rem !important;
        }

        /* ===== Kartu info awal — mengisi sisa layar, tanpa perlu scroll ===== */
        #info-test {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
        }

        #info-test .info-wrapper {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        #info-test .card {
            width: 100%;
            max-width: 640px;
            border-radius: 1.5rem;
        }

        #info-test .card-body {
            padding: 2rem 2.25rem !important;
        }

        #info-test .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df, #6f8cf0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: #fff;
            font-size: 1.4rem;
            box-shadow: 0 8px 20px rgba(78, 115, 223, .3);
        }

        #info-test h5 {
            font-size: 1.3rem;
            margin-bottom: .25rem !important;
        }

        #info-test .info-stat {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 12px 16px;
            flex: 1;
            border: 1px solid #eaecf4;
        }

        #info-test .info-stat .label {
            font-size: .78rem;
            color: #858796;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 2px;
        }

        #info-test .info-stat .value {
            font-size: 1.35rem;
            font-weight: 700;
        }

        #info-test .btn {
            min-width: 140px;
            padding: 10px 0;
            font-size: 1rem;
        }

        #info-test p.small {
            margin-bottom: 1.25rem !important;
        }

        #resumeBanner {
            max-width: 640px;
            width: 100%;
            margin: 0 auto .75rem;
            border-radius: 1rem;
            padding: .6rem 1rem;
            font-size: .88rem;
        }

        /* ===== Halaman soal ===== */
        #soal-test {
            width: 100%;
            max-width: 950px;
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }

        .soal-meta {
            background: #fff;
            border: 1px solid #eaecf4;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 14px rgba(31, 45, 61, .04);
        }

        .soal-meta .meta-item {
            font-size: .92rem;
            color: #444;
            margin-bottom: 2px;
        }

        .soal-meta .meta-item strong {
            color: #222;
        }

        #timer {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-size: 1.15rem;
            background: #eef1ff;
            color: #4e73df;
            border-radius: 999px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .25s, color .25s;
        }

        #timer::before {
            content: "\F293";
            font-family: "bootstrap-icons";
        }

        #timer.timer-warning {
            background: #ffe6e6;
            color: #dc3545;
            animation: pulseTimer 1s infinite;
        }

        @keyframes pulseTimer {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .progress {
            height: 10px;
            border-radius: 999px;
            background: #eaecf4;
            overflow: hidden;
        }

        .progress-bar {
            font-size: .65rem;
            line-height: 10px;
            background: linear-gradient(90deg, #4e73df, #6f8cf0);
        }

        .question-panel {
            background: #fff;
            border: 1px solid #eaecf4;
            border-radius: 16px;
            padding: 1.75rem;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 18px rgba(31, 45, 61, .05);
        }

        .soal-nomor {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            font-weight: 700;
            color: #4e73df;
            background: #eef1ff;
            border-radius: 999px;
            padding: 4px 12px;
            margin-bottom: .6rem;
        }

        #questionText {
            font-size: 1.15rem;
            line-height: 1.5;
        }

        /* Opsi jawaban bergaya kartu, seluruh baris bisa diklik */
        #optionsContainer .option-item {
            display: block;
            border: 1.5px solid #e3e6f0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        #optionsContainer .option-item:hover {
            border-color: #b7c2f0;
            background: #f8f9fe;
        }

        #optionsContainer .form-check-input:checked ~ .option-label {
            font-weight: 600;
        }

        #optionsContainer .option-item:has(.form-check-input:checked) {
            border-color: #4e73df;
            background: #eef1ff;
        }

        #optionsContainer .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        #optionsContainer .form-check-input {
            width: 1.15em;
            height: 1.15em;
            flex-shrink: 0;
        }

        .btn-next {
            min-width: 150px;
            padding: 10px 0;
            font-weight: 600;
        }

        /* ===== Responsif ===== */
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            #info-test {
                min-height: auto;
                margin-top: 1rem;
            }

            #info-test .card-body {
                padding: 1.75rem 1.5rem !important;
            }

            #info-test .d-flex.gap-3 {
                flex-direction: column;
            }

            #info-test .info-stat {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 0 !important;
            }

            .question-panel {
                padding: 1.25rem;
                min-height: 220px;
            }

            .soal-meta {
                padding: 12px 14px;
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            #timer {
                align-self: flex-end;
            }

            #questionText {
                font-size: 1.05rem;
            }

            #info-test .d-flex.justify-content-center.gap-2 {
                flex-direction: column;
            }

            #info-test .btn {
                width: 100%;
            }
        }
    </style>

</head>

<body class="p-4">

    <div class="container-fluid px-4 px-xl-5">

        <h3 class="text-center mb-4">
            {{ $judul }} <small class="text-muted">({{ ucfirst($topik) }})</small>
        </h3>

        <!-- INFORMASI AWAL -->
        <div id="info-test" class="text-center">

            <div class="info-wrapper">

                <!-- Banner muncul otomatis lewat JS kalau ada progres tersimpan -->
                <div id="resumeBanner" class="alert alert-info d-none text-start d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        Kamu punya pengerjaan yang belum selesai. Tekan <b>Lanjutkan</b> untuk melanjutkan dari soal terakhir.
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <div class="icon-circle">
                            <i class="bi bi-pencil-square"></i>
                        </div>

                        <h5 class="fw-bold mb-1">Keterangan Aktivitas</h5>
                        <p class="text-muted small mb-4">Baca dulu sebelum memulai pengerjaan</p>

                        <div class="d-flex gap-3 mb-4 text-start">
                            <div class="info-stat text-center">
                                <div class="label">Jumlah Soal</div>
                                <div class="value text-success" id="infoJumlahSoal">
                                    {{ isset($jumlah_soal) ? $jumlah_soal . ' Soal' : '—' }}
                                </div>
                            </div>
                            <div class="info-stat text-center">
                                <div class="label">Durasi</div>
                                <div class="value text-primary" id="infoDurasi">
                                    {{ isset($durasi) ? $durasi . ' Menit' : '—' }}
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-4">
                            Waktu akan mulai dihitung setelah Anda menekan tombol <b>Mulai</b>.
                            Progres jawabanmu otomatis tersimpan di perangkat ini — jika halaman
                            ter-refresh secara tidak sengaja, pengerjaan akan dilanjutkan otomatis.
                        </p>

                        <div class="d-flex justify-content-center gap-2">
                            <button id="mulaiBtn" class="btn btn-primary px-4" onclick="mulai()">
                                <i class="bi bi-play-fill me-1"></i> Mulai Sekarang
                            </button>
                            <a href="{{ route('siswa.aktivitas') }}" class="btn btn-outline-secondary px-4">Kembali</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <!-- AREA SOAL -->
        <div id="soal-test" hidden class="mx-auto" style="max-width: 900px;">

            <!-- Header Soal -->
            <div class="soal-meta d-flex justify-content-between align-items-start">
                <div>
                    <div class="meta-item"><strong>Kelas:</strong> {{ $kelas }}</div>
                    <div class="meta-item"><strong>Mata Pelajaran:</strong> {{ $mapel }}</div>
                    <div class="meta-item"><strong>Topik:</strong> {{ $topik }}</div>
                </div>

                <div id="timer">
                    {{ str_pad($durasi, 2, '0', STR_PAD_LEFT) }}:00
                </div>
            </div>

            <!-- PROGRESS BAR -->
            <div class="mb-3">
                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        style="width: 0%">
                        0%
                    </div>
                </div>
            </div>

            <!-- Panel Soal -->
            <div class="question-panel">
                <div>
                    <div id="soalNomorBadge" class="soal-nomor"></div>
                    <div id="questionText" class="mb-3 fw-semibold"></div>

                    <!-- Tempat opsi -->
                    <div id="optionsContainer" class="mb-4"></div>
                </div>

                <!-- Tombol -->
                <div class="d-flex justify-content-end">
                    <button id="nextBtn" class="btn btn-success btn-next" onclick="checkAnswer()">
                        Selanjutnya
                    </button>
                </div>
            </div>

        </div>


    </div>

    <!-- COMBO METER -->
    <div id="comboMeter"
        style="position:fixed; top:20px; left:20px; 
            font-size:2rem; font-weight:bold; 
            color:#ff9800; text-shadow:2px 2px 8px rgba(0,0,0,.4);
            display:none; z-index:9999;">
    </div>

    <!-- ON FIRE EFFECT -->
    <div id="onFire"
        style="position:fixed; bottom:20px; right:20px;
            font-size:2.5rem; font-weight:bold; 
            color:#ff3b3b; text-shadow:0 0 15px orange;
            display:none; z-index:9999;">
        🔥 ON FIRE!
    </div>

    <script>
        const ID_ACTIVITY = {{ $id_activity }};
        const STORAGE_KEY = `graflearn_individu_progress_${ID_ACTIVITY}`;

        let currentIndex = 0;
        let totalQuestions = 0;
        let answers = [];
        let currentQuestionID = null;
        let timeLeft = 30 * 60;
        let timerInterval;
        let totalBenar = 0;
        let totalSalah = 0;
        let testStarted = false;

        // ================= PERSISTENSI (fix bug: refresh -> progres hilang) =================

        function saveProgress() {
            if (!testStarted) return;
            const payload = {
                currentIndex,
                totalQuestions,
                answers,
                timeLeft,
                totalBenar,
                totalSalah,
                savedAt: Date.now()
            };
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {
                console.warn('Gagal menyimpan progres lokal:', e);
            }
        }

        function loadSavedProgress() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                return null;
            }
        }

        function clearSavedProgress() {
            localStorage.removeItem(STORAGE_KEY);
        }

        // Tampilkan banner "lanjutkan pengerjaan" kalau ada progres tersimpan
        window.addEventListener('DOMContentLoaded', () => {
            const saved = loadSavedProgress();
            if (saved && saved.timeLeft > 0 && saved.totalQuestions > 0) {
                document.getElementById('resumeBanner').classList.remove('d-none');
                const btn = document.getElementById('mulaiBtn');
                btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Lanjutkan';
                btn.onclick = () => resumeProgress(saved);
            }
        });

        function resumeProgress(saved) {
            currentIndex = saved.currentIndex;
            totalQuestions = saved.totalQuestions;
            answers = saved.answers;
            timeLeft = saved.timeLeft;
            totalBenar = saved.totalBenar;
            totalSalah = saved.totalSalah;
            testStarted = true;

            document.getElementById("info-test").hidden = true;
            document.getElementById("soal-test").hidden = false;

            loadQuestion();
            startTimer();
        }

        // Peringatan sebelum menutup/refresh tab selagi mengerjakan.
        // Catatan: browser modern TIDAK mengizinkan JavaScript benar-benar
        // memblokir refresh/tutup tab (ini pembatasan keamanan browser, bukan
        // celah di kode kita) — dialog konfirmasi bawaan browser adalah batas
        // maksimal yang bisa dipicu di sini.
        window.addEventListener('beforeunload', (e) => {
            if (testStarted) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Cegat tombol refresh keyboard (F5 / Ctrl+R / Cmd+R) selagi mengerjakan,
        // supaya siswa tidak reflek me-refresh dan harus sadar dulu lewat konfirmasi.
        window.addEventListener('keydown', (e) => {
            if (!testStarted) return;
            const isRefreshCombo = e.key === 'F5' ||
                ((e.ctrlKey || e.metaKey) && (e.key === 'r' || e.key === 'R'));

            if (isRefreshCombo) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Jangan Refresh Halaman',
                    text: 'Tes sedang berjalan. Menutup atau memuat ulang halaman bisa mengganggu pengerjaanmu.',
                    confirmButtonText: 'Mengerti'
                });
            }
        });

        // ======================================================================================

        function startTimer() {
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--;

                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;

                const timerEl = document.getElementById("timer");
                timerEl.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                timerEl.classList.toggle('timer-warning', timeLeft <= 60);

                saveProgress();

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    Swal.fire("Waktu Habis!", "Tes otomatis diselesaikan.", "info");
                    showResult();
                }
            }, 1000);
        }

        function mulai() {
            fetch(`/activity/{{ $id_activity }}/start`)
                .then(async r => {
                    const data = await r.json();

                    if (!r.ok) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Aktivitas Tidak Bisa Dimulai',
                            text: data.message ?? 'Aktivitas belum siap',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#dc3545'
                        });
                        throw new Error(data.message);
                    }

                    return data;
                })
                .then(data => {
                    totalQuestions = data.totalQuestions;
                    answers = Array(totalQuestions).fill(null);
                    testStarted = true;

                    document.getElementById("info-test").hidden = true;
                    document.getElementById("soal-test").hidden = false;

                    const durasiMenit = Number.isInteger(data.durasi_pengerjaan) ?
                        data.durasi_pengerjaan :
                        30;

                    timeLeft = durasiMenit * 60;
                    currentIndex = 0;
                    totalBenar = 0;
                    totalSalah = 0;

                    saveProgress();
                    loadQuestion();
                    startTimer();
                })
                .catch(err => {
                    console.warn('Start dibatalkan:', err.message);
                });
        }

        function loadQuestion() {

            fetch(`/activity/{{ $id_activity }}/question?index=${currentIndex}`)
                .then(r => r.json())
                .then(q => {

                    currentQuestionID = q.question_id;

                    let diff = q.difficulty ?? "tidak ada";
                    let badgeClass = "bg-secondary";
                    let diffLabel = diff.charAt(0).toUpperCase() + diff.slice(1);

                    if (diff.toLowerCase() === "mudah") badgeClass = "bg-success";
                    if (diff.toLowerCase() === "sedang") badgeClass = "bg-warning text-dark";
                    if (diff.toLowerCase() === "sulit") badgeClass = "bg-danger";

                    document.getElementById('soalNomorBadge').innerHTML = `
                        Soal ${currentIndex + 1} dari ${totalQuestions}
                        <span class="badge ${badgeClass} ms-1">${diffLabel}</span>
                    `;

                    document.getElementById('questionText').innerHTML = q.question.text;

                    let html = "";

                    if (q.type === "MultipleChoice") {

                        q.options.forEach(o => {
                            let key = Object.keys(o)[0];
                            let val = o[key].teks;

                            html += `
            <label class="option-item">
                <div class="form-check">
                    <input type="radio" name="answer" value="${key}" class="form-check-input"
                        ${answers[currentIndex] === key ? "checked" : ""}
                        onchange="saveDraftAnswer()">
                    <span class="option-label form-check-label">${key.toUpperCase()}. ${val}</span>
                </div>
            </label>
        `;
                        });

                    } else if (q.type === "ShortAnswer") {

                        html = `
        <input type="text" name="answer" class="form-control"
            placeholder="Ketik jawaban..."
            value="${answers[currentIndex] ?? ''}"
            oninput="saveDraftAnswer()">
    `;
                    }

                    document.getElementById("optionsContainer").innerHTML = html;

                    if (currentIndex === totalQuestions - 1) {
                        document.getElementById("nextBtn").innerText = "Selesai";
                        document.getElementById("nextBtn").classList.replace("btn-success", "btn-primary");
                    } else {
                        document.getElementById("nextBtn").innerText = "Selanjutnya";
                        document.getElementById("nextBtn").classList.replace("btn-danger", "btn-success");
                    }

                    updateProgress();
                });
        }

        // Simpan draf jawaban (belum submit) ke localStorage supaya tidak hilang saat refresh
        function saveDraftAnswer() {
            let selectedRadio = document.querySelector('input[name="answer"]:checked');
            let textAnswer = document.querySelector('input[name="answer"]:not([type=radio])');

            if (selectedRadio) {
                answers[currentIndex] = selectedRadio.value;
            } else if (textAnswer) {
                answers[currentIndex] = textAnswer.value;
            }
            saveProgress();
        }

        function checkAnswer() {

            let selectedRadio = document.querySelector('input[name="answer"]:checked');
            let textAnswer = document.querySelector('input[name="answer"]:not([type=radio])');

            let finalAnswer = null;

            if (selectedRadio) {
                finalAnswer = selectedRadio.value;
            } else if (textAnswer) {
                finalAnswer = textAnswer.value.trim();
                if (finalAnswer === "") {
                    return Swal.fire("Oops", "Isi jawaban dulu!", "warning");
                }
            } else {
                return Swal.fire("Oops", "Pilih atau isi jawaban dulu!", "warning");
            }

            answers[currentIndex] = finalAnswer;
            saveProgress();

            document.getElementById("nextBtn").disabled = true;

            fetch(`/activity/{{ $id_activity }}/submit`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        question_id: currentQuestionID,
                        user_answer: finalAnswer
                    })
                })
                .then(r => r.json())
                .then(res => {

                    if (res.correct === true) {
                        totalBenar++;
                    } else {
                        totalSalah++;
                    }

                    saveProgress();

                    showAnswerFeedback(res);
                    updateComboUI(res.correct ? res.streak_correct : 0);

                    setTimeout(() => {
                        document.getElementById("nextBtn").disabled = false;
                        if (currentIndex < totalQuestions - 1) {
                            currentIndex++;
                            saveProgress();
                            loadQuestion();
                        } else {
                            showResult();
                        }
                    }, 1200);
                })
                .catch(err => {
                    document.getElementById("nextBtn").disabled = false;
                    Swal.fire("Error", "Gagal mengirim jawaban. Periksa koneksi internet dan coba lagi.", "error");
                });
        }

        function showAnswerFeedback(res) {

            const isCorrect = res.correct === true;

            Swal.fire({
                icon: isCorrect ? 'success' : 'error',
                title: isCorrect ? 'Jawaban Benar 🎉' : 'Jawaban Salah ❌',
                html: `
            <div style="text-align:center">
                ${isCorrect
                        ? `<p class="mb-0">Mantap! Jawaban kamu sudah benar 👏</p>`
                        : `<p class="mb-0 text-muted">Yuk coba fokus di soal berikutnya 💪</p>`
                    }
            </div>
        `,
                timer: 1100,
                showConfirmButton: false,
                timerProgressBar: true,
                allowOutsideClick: false
            });
        }

        function updateComboUI(streak) {

            const combo = document.getElementById("comboMeter");
            const fire = document.getElementById("onFire");

            if (streak >= 2) {
                combo.style.display = "block";
                combo.innerText = `COMBO x${streak}`;
            } else {
                combo.style.display = "none";
            }

            if (streak >= 3) {
                fire.style.display = "block";
                fire.classList.add("active");
            } else {
                fire.style.display = "none";
                fire.classList.remove("active");
            }
        }

        function showResult() {
            clearInterval(timerInterval);
            testStarted = false;

            fetch(`/activity/{{ $id_activity }}/finish`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(r => r.json())
                .then(res => {
                    clearSavedProgress();

                    const db = res.result_db ?? null;

                    const sec = res.duration_seconds ?? (db ? db.waktu_mengerjakan : 0);
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;

                    const jumlahSoal = (res.jumlah_soal ?? (db ? (db.jumlah_soal ?? null) : null)) ?? '-';

                    const totalBenarFinal = db ? (db.total_benar ?? (res.total_correct ?? 0)) : (res.total_correct ?? 0);

                    const base = db ? (db.result ?? null) : (res.result ?? null);
                    const bonus = db ? (db.bonus_poin ?? null) : (res.bonus_poin ?? null);
                    const real = db ? (db.real_poin ?? null) : (res.real_poin ?? null);

                    const nilaiAkhir = db ? (db.nilai_akhir ?? (res.nilai_akhir ?? null)) : (res.nilai_akhir ?? null);

                    const statusText = db ? (db.result_status ?? (res.status_benar ? 'Pass' : 'Remedial')) : (res
                        .status_benar ? 'Pass' : 'Remedial');

                    const fmt = v => (v === null || v === undefined) ? '-' : v;

                    const html = `
            <div style="text-align:left">
                <p><strong>Waktu mengerjakan:</strong> ${m} m ${s} s</p>
                <p><strong>Total Benar:</strong> ${totalBenarFinal}</p>
                <p><strong>Total Salah:</strong> ${totalSalah}</p>
                <p><strong>Nilai dasar:</strong> ${fmt(real)}</p>
                <p><strong>Bonus poin:</strong> ${fmt(bonus)}</p>
                <p><strong>Total poin:</strong> ${fmt(base)}</p>
                <p><strong>Nilai akhir (0-100):</strong> ${fmt(nilaiAkhir)}</p>
                <p><strong>Status:</strong> ${fmt(statusText)}</p>
            </div>
        `;

                    Swal.fire({
                        title: "Selesai!",
                        html: html,
                        icon: "success",
                        confirmButtonText: "Kembali ke Aktivitas",
                        reverseButtons: true,
                    }).then(result => {
                        if (result.isConfirmed) {
                            location.href = "{{ route('siswa.aktivitas') }}";
                        }
                    });

                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Gagal menyelesaikan tes. Coba lagi.", "error")
                        .then(() => location.href = "{{ route('siswa.aktivitas') }}");
                });
        }

        function updateProgress() {
            if (totalQuestions === 0) return;

            const percent = Math.round(((currentIndex + 1) / totalQuestions) * 100);

            const bar = document.getElementById("progressBar");
            bar.style.width = percent + "%";
            bar.innerText = percent + "%";
            bar.setAttribute("aria-valuenow", percent);
        }
    </script>

</body>

</html>