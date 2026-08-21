@extends('layouts.main')
@section('dataNilai', 'active')

@section('content')

@if(isset($isMode2) && $isMode2)
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-primary mb-3 shadow-sm rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bolder text-dark mb-1"><i class="fas fa-robot text-primary me-2"></i> Hasil Auto-Grading Sistem</h3>
            <p class="text-muted mb-0">
                Aktivitas: <strong class="text-dark">{{ $activity->title }}</strong> |
                Siswa: <strong class="text-primary">{{ $student->name }}</strong>
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Lembar Jawaban Kuis Siswa</h5>
                <span class="badge bg-secondary ms-3 rounded-pill">{{ count($answers) }} Soal Dikerjakan</span>
            </div>

            @forelse($answers as $index => $ans)
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light border-bottom-0 pt-3 pb-2 rounded-top-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-5">Soal #{{ $index + 1 }}</span>
                    <span class="badge {{ $ans->type === 'MultipleChoice' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                        {{ $ans->type === 'MultipleChoice' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4 text-dark fs-6 lh-base bg-light p-3 rounded border">
                        {!! json_decode($ans->soal_teks)->text ?? 'Teks soal tidak tersedia' !!}
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-success small mb-1"><i class="fas fa-key me-1"></i> Kunci Sistem</label>
                            <div class="p-2 border border-success rounded bg-success-subtle text-success-emphasis fw-semibold">
                                @if($ans->type === 'MultipleChoice')
                                    {{ strtoupper($ans->MC_answer) }}
                                @else
                                    @php
                                        $saKeys = is_string($ans->SA_answer) ? json_decode($ans->SA_answer, true) : $ans->SA_answer;
                                        echo is_array($saKeys) ? implode(' / ', $saKeys) : $ans->SA_answer;
                                    @endphp
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-primary small mb-1"><i class="fas fa-pencil-alt me-1"></i> Jawaban Siswa</label>
                            <div class="p-2 border border-primary rounded bg-primary-subtle text-primary-emphasis fw-semibold">
                                {{ $ans->user_answer ?? '(Kosong)' }}
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    
                    <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded">
                        <span class="fw-bold text-dark">Hasil Koreksi Sistem:</span>
                        <div>
                            @if($ans->is_correct)
                                <span class="badge bg-success fs-6 px-4 py-2 shadow-sm"><i class="fas fa-check-circle me-1"></i> Benar</span>
                            @else
                                <span class="badge bg-danger fs-6 px-4 py-2 shadow-sm"><i class="fas fa-times-circle me-1"></i> Salah</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="alert alert-info text-center py-4">Siswa ini belum menjawab soal apa pun.</div>
            @endforelse
        </div>

        <div class="col-lg-4">
            <div class="position-sticky" style="top: 20px;">
                <div class="card shadow border-primary border-2 rounded-4">
                    <div class="card-header bg-primary text-white pt-3 pb-2 rounded-top-4">
                        <h5 class="fw-bold mb-0 text-center"><i class="fas fa-award me-2"></i> Hasil Akhir Kuis</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <label class="form-label fw-bolder text-dark mb-3">Nilai Murni (Auto-Graded)</label>
                            <h1 class="display-2 fw-bolder text-primary mb-0">{{ $result->result ?? 0 }}</h1>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between text-muted small px-2">
                            <span>Total Jawaban Benar:</span>
                            <strong class="text-dark">{{ $result->total_benar ?? 0 }} Soal</strong>
                        </div>
                        <div class="alert alert-info mt-4 small mb-0 text-start">
                            <i class="fas fa-info-circle me-1"></i> Nilai kuis ini dinilai secara otomatis oleh sistem. Nilai akan dikalikan dengan <strong>Indeks SCI</strong> untuk mendapatkan Nilai Akhir siswa.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="container py-4">
    {{-- HEADER HALAMAN & TOMBOL AKSI --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="javascript:history.back()" class="btn btn-outline-primary mb-3 shadow-sm rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                Pemeriksaan Uraian Kelompok
            </h3>
            <p class="text-muted mb-0 mt-1">
                Aktivitas: <strong class="text-primary">{{ $activity->title ?? 'Aktivitas' }}</strong> |
                Nama Kelompok: <strong>{{ $group->name }}</strong>
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-lg fw-bold shadow px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalBeriNilai">
                <i class="fas fa-check-circle me-2"></i> Beri Nilai Kelompok
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-9">
            @php
            $groupedAnswers = collect($answers)->groupBy(function($ans) {
                return json_decode($ans->soal)->text ?? 'Soal tidak terbaca';
            });
            $nomorSoal = 1;
            @endphp

            @forelse($groupedAnswers as $soalText => $userAnswers)
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-header bg-white pt-4 pb-3 border-bottom rounded-top-4">
                    <h5 class="fw-bold text-primary mb-0"> Soal {{ $nomorSoal++ }} </h5>
                </div>
                <div class="card-body p-4">
                    <p class="fw-bold mb-4 text-dark fs-5 lh-base border-bottom pb-3">{{ $soalText }}</p>
                    <h6 class="fw-bold text-secondary mb-3 small text-uppercase"><i class="fas fa-tasks me-1"></i> Pemeriksaan Jawaban Anggota:</h6>
                    
                    <div class="row g-3">
                        @foreach($userAnswers as $index => $ans)
                        <div class="col-md-6">
                            <div class="card border border-light shadow-sm h-100 bg-light rounded-4 peer-answer-card transition-all" id="card_ans_{{ $nomorSoal }}_{{ $index }}">
                                <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info text-dark shadow-sm px-3 py-2">
                                        <i class="fas fa-user-edit me-1"></i> {{ $ans->penjawab }}
                                    </span>
                                    
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input check-diperiksa" type="checkbox" role="switch" style="cursor: pointer;"
                                            id="check_{{ $nomorSoal }}_{{ $index }}" 
                                            onchange="toggleDiperiksa('card_ans_{{ $nomorSoal }}_{{ $index }}', this)">
                                        <label class="form-check-label small fw-bold text-muted" for="check_{{ $nomorSoal }}_{{ $index }}" style="cursor: pointer;">Diperiksa</label>
                                    </div>
                                </div>
                                <div class="card-body pt-3 pb-3">
                                    <div class="text-dark bg-white p-3 rounded-3 border shadow-sm" style="font-size: 0.95rem; min-height: 80px;">
                                        {!! nl2br(e($ans->answer)) !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white shadow-sm rounded-4">
                <i class="fas fa-folder-open display-4 text-muted mb-3 opacity-50"></i>
                <h5 class="fw-bold text-secondary">Belum Ada Jawaban</h5>
            </div>
            @endforelse
        </div>


        {{-- ========================= --}}
        {{-- PEER REVIEW --}}
        {{-- ========================= --}}
        <div class="col-lg-3">
            <div class="position-sticky" style="top: 20px;">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-bottom pt-4 pb-3 rounded-top-4">
                        <h5 class="fw-bold mb-0 text-info text-center"><i class="fas fa-star text-warning me-1"></i> Hasil Peer Review</h5>
                    </div>
                    <div class="card-body p-3">
                        @forelse($ratings as $rating)
                        @php $isSelfRating = $rating->id_evaluator == $rating->id_evaluated; @endphp
                        <div class="mb-3 p-3 border rounded-3 bg-light shadow-sm d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark small">{{ $rating->evaluator_name }}</span>
                                <i class="fas fa-arrow-right text-muted small"></i>
                                @if($isSelfRating)
                                <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-user-check"></i> Sendiri</span>
                                @else
                                <span class="fw-bold text-primary small">{{ $rating->evaluated_name }}</span>
                                @endif
                            </div>
                            <div class="text-center mt-1">
                                <span class="badge bg-warning text-dark px-3 py-2 w-100 fs-6 shadow-sm">Beri Nilai: {{ $rating->score }} Poin</span>
                            </div>
                            @if(!empty($rating->comment))
                            <div class="mt-1 p-2 bg-white rounded border small text-secondary fst-italic">"{{ $rating->comment }}"</div>
                            @endif

                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-muted small mb-0">Belum ada penilaian SCI.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modalBeriNilai" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-success text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i> Pengesahan Nilai Kelompok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.penilaian.simpan', ['activity' => $activity->id, 'group' => $group->id]) }}" method="POST">
                @csrf


                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-4">
                        <i class="fas fa-info-circle me-1"></i> Berikan satu nilai rata-rata untuk keseluruhan pekerjaan kelompok ini. Sistem akan memecah nilai ini menggunakan Indeks SCI siswa.
                    </div>
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold text-dark mb-2">Nilai Mentah Kelompok (0 - 100)</label>
                        <input type="number" name="nilai_kelompok" class="form-control form-control-lg border-success text-center fw-bold fs-3 text-success shadow-sm" required min="0" max="100" placeholder="0">
                    </div>


                    <div class="alert alert-light border small mb-0">

                        <i class="fas fa-info-circle text-info me-1"></i>

                        Nilai ini menjadi
                        <strong>nilai dasar kelompok</strong>.
                        Sistem akan menggunakan
                        <strong>Indeks SCI</strong>
                        masing-masing siswa berdasarkan
                        peer review untuk menentukan nilai akhir individu.

                    </div>

                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">Sahkan & Simpan Nilai</button>
                </div>

            </form>
        </div>

    </div>

</div>

<script>
    function toggleDiperiksa(cardId, checkbox) {
        const card = document.getElementById(cardId);
        const label = checkbox.nextElementSibling;
        
        if (checkbox.checked) {
            // Ubah tampilan menjadi hijau (sudah diperiksa)
            card.classList.remove('bg-light', 'border-light');
            card.classList.add('bg-success-subtle', 'border-success');
            label.classList.remove('text-muted');
            label.classList.add('text-success');
            label.innerHTML = '<i class="fas fa-check-double me-1"></i> Oke';
        } else {
            // Kembalikan ke tampilan awal
            card.classList.remove('bg-success-subtle', 'border-success');
            card.classList.add('bg-light', 'border-light');
            label.classList.remove('text-success');
            label.classList.add('text-muted');
            label.innerHTML = 'Diperiksa';
        }
    }
</script>
@endif

@endsection