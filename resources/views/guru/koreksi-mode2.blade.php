@extends('layouts.main')
@section('dataNilai', 'active')

@section('content')
<div class="container py-4">
    <!-- Header Informasi -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="{{ route('detail.nilai', $activity->id) }}" class="btn btn-sm btn-outline-primary mb-3 shadow-sm rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Rekap
            </a>
            <h3 class="fw-bolder text-dark mb-1"><i class="fas fa-user-edit text-primary me-2"></i> Koreksi Manual Siswa</h3>
            <p class="text-muted mb-0">
                Aktivitas: <strong class="text-dark">{{ $activity->title }}</strong> | 
                Siswa: <strong class="text-primary">{{ $student->name }}</strong>
            </p>
        </div>
    </div>

    <!-- Form Koreksi -->
    <form action="{{ route('guru.koreksi.simpan', ['idActivity' => $activity->id, 'idUser' => $student->id]) }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Sisi Kiri: Daftar Jawaban -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">Lembar Jawaban Siswa</h5>
                    <span class="badge bg-secondary ms-3 rounded-pill">{{ $answers->count() }} Soal Dikerjakan</span>
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
                        <!-- Teks Soal -->
                        <div class="mb-4 text-dark fs-6 lh-base bg-light p-3 rounded border">
                            {!! json_decode($ans->soal_teks)->text ?? 'Teks soal tidak tersedia' !!}
                        </div>

                        <div class="row g-3">
                            <!-- Kunci Jawaban -->
                            <div class="col-md-6">
                                <label class="fw-bold text-success small mb-1"><i class="fas fa-key me-1"></i> Kunci Jawaban Sistem</label>
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
                            
                            <!-- Jawaban Siswa -->
                            <div class="col-md-6">
                                <label class="fw-bold text-primary small mb-1"><i class="fas fa-pencil-alt me-1"></i> Jawaban Siswa</label>
                                <div class="p-2 border border-primary rounded bg-primary-subtle text-primary-emphasis fw-semibold">
                                    {{ $ans->user_answer ?? '(Kosong)' }}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Keputusan Benar/Salah -->
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded">
                            <span class="fw-bold text-dark">Keputusan Guru:</span>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="answers[{{ $ans->answer_id }}][is_correct]" id="correct_{{ $ans->answer_id }}" value="1" {{ $ans->is_correct ? 'checked' : '' }}>
                                <label class="btn btn-outline-success fw-bold px-4" for="correct_{{ $ans->answer_id }}"><i class="fas fa-check me-1"></i> Benar</label>

                                <input type="radio" class="btn-check" name="answers[{{ $ans->answer_id }}][is_correct]" id="wrong_{{ $ans->answer_id }}" value="0" {{ !$ans->is_correct ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger fw-bold px-4" for="wrong_{{ $ans->answer_id }}"><i class="fas fa-times me-1"></i> Salah</label>
                            </div>
                        </div>
                        @if($ans->type === 'ShortAnswer')
                        <div class="form-text text-muted mt-2 small">
                            * Jika siswa typo sedikit pada isian singkat dan Anda ingin membenarkannya, ubah ke "Benar".
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="alert alert-info border-0 shadow-sm rounded-4 text-center py-4">
                    Siswa ini belum menjawab soal apa pun.
                </div>
                @endforelse
            </div>

            <!-- Sisi Kanan: Panel Nilai Akhir (Sticky) -->
            <div class="col-lg-4">
                <div class="position-sticky" style="top: 20px;">
                    <div class="card shadow border-primary border-2 rounded-4">
                        <div class="card-header bg-primary text-white pt-3 pb-2 rounded-top-4">
                            <h5 class="fw-bold mb-0 text-center"><i class="fas fa-award me-2"></i> Pengesahan Nilai</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Info Pengerjaan -->
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                                <span class="text-muted small">Waktu Pengerjaan</span>
                                <span class="fw-bold text-dark">{{ $result ? floor($result->waktu_mengerjakan / 60) . ' mnt ' . ($result->waktu_mengerjakan % 60) . ' dtk' : '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-4">
                                <span class="text-muted small">Jawaban Benar (Otomatis)</span>
                                <span class="fw-bold text-success">{{ $result->total_benar ?? 0 }} Soal</span>
                            </div>

                            <!-- Input Nilai Akhir -->
                            <div class="mb-4">
                                <label class="form-label fw-bolder text-dark">Nilai Akhir Siswa (0-100)</label>
                                <input type="number" step="0.01" min="0" max="100" name="nilai_akhir" class="form-control form-control-lg border-primary text-center fw-bold text-primary fs-3 shadow-sm" required value="{{ old('nilai_akhir', $result->nilai_akhir ?? 0) }}">
                                <div class="form-text text-center mt-2 small text-muted">
                                    Anda bebas menyesuaikan nilai ini berdasarkan hasil koreksi manual di samping.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow">
                                <i class="fas fa-save me-2"></i> Sahkan & Simpan Nilai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection