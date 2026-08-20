@extends('layouts.main')

@section('content')

<style>
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .page-subtitle {
        font-size: .9rem;
        color: #6c757d;
    }

    .question-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .question-header {
        background: #f8f9fa;
        padding: 10px 16px;
        border-bottom: 1px solid #e9ecef;
    }

    .question-body {
        padding: 16px;
    }

    .question-text {
        font-size: .95rem;
        line-height: 1.6;
        color: #212529;
        margin-bottom: 14px;
    }

    .member-answer {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 12px;
        height: 100%;
    }

    .member-name {
        font-size: .82rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 7px;
    }

    .answer-text {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 9px 10px;
        font-size: .88rem;
        line-height: 1.5;
        color: #343a40;
    }

    .section-label {
        font-size: .78rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .review-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 11px;
        background: #f8f9fa;
    }

    .review-name {
        font-size: .82rem;
        font-weight: 600;
    }

    .review-target {
        font-size: .8rem;
        color: #0d6efd;
        font-weight: 600;
    }

    .review-score {
        font-size: .75rem;
    }

    .review-comment {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 7px 9px;
        font-size: .78rem;
        color: #6c757d;
    }

    .empty-state {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        background: #fff;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
    }
</style>

<div class="container-fluid px-3 px-md-4 py-3">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">

        <div>

            <div class="d-flex align-items-center gap-2 mb-1">

                <a href="javascript:history.back()"
                    class="btn btn-sm btn-light border"
                    title="Kembali">

                    <i class="fas fa-arrow-left"></i>

                </a>

                <h3 class="page-title mb-0">
                    Penilaian Kelompok
                </h3>

            </div>

            <div class="page-subtitle">
                Aktivitas:
                <strong class="text-primary">
                    {{ $activity->title ?? 'Aktivitas' }}
                </strong>

                <span class="mx-1">•</span>

                Kelompok:
                <strong>
                    {{ $group->name }}
                </strong>
            </div>

        </div>

        {{-- AKSI UTAMA --}}
        <button
            type="button"
            class="btn btn-success fw-semibold px-3"
            data-bs-toggle="modal"
            data-bs-target="#modalBeriNilai">

            <i class="fas fa-check-circle me-1"></i>
            Beri Nilai Kelompok

        </button>

    </div>


    {{-- CONTENT --}}
    <div class="row g-3">

        {{-- ========================= --}}
        {{-- JAWABAN KELOMPOK --}}
        {{-- ========================= --}}
        <div class="col-lg-9">

            @php
                $groupedAnswers = collect($answers)->groupBy(function($ans) {
                    return json_decode($ans->soal)->text ?? 'Soal tidak terbaca';
                });

                $nomorSoal = 1;
            @endphp


            @forelse($groupedAnswers as $soalText => $userAnswers)

                <div class="question-card mb-3 shadow-sm">

                    {{-- HEADER SOAL --}}
                    <div class="question-header d-flex justify-content-between align-items-center">

                        <span class="fw-semibold">
                            Soal {{ $nomorSoal++ }}
                        </span>

                        <span class="badge bg-light text-secondary border">
                            {{ $userAnswers->count() }} jawaban
                        </span>

                    </div>


                    <div class="question-body">

                        {{-- PERTANYAAN --}}
                        <div class="question-text">
                            {{ $soalText }}
                        </div>


                        {{-- JAWABAN ANGGOTA --}}
                        <div class="section-label mb-2">
                            Jawaban Anggota
                        </div>


                        <div class="row g-2">

                            @foreach($userAnswers as $ans)

                                <div class="col-md-6">

                                    <div class="member-answer">

                                        <div class="member-name">

                                            <i class="fas fa-user me-1 text-secondary"></i>

                                            {{ $ans->penjawab }}

                                        </div>

                                        <div class="answer-text">
                                            {!! nl2br(e($ans->answer)) !!}
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state text-center py-5">

                    <i class="fas fa-folder-open fs-1 text-muted mb-3"></i>

                    <h6 class="fw-semibold text-secondary">
                        Belum Ada Jawaban
                    </h6>

                    <p class="text-muted small mb-0">
                        Kelompok ini belum mengirimkan jawaban apa pun.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- ========================= --}}
        {{-- PEER REVIEW --}}
        {{-- ========================= --}}
        <div class="col-lg-3">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <h6 class="fw-semibold mb-0">
                            <i class="fas fa-users text-info me-1"></i>
                            Peer Review
                        </h6>

                        <span class="badge bg-light text-secondary border">
                            {{ $ratings->count() }}
                        </span>

                    </div>

                </div>


                <div class="card-body p-2">

                    @forelse($ratings as $rating)

                        @php
                            $isSelfRating = $rating->id_evaluator == $rating->id_evaluated;
                        @endphp


                        <div class="review-card mb-2">

                            {{-- NAMA --}}
                            <div class="d-flex justify-content-between align-items-center">

                                <span class="review-name">
                                    {{ $rating->evaluator_name }}
                                </span>

                                @if($isSelfRating)

                                    <span class="badge bg-success-subtle text-success review-score">
                                        Diri Sendiri
                                    </span>

                                @else

                                    <span class="review-target">
                                        <i class="fas fa-arrow-right text-muted me-1"></i>
                                        {{ $rating->evaluated_name }}
                                    </span>

                                @endif

                            </div>


                            {{-- NILAI --}}
                            <div class="mt-2">

                                <span class="badge bg-warning text-dark review-score">
                                    Nilai: {{ $rating->score }}
                                </span>

                            </div>


                            {{-- KOMENTAR --}}
                            @if(!empty($rating->comment))

                                <div class="review-comment mt-2">
                                    "{{ $rating->comment }}"
                                </div>

                            @endif

                        </div>

                    @empty

                        <div class="text-center py-4">

                            <i class="fas fa-user-clock fs-3 text-muted mb-2"></i>

                            <p class="text-muted small mb-0">
                                Belum ada penilaian.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ============================================== --}}
{{-- MODAL PENILAIAN GURU --}}
{{-- ============================================== --}}

<div
    class="modal fade"
    id="modalBeriNilai"
    tabindex="-1"
    aria-labelledby="modalBeriNilaiLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title fw-semibold" id="modalBeriNilaiLabel">

                    <i class="fas fa-check-circle me-2"></i>
                    Nilai Kelompok

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>


            <form
                action="{{ route('guru.penilaian.simpan', ['activity' => $activity->id, 'group' => $group->id]) }}"
                method="POST">

                @csrf


                <div class="modal-body p-4">

                    <div class="mb-3">

                        <div class="fw-semibold">
                            {{ $group->name }}
                        </div>

                        <div class="small text-muted">
                            Masukkan nilai dasar kelompok dari 0–100.
                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Nilai Kelompok
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light">
                                <i class="fas fa-edit text-success"></i>
                            </span>

                            <input
                                type="number"
                                name="nilai_kelompok"
                                class="form-control @error('nilai_kelompok') is-invalid @enderror"
                                required
                                min="0"
                                max="100"
                                placeholder="0 - 100">

                        </div>

                        @error('nilai_kelompok')

                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>

                        @enderror

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


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        class="btn btn-success fw-semibold">

                        <i class="fas fa-save me-1"></i>
                        Simpan Nilai

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection