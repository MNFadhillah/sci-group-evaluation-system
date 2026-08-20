@extends('layouts.main')
@section('dataSoal', request()->is('generate-soal') ? 'active' : '')
@section('content')
    <div id="generateSoalWrap" class="container-fluid px-3 px-md-4 py-3 d-flex flex-column">

        <div class="d-flex align-items-center gap-2 mb-2">
            <h4 class="fw-bold mb-0">Generator Soal</h4>

            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:30px;height:30px" data-bs-toggle="modal" data-bs-target="#modalInfoGenerateSoal"
                title="Informasi Generator Soal">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-3">
                <form method="POST" action="{{ route('generateSoal.post') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold mb-1">Topik Soal</label>
                            <select name="topic" class="form-select" required>
                                <option value="">-- Pilih Topik --</option>
                                @foreach ($topics as $t)
                                    <option value="{{ $t->id }}"
                                        {{ isset($selectedTopic) && $selectedTopic == $t->id ? 'selected' : '' }}>
                                        {{ $t->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Jenjang</label>
                            <select name="jenjang" class="form-select" required>
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach ($jenjangList as $j)
                                    <option value="{{ $j }}"
                                        {{ (old('jenjang') ?? ($selectedJenjang ?? null)) == $j ? 'selected' : '' }}>
                                        {{ strtoupper($j) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Jumlah Soal</label>
                            <input type="number" name="jumlah" class="form-control" min="3" max="30"
                                value="{{ $jumlahInput ?? 9 }}" required>
                        </div>
                    </div>

                    <button class="btn btn-success w-100 py-2 fw-bold mt-2">
                        <i class="bi bi-rocket-takeoff"></i>
                        Buat Prompt AI
                    </button>
                </form>
            </div>
        </div>

        @isset($prompt)
            <div class="mt-2">
                <h6 class="fw-bold mb-2 text-primary">Prompt AI yang Dihasilkan:</h6>
                <textarea id="promptTextarea" class="form-control bg-light p-3" rows="10">{{ $prompt }}</textarea>

                <div class="text-end mt-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnCopyPrompt">
                        <i class="bi bi-clipboard-check me-1"></i> Salin Prompt
                    </button>
                </div>
            </div>
        @endisset

        <hr class="my-2">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <h6 class="fw-bold text-primary mb-0">Import Soal dari JSON</h6>
            <span class="text-muted small">Pilih metode input di bawah</span>
        </div>

        <!-- ========== NAV TABS ========== -->
        <ul class="nav nav-tabs" id="importTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="paste-tab" data-bs-toggle="tab"
                    data-bs-target="#paste-json" type="button" role="tab">
                    <i class="bi bi-clipboard me-1"></i> Tempel Kode JSON
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="upload-tab" data-bs-toggle="tab"
                    data-bs-target="#upload-json" type="button" role="tab">
                    <i class="bi bi-upload me-1"></i> Upload File JSON
                </button>
            </li>
        </ul>

        <!-- ========== TAB CONTENT ========== -->
        <div class="tab-content mt-2">

            <!-- TAB 1: PASTE JSON -->
            <div class="tab-pane show active" id="paste-json" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 rounded-top-0">
                    <div class="card-body p-3">
                        <form action="{{ route('importQuestionJson') }}" method="POST" id="formPasteJson">
                            @csrf
                            <textarea name="json_text" class="form-control" rows="6"
                                placeholder="Tempel JSON di sini..."></textarea>
                            <input type="hidden" name="upload_mode" value="paste">
                            <button type="submit" class="btn btn-primary w-100 mt-2 fw-bold">
                                <i class="bi bi-cloud-upload"></i> Simpan JSON ke Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: UPLOAD FILE -->
            <div class="tab-pane fade" id="upload-json" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 rounded-top-0">
                    <div class="card-body p-3">
                        <form action="{{ route('importQuestionJson') }}" method="POST" enctype="multipart/form-data"
                            id="formUploadJson">
                            @csrf
                            <div class="input-group">
                                <input type="file" name="file" accept=".json,.txt" class="form-control" required>
                                <button type="submit" class="btn btn-success fw-bold">
                                    <i class="bi bi-upload"></i> Simpan ke Database
                                </button>
                            </div>
                            <input type="hidden" name="upload_mode" value="file">
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end mt-auto pt-3">
            <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </a>
        </div>

    </div>

    {{-- MODAL INFO GENERATOR SOAL --}}
    <div class="modal fade" id="modalInfoGenerateSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Panduan Generator Soal Otomatis
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body small">
                    <ul class="mb-2">
                        <li>Pilih <b>Topik</b>, <b>Jenjang</b>, dan <b>Jumlah Soal</b>, lalu sistem menghasilkan
                            <b>prompt AI</b> siap pakai.</li>
                        <li>Salin prompt tersebut ke AI (misalnya ChatGPT) untuk menghasilkan soal dalam format
                            <b>JSON</b>.</li>
                    </ul>
                    <ul class="mb-2">
                        <li>Import JSON hasil AI lewat tab <b>Tempel JSON</b> atau <b>Upload File JSON</b>.</li>
                        <li>JSON yang valid otomatis disimpan ke bank soal; format tidak sesuai akan dibatalkan.</li>
                    </ul>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const btnCopy = document.getElementById('btnCopyPrompt');
            const textarea = document.getElementById('promptTextarea');

            if (btnCopy && textarea) {
                btnCopy.addEventListener('click', function() {
                    const promptText = textarea.value;

                    navigator.clipboard.writeText(promptText)
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Prompt AI berhasil disalin ke clipboard.',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal menyalin',
                                text: 'Browser tidak mengizinkan akses clipboard.',
                            });
                        });
                });
            }

            // ===== SUBMIT HANDLER (PASTE & UPLOAD JSON) =====
            ['formPasteJson', 'formUploadJson'].forEach(function(formId) {
                const form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', function() {
                    Swal.fire({
                        title: 'Memproses JSON',
                        text: 'Mohon tunggu, soal sedang disimpan ke database.',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                });
            });

            // ===== SWEET ALERT DARI SESSION =====
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    html: `
                        <p>{{ session('success') }}</p>
                        <p><strong>Jumlah soal berhasil disimpan sebanyak: {{ session('imported_count') }} soal</strong></p>
                    `,
                    confirmButtonText: 'OK'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: "{{ session('error') }}",
                    confirmButtonText: 'Periksa JSON'
                });
            @endif

            // Isi ruang kosong antara konten dan footer secara dinamis,
            // konsisten dengan halaman Tambah Soal.
            function findFooter() {
                return document.querySelector('footer') ||
                    document.querySelector('.footer') ||
                    document.querySelector('[class*="footer"]');
            }

            function adjustWrapHeight() {
                const wrap = document.getElementById('generateSoalWrap');
                if (!wrap) return;

                wrap.style.minHeight = '0px';

                const top = wrap.getBoundingClientRect().top;
                const footer = findFooter();
                const footerHeight = footer ? footer.getBoundingClientRect().height : 0;
                const available = window.innerHeight - top - footerHeight;

                wrap.style.minHeight = Math.max(available, 0) + 'px';
            }

            adjustWrapHeight();
            window.addEventListener('resize', adjustWrapHeight);
            window.addEventListener('load', adjustWrapHeight);
        });
    </script>

@endsection