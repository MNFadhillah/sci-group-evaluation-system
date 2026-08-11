@extends('layouts.main')

@section('content')
<div class="container py-4">
    <!-- Header Aktivitas -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('data.nilai') }}" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h2 class="fw-bold">Detail Jawaban Kelompok</h2>
            <p class="text-muted">Aktivitas: <strong>{{ $activity->title }}</strong></p>
            <hr>
        </div>
    </div>

    <!-- Daftar Kelompok -->
    <div class="row">
        @forelse($groups as $group)

        <div class="col-md-4 mb-4">
            <!-- Tambahan border-success jika sudah dinilai -->
            <div class="card shadow-sm h-100 border-0 {{ $group->is_graded ? 'border-success border border-2' : '' }}">
                <div class="card-header bg-white d-flex justify-content-between align-items-center pt-3 pb-2 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">
                        {{ $group->name }}
                    </h5>

                    <!-- Perubahan Badge jika sudah dinilai -->
                    @if($group->is_graded)
                    <div class="d-flex gap-1">
                        <span class="badge bg-primary shadow-sm px-2 py-1" title="Nilai Kelompok yang Diberikan Guru">
                            Nilai: {{ $group->nilai_kelompok }}
                        </span>
                        <span class="badge bg-success shadow-sm px-2 py-1">
                            <i class="fas fa-check-double me-1"></i> Dinilai
                        </span>
                    </div>
                    @else
                    <span class="badge bg-light text-dark border">
                        {{ $group->submitted_count }} / {{ $group->total_members }} Selesai
                    </span>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Progress Bar Kelompok -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progress Kelompok</small>
                            <small class="fw-bold">{{ $group->progress_percentage }}%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $group->progress_percentage == 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped {{ $group->progress_percentage < 100 ? 'progress-bar-animated' : '' }}"
                                role="progressbar"
                                @style(["width: {$group->progress_percentage}%"])
                                aria-valuenow="{{ $group->progress_percentage }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Status Anggota -->
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($group->members as $member)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
                            <span class="text-secondary">
                                {{ $member->user->name ?? 'User Tidak Diketahui' }}
                            </span>

                            @if($member->has_submitted)
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                Telah Submit
                            </span>
                            @else
                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                Belum Mengerjakan
                            </span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Tombol Aksi -->
                <div class="card-footer bg-white border-top-0 pb-3 pt-0">
                    <div class="d-grid gap-2">

                        <!-- Tombol Aksi Selalu Muncul (Berubah warna/teks jika sudah dinilai) -->
                        <div class="d-grid">
                            <a href="{{ route('guru.penilaian.form', ['activity' => $activity->id, 'group' => $group->id]) }}"
                                class="btn {{ $group->is_graded ? 'btn-outline-success' : 'btn-success' }} fw-bold shadow-sm">
                                @if($group->is_graded)
                                <i class="fas fa-edit me-2"></i> Edit Penilaian
                                @else
                                <i class="fas fa-star me-2"></i> Lihat Jawaban & Beri Nilai
                                @endif
                            </a>
                        </div>

                        <!-- Keterangan Status Progres di Bawah Tombol -->
                        <div class="text-center mt-2">
                            @if($group->progress_percentage == 100)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                                Progres Selesai (100%)
                            </span>
                            @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.75rem;">
                                Progres Belum Selesai ({{ $group->progress_percentage }}%)
                            </span>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                Belum ada kelompok yang dibentuk untuk aktivitas ini.
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection