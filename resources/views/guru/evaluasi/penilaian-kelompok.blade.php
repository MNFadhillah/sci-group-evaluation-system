@extends('layouts.main')

@section('content')
<div class="container py-4">

    {{-- HEADER HALAMAN & TOMBOL AKSI --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="javascript:history.back()" class="btn btn-outline-primary mb-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                Penilaian Kelompok
            </h3>
            <p class="text-muted mb-0 mt-1">
                Aktivitas: <strong class="text-primary">{{ $activity->title ?? 'Aktivitas' }}</strong> | 
                Nama Kelompok: <strong>{{ $group->name }}</strong>
            </p>
        </div>

        {{-- Tombol Buka Modal Penilaian --}}
        <div>
            <button type="button" class="btn btn-success fw-bold shadow px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalBeriNilai">
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
                <div class="card shadow-sm border-1 mb-2 rounded-2">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-1" style="border-radius: 5px 5px 0 0">
                        <h5 class="fw-bold mb-0"> Soal {{ $nomorSoal++ }}
                        </h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Teks Soal -->
                        <p class="fw-bold mb-4 text-dark fs-5 lh-base border-bottom pb-3">
                            {{ $soalText }}
                        </p>

                        <!-- Area Jawaban Anggota -->
                        <h6 class="fw-bold text-secondary mb-3 small text-uppercase">Jawaban Anggota Kelompok:</h6>
                        
                        <div class="row g-3">
                            @foreach($userAnswers as $ans)
                            <div class="col-md-6">
                                <div class="card border border-light shadow-sm h-100 bg-light rounded-3">
                                    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
                                        <span class="badge bg-info text-dark shadow-sm px-3 py-2" style="font-size: 0.8rem;">
                                            <i class="fas fa-user-edit me-1"></i> {{ $ans->penjawab }}
                                        </span>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="text-dark bg-white p-3 rounded border border-light shadow-sm" style="font-size: 0.95rem;">
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
                    <p class="text-muted">Kelompok ini belum mengirimkan jawaban apa pun.</p>
                </div>
            @endforelse

        </div>

        <div class="col-lg-3">
            <div class="card shadow-sm border-1 rounded-2">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h5 class="fw-bold mb-0 text-info" style="font-size: 1.1rem;">Peer Review
                    </h5>
                </div>
                <div class="card-body p-3">
                    @forelse($ratings as $rating)
                        @php
                            $isSelfRating = $rating->id_evaluator == $rating->id_evaluated;
                        @endphp
                        
                        <div class="mb-3 p-3 border rounded-3 bg-light border-0 shadow-sm d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $rating->evaluator_name }}</span>
                                
                                @if($isSelfRating)
                                    <span class="badge bg-success-subtle text-success px-2 py-1 border border-success-subtle" style="font-size: 0.7rem;">
                                        <i class="fas fa-user-check"></i> Diri Sendiri
                                    </span>
                                @else
                                    <i class="fas fa-arrow-right text-muted" style="font-size: 0.7rem;"></i>
                                    <span class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $rating->evaluated_name }}</span>
                                @endif
                            </div>
                            
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-warning text-dark px-2 py-1 border border-warning shadow-sm" style="font-size: 0.75rem;">
                                    Nilai : {{ $rating->score }} Poin
                                </span>
                            </div>

                            @if(!empty($rating->comment))
                                <div class="mt-2 p-2 bg-white rounded border border-light small text-secondary fst-italic" style="font-size: 0.8rem;">
                                    "{{ $rating->comment }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-user-clock fs-1 text-muted mb-2 opacity-50"></i>
                            <p class="text-muted small mb-0">Belum ada penilaian.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================== -->
<!-- MODAL FORM PENILAIAN GURU & KALKULASI SCI -->
<!-- ============================================== -->
<div class="modal fade" id="modalBeriNilai" tabindex="-1" aria-labelledby="modalBeriNilaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header bg-success text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalBeriNilaiLabel">
                    <i class="fas fa-check-circle me-2"></i> Nilai Kelompok
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Form Mengarah ke Controller Kalkulasi --}}
            <form action="{{ route('guru.penilaian.simpan', ['activity' => $activity->id, 'group' => $group->id]) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h6 class="fw-bold text-dark mb-1">Kelompok: {{ $group->name }}</h6>
                        <p class="text-muted small">Pastikan Anda telah membaca lembar jawaban sebelum menilai.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nilai Mentah Kelompok (0 - 100)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-success"><i class="fas fa-edit text-success"></i></span>
                            <input type="number" name="nilai_kelompok" class="form-control border-success @error('nilai_kelompok') is-invalid @enderror" required min="0" max="100" >
                        </div>
                        @error('nilai_kelompok')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="form-text text-muted mt-3 lh-sm p-3 bg-light rounded border border-light">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            Nilai ini adalah <strong>nilai dasar kelompok</strong>. Sistem akan otomatis mengalikan nilai ini dengan <strong>Indeks SCI</strong> masing-masing siswa (berdasarkan <em>peer review</em>) untuk menjadi Nilai Akhir Individu.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 rounded-bottom-4 justify-content-between">
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">
                        <i class="fas fa-calculator me-1"></i> Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection