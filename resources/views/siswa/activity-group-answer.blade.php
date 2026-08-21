@extends('layouts.main')
@section('aktivitas', 'active')

@section('content')
<style>
    /* =======================================
       CUSTOM UI/UX DASHBOARD STYLE
       ======================================= */
    .quiz-container { max-width: 1200px; margin: 0 auto; }
    
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
    .num-btn:hover { border-color: #0d6efd; color: #0d6efd; }
    .num-btn.answered { background-color: #0d6efd; border-color: #0d6efd; color: white; }
    .num-btn.current { border-color: #0dcaf0; box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.25); color: #0dcaf0; }
    .num-btn.flagged { background-color: #ffc107; border-color: #ffc107; color: #000; }
    
    /* Styling Jawaban Teman (Mode 1) */
    .peer-answer-card {
        border-left: 4px solid #0dcaf0;
        background-color: #f8f9fa;
        border-radius: 0 12px 12px 0;
    }
</style>

<div class="container py-4 quiz-container">
    
    <!-- HEADER DASHBOARD -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="{{ route('siswa.aktivitas') }}" class="btn btn-sm btn-outline-secondary mb-2 rounded-pill shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <h3 class="fw-bolder text-dark mb-1">{{ $activity->title }}</h3>
            <p class="text-muted mb-0 small">
                <i class="fas fa-users me-1"></i> Kelompok: <strong class="text-primary">{{ $group->name }}</strong> | 
                <i class="fas fa-list-ol me-1"></i> Total: <strong>{{ $questions->count() }} Soal</strong>
            </p>
        </div>
        <div>
            @if($isMode2)
                <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-user-edit me-1"></i> Mode Kuis Individu</span>
            @else
                <span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-users-cog me-1"></i> Mode Tugas Kelompok</span>
            @endif
        </div>
    </div>

    <!-- HALAMAN PETUNJUK (SUDAH DIPERLEBAR & DISESUAIKAN) -->
    <div id="instructionPage" class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h3 class="fw-bolder text-dark">Petunjuk Pengerjaan</h3>
            </div>
            
            <div class="bg-light p-4 rounded-4 mx-auto mb-4" style="max-width: 900px;">
                <ul class="text-muted mb-0" style="line-height: 1.8; font-size: 1.05rem;">
                    <li>Aktivitas ini terdiri dari <strong class="text-dark">{{ $questions->count() }} butir soal</strong>.</li>
                    @if($isMode2)
                        <li>Paket soal ini adalah <strong>milik Anda secara individu</strong>. Nilai akan digabung untuk menjadi rata-rata kelompok.</li>
                    @else
                        <li>Ini adalah <strong>Tugas Kelompok</strong>. Anda dapat melihat progres teman secara <i>real-time</i> di bagian bawah kolom jawaban Anda.</li>
                    @endif
                    <li>Setiap jawaban yang Anda pilih/ketik akan <strong>tersimpan otomatis secara sementara</strong> (Draft).</li>
                    <li>Jangan lupa menekan tombol <strong>Kumpulkan Jawaban</strong> di akhir agar dapat melanjutkan ke tahap Penilaian Teman (SCI).</li>
                </ul>
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
            
            <!-- SISI KIRI: LEMBAR SOAL (Full width jika Mode 1) -->
            <div class="{{ $isMode2 ? 'col-lg-8' : 'col-lg-12' }}">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <!-- Header Soal -->
                    <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-primary mb-0">Soal <span id="qIndex">1</span></h5>
                        <div>
                            <button id="flagBtn" class="btn btn-sm btn-outline-warning rounded-pill fw-bold text-dark shadow-sm px-3">
                                <i class="fas fa-flag"></i> Tandai Ragu
                            </button>
                        </div>
                    </div>

                    <!-- Area Konten Soal -->
                    <div class="card-body p-4 p-md-5 d-flex flex-column">
                        <div id="questionArea" class="flex-grow-1">
                            <!-- Injeksi JS ada di sini -->
                        </div>
                    </div>

                    <!-- Footer Soal (Tombol Prev/Next & Submit Mode 1) -->
                    <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4 rounded-bottom-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <button id="prevBtn" class="btn btn-outline-secondary px-4 fw-bold rounded-pill" disabled>
                                    <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                                </button>
                                <button id="nextBtn" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                                    Berikutnya <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            </div>

                            @if(!$isMode2)
                            <!-- Tombol Kumpul Khusus Mode 1 (Karena Sidebar disembunyikan) -->
                            <div>
                                <button id="finishBtnMode1" class="btn btn-success px-4 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Kumpulkan Jawaban
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN: NAVIGASI PALET (HANYA MUNCUL DI MODE 2) -->
            @if($isMode2)
            <div class="col-lg-4">
                <div class="position-sticky" style="top: 20px;">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4">
                            <h6 class="fw-bold text-dark mb-0 text-center">Navigasi Soal</h6>
                        </div>
                        <div class="card-body p-4">
                            <!-- Legenda Palet -->
                            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4 small fw-semibold text-secondary">
                                <div class="d-flex align-items-center"><span class="rounded-circle border" style="width:12px;height:12px;background:#fff;margin-right:6px;"></span> Belum</div>
                                <div class="d-flex align-items-center"><span class="rounded-circle" style="width:12px;height:12px;background:#0d6efd;margin-right:6px;"></span> Dijawab</div>
                                <div class="d-flex align-items-center"><span class="rounded-circle" style="width:12px;height:12px;background:#ffc107;margin-right:6px;"></span> Ragu</div>
                            </div>

                            <!-- Grid Palet -->
                            <div id="palette" class="nav-palette mb-4"></div>

                            <hr class="text-muted opacity-25 my-4">

                            <!-- Tombol Kumpul Mode 2 -->
                            <button id="finishBtnMode2" class="btn btn-success btn-lg w-100 fw-bold shadow-sm rounded-pill">
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

<!-- LIBRARY TAMBAHAN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- =======================================
     DATA BRIDGE (PHP TO JAVASCRIPT)
     ======================================= -->
<div id="quizConfig" class="d-none"
    data-ismode2="{{ $isMode2 ? 'true' : 'false' }}"
    data-userid="{{ $user->id }}"
    data-submiturl="{{ route('activity.group.answer.save', $activity->id) }}"
    data-csrftoken="{{ csrf_token() }}">
</div>
<script id="data-questions" type="application/json">{!! json_encode($questions) !!}</script>
<script id="data-answers" type="application/json">{!! json_encode($answers) !!}</script>
<script id="data-members" type="application/json">{!! json_encode($group->members->load('user')) !!}</script>

<script>
    /* =======================================
       1. DEKLARASI VARIABEL DARI SERVER
       ======================================= */
    const configEl = document.getElementById('quizConfig');
    
    const IS_MODE_2 = configEl.dataset.ismode2 === 'true';
    const MY_USER_ID = parseInt(configEl.dataset.userid);
    const SUBMIT_URL = configEl.dataset.submiturl;
    const CSRF_TOKEN = configEl.dataset.csrftoken;
    
    const rawQuestions = JSON.parse(document.getElementById('data-questions').textContent);
    const existingAnswers = JSON.parse(document.getElementById('data-answers').textContent);
    const groupMembers = JSON.parse(document.getElementById('data-members').textContent);

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
        let parsedText = safeJSONParse(q.question, { text: q.question || 'Teks soal tidak terbaca', url: null });
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
                                finalOptions[letter] = { text: content, url: null };
                            } else {
                                finalOptions[letter] = { text: content.teks || content.text || '', url: content.url || null };
                            }
                        } else {
                            let letter = String.fromCharCode(97 + idx);
                            finalOptions[letter] = { text: String(optObj), url: null };
                        }
                    });
                } else {
                    Object.entries(parsedOpts).forEach(([letter, content]) => {
                        if (typeof content === 'string') {
                            finalOptions[letter] = { text: content, url: null };
                        } else {
                            finalOptions[letter] = { text: content.teks || content.text || '', url: content.url || null };
                        }
                    });
                }
            } catch (err) {
                console.error("Format opsi kacau pada soal:", q.id);
            }
        }

        let finalSoalText = typeof parsedText === 'string' ? parsedText : (parsedText.text || parsedText.teks || '');

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
       3. KONTROL TAMPILAN (UI LOGIC)
       ======================================= */
    document.getElementById('startBtn').onclick = () => {
        document.getElementById('instructionPage').classList.add('d-none');
        document.getElementById('quizPage').classList.remove('d-none');
        renderQuestion(0);
        updatePalette();
    };

    function renderQuestion(i) {
        idx = i;
        document.getElementById('qIndex').textContent = i + 1;
        const q = questions[i];
        const area = document.getElementById('questionArea');

        let html = ``;
        if (q.image) {
            html += `<div class="text-center mb-4"><img src="${q.image}" class="img-fluid rounded-3 shadow-sm border" style="max-height: 280px;"></div>`;
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

        const flagBtn = document.getElementById('flagBtn');
        if(flagged[i]) {
            flagBtn.className = "btn btn-sm btn-warning rounded-pill fw-bold text-dark shadow-sm px-3";
            flagBtn.innerHTML = '<i class="fas fa-flag"></i> Batal Tandai';
        } else {
            flagBtn.className = "btn btn-sm btn-outline-warning rounded-pill fw-bold text-dark shadow-sm px-3";
            flagBtn.innerHTML = '<i class="far fa-flag"></i> Tandai Ragu';
        }

        document.getElementById('prevBtn').disabled = i === 0;
        document.getElementById('nextBtn').disabled = i === questions.length - 1;
    }

    function renderOptions(q) {
        let html = '';
        
        // ===================================
        // UI JAWABAN MODE 2 (KUIS INDIVIDU)
        // ===================================
        if (IS_MODE_2) {
            if (q.type === 'MultipleChoice') {
                // MENGGUNAKAN GRID 2 KOLOM AGAR OPSI TIDAK MEMANJANG KE BAWAH
                html += `<div class="row g-3">`;
                Object.entries(q.options || {}).forEach(([key, opt]) => {
                    let isSelected = answers[idx] === key;
                    let className = isSelected ? 'quiz-option p-2 px-3 h-100 selected shadow-sm' : 'quiz-option p-2 px-3 h-100';
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
        } 
        
        // ===================================
        // UI JAWABAN MODE 1 (ESAI KELOMPOK)
        // ===================================
        else {
            html += `
                <div class="mb-5">
                    <label class="form-label fw-bold text-primary mb-2"><i class="fas fa-edit me-1"></i> Area Jawaban Anda:</label>
                    <textarea class="form-control bg-primary bg-opacity-10 border-primary border-opacity-25 shadow-sm" rows="5" placeholder="Tuliskan analisis atau pemikiran Anda di sini..." 
                    oninput="answers[idx] = this.value; updatePalette();">${answers[idx] || ''}</textarea>
                </div>
            `;

            html += `<h6 class="fw-bold text-secondary mb-3"><i class="fas fa-users me-1"></i> Progres Jawaban Anggota Lain:</h6>`;
            let hasPeers = false;
            
            groupMembers.forEach(member => {
                if (member.id_user !== MY_USER_ID) {
                    hasPeers = true;
                    let key = q.id + '_' + member.id_user;
                    let peerAns = existingAnswers[key] ? existingAnswers[key].answer : '<span class="text-muted fst-italic">Belum menuliskan jawaban...</span>';
                    let name = member.user ? member.user.name : 'Anggota';
                    
                    html += `
                    <div class="peer-answer-card p-3 mb-3 shadow-sm">
                        <div class="fw-bold text-info mb-2 small text-uppercase"><i class="fas fa-user-circle me-1"></i> ${name}</div>
                        <div class="text-dark fs-6" style="line-height:1.6;">${peerAns}</div>
                    </div>`;
                }
            });

            if(!hasPeers) {
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
        if(!palette) return; // Mode 1 tidak punya palet
        
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

    /* =======================================
       4. EVENT LISTENER NAVIGASI
       ======================================= */
    document.getElementById('flagBtn').onclick = () => {
        flagged[idx] = !flagged[idx];
        updatePalette();
        renderQuestion(idx);
    };

    document.getElementById('prevBtn').onclick = () => idx > 0 && renderQuestion(idx - 1);
    document.getElementById('nextBtn').onclick = () => idx < questions.length - 1 && renderQuestion(idx + 1);


    /* =======================================
       5. LOGIKA PENGUMPULAN (AJAX BATCH)
       ======================================= */
    const submitQuizAction = async () => {
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
            body: JSON.stringify({ jawaban: payload })
        })
        .then(res => {
            if(!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
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
            Swal.fire('Gagal Menyimpan!', 'Terjadi masalah pada server. Periksa koneksi internet Anda lalu coba lagi.', 'error');
        });
    };

    // Binding tombol submit sesuai mode yang aktif
    const btnMode2 = document.getElementById('finishBtnMode2');
    const btnMode1 = document.getElementById('finishBtnMode1');
    if (btnMode2) btnMode2.onclick = submitQuizAction;
    if (btnMode1) btnMode1.onclick = submitQuizAction;

</script>
@endsection