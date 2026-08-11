@extends('layouts.main')
@section('dataNilai', 'active')

@section('head')
<style>
    /* ===== CARD & LAYOUT ===== */
    .page-header {
        margin-bottom: 2rem;
    }

    .class-card {
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .class-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(0, 0, 0, .1);
    }

    .class-card .card-body {
        padding: 1.5rem;
    }

    /* ===== TEXT ===== */
    .muted-small {
        font-size: .85rem;
        color: #6c757d;
    }

    .subject-badge {
        font-size: .8rem;
        margin-right: .4rem;
    }

    .topic-title {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: .75rem;
    }

    /* ===== GROUPING ===== */
    .subject-block {
        margin-bottom: 1.5rem;
    }

    .topic-block {
        margin-bottom: 1rem;
    }

    /* ===== ACTIVITY ===== */
    .activity-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        /* Agar elemen turun ke bawah di layar kecil */
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: 10px;
        background: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }

    .activity-row+.activity-row {
        margin-top: .75rem;
    }

    .activity-row:hover {
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
        transform: translateX(3px);
        /* Efek geser kanan sedikit saat disorot */
    }

    .activity-title {
        font-weight: 600;
        color: #34495e;
        line-height: 1.4;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        /* Tombol aman di layar kecil */
    }

    /* ===== SCROLL AREA ===== */
    .card-scroll {
        max-height: 450px;
        overflow-y: auto;
        padding-right: .5rem;
    }

    /* Modifikasi Scrollbar agar rapi */
    .card-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .card-scroll::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }

    /* ===== BUTTON ===== */
    .btn-sm-custom {
        padding: .4rem .75rem;
        white-space: nowrap;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold mb-0 text-gray-800">Data Nilai & Evaluasi</h1>

                <button type="button"
                    class="btn btn-sm btn-outline-info rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                    style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoDataNilai"
                    title="Informasi Data Nilai">
                    <i class="bi bi-info-lg fw-bold"></i>
                </button>
            </div>

            <div class="text-muted">
                Pantau progres kelompok dan hasil nilai siswa dari kelas yang Anda ampu.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('guru.datanilai.export') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-1"></i> Export Semua Kelas
            </a>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary shadow-sm" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </a>
        </div>
    </div>

    {{-- CONTENT --}}
    @if ($grouped->isEmpty())
    <div class="alert alert-info shadow-sm rounded-3 border-0">
        <i class="fas fa-info-circle me-2"></i> Belum ada kelas atau aktivitas untuk Anda.
    </div>
    @else
    <div class="row g-4">
        @foreach($grouped as $class)
        {{-- Menggunakan col-12 agar card kelas melebar secara optimal untuk layout 2 kolom --}}
        <div class="col-12">
            <div class="card class-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">

                    {{-- HEADER KELAS --}}
                    <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3 flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary">{{ $class['class_name'] }}</h4>
                            <div class="text-secondary">
                                <i class="fas fa-users me-1"></i> Siswa: <strong>{{ count($class['students'] ?? []) }} orang</strong>
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm mb-2">
                                {{ collect($class['subjects'])->sum(function ($s) {
                                        return collect($s['topics'])->sum(fn($t) => count($t['activities']));
                                    }) }} Total Aktivitas
                            </span>
                            <br>
                            <a href="{{ route('guru.datanilai.exportClass', $class['class_id']) }}"
                                class="btn btn-outline-success btn-sm fw-bold shadow-sm">
                                <i class="fas fa-download"></i> Unduh Rekap Kelas
                            </a>
                        </div>
                    </div>

                    {{-- BODY (Mata Pelajaran, Topik, & Kolom Terpisah) --}}
                    <div class="card-scroll pe-2">
                        @forelse ($class['subjects'] as $subject)
                        <div class="subject-block mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info text-dark subject-badge px-3 py-1 shadow-sm fw-bold">
                                    {{ $subject['name'] }}
                                </span>
                                <small class="text-muted fw-semibold ms-2">
                                    ( {{ count($subject['topics']) }} Topik )
                                </small>
                            </div>

                            @foreach ($subject['topics'] as $topic)
                            <div class="topic-block ps-3 border-start border-3 border-info mb-4">
                                <div class="topic-title fw-bold text-dark fs-6 mb-3">
                                    <i class="fas fa-book-open text-info me-1"></i> {{ $topic['title'] }}
                                </div>

                                @php
                                // Ubah aktivitas menjadi Collection untuk memudahkan pemisahan data
                                $activities = collect($topic['activities']);
                                $individualActivities = $activities->where('is_group_activity', '!=', 'yes');
                                $groupActivities = $activities->where('is_group_activity', 'yes');
                                @endphp

                                @if($activities->isEmpty())
                                <div class="text-muted small fst-italic">
                                    Belum ada aktivitas pada topik ini.
                                </div>
                                @else
                                {{-- STRUKTUR 2 KOLOM: Kiri Individu, Kanan Kelompok --}}
                                <div class="row g-3">

                                    {{-- KOLOM KIRI: AKTIVITAS INDIVIDU --}}
                                    <div class="col-12 col-lg-6">
                                        <div class="p-3 rounded-3 bg-light border h-100">
                                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                                <span class="badge bg-secondary text-white rounded-pill px-2 py-1">
                                                    <i class="fas fa-user me-1"></i> Aktivitas Individu
                                                </span>
                                            </div>

                                            <div class="d-flex flex-column gap-2">
                                                @forelse ($individualActivities as $act)
                                                @php
                                                $cnt = $act['results_count'] ?? 0;
                                                $badgeClass = $cnt > 0 ? 'bg-success' : 'bg-secondary';
                                                @endphp
                                                <div class="activity-row p-3 bg-white rounded-2 border shadow-sm">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                        <div class="activity-title text-dark fw-semibold" title="{{ $act['title'] }}">
                                                            <i class="fas fa-tasks text-primary me-2"></i> {{ $act['title'] }}
                                                        </div>
                                                        <div class="action-buttons d-flex align-items-center gap-2">
                                                            <span class="badge {{ $badgeClass }} px-2 py-1 shadow-sm">
                                                                {{ $cnt }} Dinilai
                                                            </span>
                                                            <a href="{{ route('detail.nilai', $act['id']) }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                                                                <i class="fas fa-eye"></i> Nilai Akhir
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @empty
                                                <div class="text-muted small fst-italic py-3 text-center bg-white rounded-2 border border-dashed">
                                                    Tidak ada aktivitas individu.
                                                </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    {{-- KOLOM KANAN: AKTIVITAS KELOMPOK --}}
                                    <div class="col-12 col-lg-6">
                                        <div class="p-3 rounded-3 bg-light border h-100">
                                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                                <span class="badge bg-primary text-white rounded-pill px-2 py-1">
                                                    <i class="fas fa-users me-1"></i> Aktivitas Kelompok
                                                </span>
                                            </div>

                                            <div class="d-flex flex-column gap-2">
                                                @forelse ($groupActivities as $act)
                                                @php
                                                $cnt = $act['results_count'] ?? 0;
                                                $badgeClass = $cnt > 0 ? 'bg-success' : 'bg-secondary';
                                                @endphp
                                                <div class="activity-row p-3 bg-white rounded-2 border shadow-sm">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                        <div class="activity-title text-dark fw-semibold" title="{{ $act['title'] }}">
                                                            <i class="fas fa-tasks text-primary me-2"></i> {{ $act['title'] }}
                                                        </div>
                                                        <div class="action-buttons d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="badge {{ $badgeClass }} px-2 py-1 shadow-sm">
                                                                {{ $cnt }} Dinilai
                                                            </span>
                                                            <a href="{{ route('guru.monitoring', $act['id']) }}" class="btn btn-warning btn-sm fw-bold shadow-sm text-dark">
                                                                <i class="bi bi-people-fill me-1"></i> Nilai Kelompok
                                                            </a>
                                                            <a href="{{ route('detail.nilai', $act['id']) }}" class="btn btn-primary btn-sm fw-bold shadow-sm">
                                                                <i class="fas fa-eye"></i> Nilai Akhir
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @empty
                                                <div class="text-muted small fst-italic py-3 text-center bg-white rounded-2 border border-dashed">
                                                    Tidak ada aktivitas kelompok.
                                                </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @empty
                        <div class="text-muted small">
                            Tidak ada mata pelajaran untuk kelas ini.
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- MODAL INFO --}}
<div class="modal fade" id="modalInfoDataNilai" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i> Informasi Data Nilai & Evaluasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Halaman <strong>Data Nilai</strong> digunakan untuk memantau pengerjaan kelompok dan mengelola hasil penilaian siswa dari berbagai <strong>kelas</strong> yang Anda ampu.
                </p>
                <hr class="my-3">
                <h6 class="fw-bold text-warning mb-2">
                    <i class="bi bi-eye me-1"></i> Fungsi Tombol Aksi
                </h6>
                <ul class="mb-3">
                    <li>
                        <button class="btn btn-warning btn-sm py-0 px-2 fw-bold text-dark me-1" disabled><i class="fas fa-satellite-dish"></i> Monitoring</button>
                        Hanya tersedia pada <strong>Aktivitas Kelompok</strong>. Digunakan untuk memantau progres jawaban kelompok secara <em>real-time</em> dan memberikan nilai proyek kelompok.
                    </li>
                    <li class="mt-2">
                        <button class="btn btn-primary btn-sm py-0 px-2 fw-bold me-1" disabled><i class="fas fa-eye"></i> Nilai Akhir</button>
                        Digunakan untuk melihat rekapitulasi nilai akhir individu, indeks kontribusi SCI, dan Badge siswa.
                    </li>
                </ul>
                <hr class="my-3">
                <h6 class="fw-bold text-success mb-2">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Data
                </h6>
                <ul class="mb-0">
                    <li><strong>Export Semua Kelas</strong> → mengunduh seluruh data nilai dari semua kelas.</li>
                    <li><strong>Unduh Rekap</strong> → mengunduh nilai berdasarkan kelas tertentu saja.</li>
                </ul>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup Pemandu</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (session('swal'))
<script>
    Swal.fire({
        icon: "{{ session('swal.icon') }}",
        title: "{{ session('swal.title') }}",
        text: "{{ session('swal.text') }}",
        confirmButtonColor: '#4e73df'
    });
</script>
@endif
@endpush