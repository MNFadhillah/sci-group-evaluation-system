@extends('layouts.main')
@section('dataSoal', request()->is('soal/tambah') ? 'active' : '')

@section('content')
    <div id="tambahSoalWrap" class="container-fluid px-3 px-md-4 py-3 d-flex flex-column">
    <div class="card shadow-sm border-0 rounded-4 flex-grow-1">
        <div class="card-body p-3 p-md-4 d-flex flex-column">

                {{-- HEADER --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <h4 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Soal
                    </h4>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        style="width:30px;height:30px" data-bs-toggle="modal"
                        data-bs-target="#modalInfoTambahSoal" title="Informasi Tambah Soal">
                        <i class="bi bi-info-lg"></i>
                    </button>
                </div>

                {{-- Info kelas --}}
                @if ($kelasGuru->count())
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        @foreach ($kelasGuru as $k)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people me-1"></i> {{ $k->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-danger small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Anda belum tergabung pada kelas manapun.
                    </div>
                @endif

                {{-- FORM START --}}
                <form id="soalForm" action="{{ route('simpanSoal') }}" method="POST"
                    enctype="multipart/form-data" class="d-flex flex-column flex-grow-1">
                    @csrf

                    {{-- baris 1: info dasar soal --}}
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Tipe Soal</label>
                            <select name="type" class="form-select" id="tipeSoal" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="MultipleChoice">Pilihan Ganda</option>
                                <option value="ShortAnswer">Isian Singkat</option>
                                <option value="Essay">Essay / Uraian</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select" id="difficulty" required>
                                <option value="">-- Pilih Kesulitan --</option>
                                <option value="mudah">Mudah</option>
                                <option value="sedang">Sedang</option>
                                <option value="sulit">Sulit</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Topik (opsional)</label>
                            <select name="id_topic" class="form-select" id="id_topic">
                                <option value="">-- Pilih Topik --</option>
                                @if (isset($topics) && $topics->count())
                                    @foreach ($topics as $t)
                                        <option value="{{ $t->id }}">{{ $t->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- baris 2: pertanyaan --}}
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Teks Pertanyaan</label>
                            <textarea name="question_text" id="question_text" class="form-control" rows="3"
                                placeholder="Tulis teks soal di sini..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Gambar Soal (opsional)</label>
                            <div class="d-flex gap-2">
                                <input type="file" name="question_image" class="form-control" accept="image/*"
                                    id="questionImageInput">
                                <input type="text" name="question_url" id="question_url" class="form-control"
                                    placeholder="Atau URL gambar">
                            </div>
                            <div id="previewQuestionImage" class="mt-2 text-center"></div>
                        </div>
                    </div>

                    {{-- Pilihan Ganda area --}}
                    <div id="opsiPilihanGanda" style="display:none;" class="mt-3">
                        <hr class="my-2">
                        <h6 class="fw-bold text-secondary mb-2">
                            <i class="bi bi-list-check me-1"></i> Pilihan Jawaban
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead>
                                    <tr class="text-muted small">
                                        <th style="width:6%">Opsi</th>
                                        <th style="width:34%">Teks Opsi</th>
                                        <th style="width:30%">Gambar (file)</th>
                                        <th style="width:30%">Gambar (URL)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['a', 'b', 'c', 'd', 'e'] as $i => $opt)
                                        <tr>
                                            <td class="fw-semibold">{{ strtoupper($opt) }}</td>
                                            <td>
                                                <input type="text" name="option_text[]"
                                                    class="form-control form-control-sm option-text"
                                                    placeholder="Teks opsi {{ strtoupper($opt) }}">
                                            </td>
                                            <td>
                                                <input type="file" name="option_image[]"
                                                    class="form-control form-control-sm" accept="image/*">
                                            </td>
                                            <td>
                                                <input type="text" name="option_url[]"
                                                    class="form-control form-control-sm"
                                                    placeholder="URL gambar (opsional)">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1">Jawaban Benar</label>
                                <select name="mc_answer" id="mc_answer" class="form-select">
                                    <option value="">-- Pilih Jawaban --</option>
                                    @foreach (['a', 'b', 'c', 'd', 'e'] as $opt)
                                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Isian Singkat --}}
                    <div id="opsiIsianSingkat" style="display:none;" class="mt-3">
                        <hr class="my-2">
                        <h6 class="fw-bold text-secondary mb-2">
                            <i class="bi bi-pencil-square me-1"></i> Jawaban Benar (Isian Singkat)
                        </h6>
                        <div id="jawabanContainer" class="d-flex flex-column gap-2">
                            <input type="text" name="sa_answer[]" class="form-control sa-answer"
                                placeholder="Masukkan jawaban singkat">
                        </div>
                        <button type="button" id="tambahJawaban" class="btn btn-outline-secondary btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Tambah Jawaban
                        </button>
                    </div>

                    {{-- Essay / Uraian --}}
                    <div id="opsiEssay" style="display:none;" class="mt-3">
                        <hr class="my-2">
                        <div class="alert alert-info py-2 mb-0 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Soal essay/uraian tidak memerlukan kunci jawaban — jawaban diberikan siswa saat mengerjakan aktivitas.
                        </div>
                    </div>

                    <div class="text-end mt-auto pt-3">
                        <button type="submit" id="submitBtn" class="btn btn-success px-4">
                            <i class="bi bi-save me-1"></i> Simpan Soal
                        </button>
                        <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                        </a>
                    </div>
                </form>
                {{-- FORM END --}}

            </div>
        </div>
    </div>

    {{-- MODAL INFO TAMBAH SOAL --}}
    <div class="modal fade" id="modalInfoTambahSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title mb-0">
                        <i class="bi bi-info-circle me-2"></i> Panduan Menambah Soal
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body small">
                    <ul class="mb-2">
                        <li><b>Pilihan Ganda</b> — opsi A–E dan satu jawaban benar.</li>
                        <li><b>Isian Singkat</b> — satu atau lebih jawaban benar (boleh lebih dari satu variasi).</li>
                        <li><b>Essay / Uraian</b> — dijawab bebas oleh siswa, tanpa kunci jawaban.</li>
                    </ul>
                    <ul class="mb-2">
                        <li><b>Tingkat Kesulitan</b> untuk pengelompokan & sistem adaptive.</li>
                        <li><b>Topik</b> opsional, hanya menampilkan topik dari mapel/kelas yang Anda ajar.</li>
                    </ul>
                    <ul class="mb-0">
                        <li>Teks pertanyaan wajib diisi; gambar soal opsional (upload atau URL).</li>
                        <li>Untuk Pilihan Ganda: semua opsi A–E wajib diisi dan jawaban benar wajib dipilih.</li>
                        <li>Form akan divalidasi otomatis sebelum soal disimpan.</li>
                    </ul>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tipeSoal = document.getElementById('tipeSoal');
            const opsiPG = document.getElementById('opsiPilihanGanda');
            const opsiSA = document.getElementById('opsiIsianSingkat');
            const opsiEssay = document.getElementById('opsiEssay');
            const tambahJawaban = document.getElementById('tambahJawaban');
            const jawabanContainer = document.getElementById('jawabanContainer');
            const questionImageInput = document.getElementById('questionImageInput');
            const previewQuestionImage = document.getElementById('previewQuestionImage');
            const form = document.getElementById('soalForm');
            const submitBtn = document.getElementById('submitBtn');

            // Isi ruang kosong antara card dan footer secara dinamis,
            // tanpa perlu tahu tinggi header/sidebar/footer dari layout.
            function findFooter() {
                return document.querySelector('footer') ||
                    document.querySelector('.footer') ||
                    document.querySelector('[class*="footer"]');
            }

            function adjustWrapHeight() {
                const wrap = document.getElementById('tambahSoalWrap');
                if (!wrap) return;

                // reset dulu supaya pengukuran top tidak terpengaruh min-height sebelumnya
                wrap.style.minHeight = '0px';

                const top = wrap.getBoundingClientRect().top;
                const footer = findFooter();
                const footerHeight = footer ? footer.getBoundingClientRect().height : 0;
                const available = window.innerHeight - top - footerHeight;

                wrap.style.minHeight = Math.max(available, 0) + 'px';
            }

            tipeSoal.addEventListener('change', function() {
                opsiPG.style.display = this.value === 'MultipleChoice' ? 'block' : 'none';
                opsiSA.style.display = this.value === 'ShortAnswer' ? 'block' : 'none';
                opsiEssay.style.display = this.value === 'Essay' ? 'block' : 'none';
                adjustWrapHeight();
            });

            tambahJawaban.addEventListener('click', function() {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'sa_answer[]';
                input.classList.add('form-control', 'sa-answer');
                input.placeholder = 'Masukkan jawaban singkat';
                jawabanContainer.appendChild(input);
                input.focus();
            });

            questionImageInput?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewQuestionImage.innerHTML =
                            `<img src="${event.target.result}" alt="Preview Gambar Soal" class="img-fluid rounded shadow-sm" style="max-height: 160px;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewQuestionImage.innerHTML = '';
                }
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#3b82f6',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '{{ route('tampilanSoal') }}';
                });
            @endif

            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;

                function fail(msg, focusEl) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: msg,
                        confirmButtonColor: '#f87171'
                    }).then(() => {
                        if (focusEl) {
                            focusEl.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            focusEl.focus();
                        }
                    });
                }

                const tipe = tipeSoal.value;
                const questionText = document.getElementById('question_text').value.trim();

                if (!tipe) {
                    return fail('Pilih tipe soal terlebih dahulu.', tipeSoal);
                }

                if (!questionText) {
                    return fail('Teks pertanyaan harus diisi.', document.getElementById('question_text'));
                }

                if (tipe === 'MultipleChoice') {
                    const optionInputs = Array.from(document.querySelectorAll('.option-text'));
                    const labels = ['A', 'B', 'C', 'D', 'E'];

                    for (let i = 0; i < optionInputs.length; i++) {
                        if ((optionInputs[i].value || '').trim() === '') {
                            return fail(`Opsi ${labels[i]} belum diisi!`, optionInputs[i]);
                        }
                    }

                    const mcAnswer = document.getElementById('mc_answer').value;
                    if (!mcAnswer) {
                        return fail('Silakan pilih jawaban benar untuk soal pilihan ganda.', document
                            .getElementById('mc_answer'));
                    }
                } else if (tipe === 'ShortAnswer') {
                    const saInputs = Array.from(document.querySelectorAll('.sa-answer'));
                    const anyFilled = saInputs.some(i => (i.value || '').trim() !== '');
                    if (!anyFilled) {
                        return fail('Masukkan minimal satu jawaban untuk isian singkat.', saInputs[0] ||
                            document.getElementById('question_text'));
                    }
                }

                submitBtn.innerHTML = 'Menyimpan...';
            });

            adjustWrapHeight();
            window.addEventListener('resize', adjustWrapHeight);
            window.addEventListener('load', adjustWrapHeight);
        });
    </script>
@endsection