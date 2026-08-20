@extends('layouts.main')

@section('dashboardGuru', request()->is('dashboardguru') ? 'active' : '')

@section('content')
<style>
    .menu-card {
        border-radius: 14px;
        transition: box-shadow .2s ease, border-color .2s ease;
        cursor: pointer;
        background: #fff;
        border: 1px solid #e5e8ec;
        height: 100%;
    }

    .menu-card:hover {
        box-shadow: 0 6px 16px rgba(0,0,0,.07);
        border-color: #4e73df;
    }

    .menu-icon {
        width: 44px;
        height: 44px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
        flex-shrink: 0;
    }

    .action-row {
        border-radius: 10px;
        padding: .65rem 1rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: background .15s ease;
        display: flex;
        align-items: center;
        gap: .8rem;
    }

    .action-row:hover {
        background: #eef2f7;
    }

    .action-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        color: #fff;
        flex-shrink: 0;
    }

    .side-panel {
        border-radius: 14px;
        border: 1px solid #e5e8ec;
        background: #fff;
        height: 100%;
    }
</style>

<div class="container-fluid py-2 px-4 d-flex flex-column" style="min-height: calc(100vh - 150px);">

    {{-- HERO --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h3 class="fw-bold mb-0">Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name }}</h3>
            <small class="text-muted">Dashboard Guru — pantau aktivitas belajar dan kelola kelas Anda.</small>
        </div>
        <div class="bg-white border px-3 py-1 rounded-3 fw-semibold small text-muted">
            <i class="fas fa-calendar-alt me-2 text-primary"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- MENU UTAMA --}}
    <div class="row g-2 mb-2">

        <div class="col-6 col-lg-3">
            <a href="{{ route('kelasGuru') }}" class="text-decoration-none text-dark">
                <div class="menu-card p-3 d-flex align-items-center gap-3">
                    <div class="menu-icon bg-success"><i class="fas fa-school"></i></div>
                    <div>
                        <div class="fw-bold">Data Kelas</div>
                        <small class="text-muted">Lihat & kelola kelas</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('tampilanSoal') }}" class="text-decoration-none text-dark">
                <div class="menu-card p-3 d-flex align-items-center gap-3">
                    <div class="menu-icon bg-danger"><i class="fas fa-question-circle"></i></div>
                    <div>
                        <div class="fw-bold">Bank Soal</div>
                        <small class="text-muted">Kumpulan soal Anda</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                <div class="menu-card p-3 d-flex align-items-center gap-3">
                    <div class="menu-icon bg-info"><i class="fas fa-tasks"></i></div>
                    <div>
                        <div class="fw-bold">Aktivitas</div>
                        <small class="text-muted">Ujian & latihan</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('data.nilai') }}" class="text-decoration-none text-dark">
                <div class="menu-card p-3 d-flex align-items-center gap-3">
                    <div class="menu-icon bg-secondary"><i class="fas fa-chart-bar"></i></div>
                    <div>
                        <div class="fw-bold">Data Nilai</div>
                        <small class="text-muted">Rekap nilai siswa</small>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- AKSI CEPAT + PANEL SAMPING --}}
    <div class="row g-2 flex-grow-1">

        <div class="col-lg-8 d-flex">
            <div class="side-panel p-4 w-100 d-flex flex-column">
                <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: .8rem; letter-spacing: .03em;">
                    Aksi Cepat
                </h6>

                <div class="d-flex flex-column gap-3 flex-grow-1 justify-content-evenly">

                    <a href="{{ route('tambahSoal') }}" class="text-decoration-none text-dark">
                        <div class="action-row">
                            <div class="action-icon bg-primary"><i class="fas fa-plus-circle"></i></div>
                            <div>
                                <div class="fw-semibold">Tambah Soal Manual</div>
                                <small class="text-muted">Buat soal sendiri satu per satu</small>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </div>
                    </a>

                    <a href="{{ route('generateSoal') }}" class="text-decoration-none text-dark">
                        <div class="action-row">
                            <div class="action-icon bg-success"><i class="fas fa-lightbulb"></i></div>
                            <div>
                                <div class="fw-semibold">Buat Soal Semi-Otomatis</div>
                                <small class="text-muted">Bantuan pembuatan soal yang lebih cepat</small>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </div>
                    </a>

                    <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                        <div class="action-row">
                            <div class="action-icon bg-info"><i class="fas fa-journal-whills"></i></div>
                            <div>
                                <div class="fw-semibold">Buat Aktivitas</div>
                                <small class="text-muted">Susun ujian atau latihan baru</small>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </div>
                    </a>

                    <a href="{{ route('kelasGuru') }}" class="text-decoration-none text-dark">
                        <div class="action-row">
                            <div class="action-icon bg-secondary"><i class="fas fa-users"></i></div>
                            <div>
                                <div class="fw-semibold">Kelola Kelas</div>
                                <small class="text-muted">Lihat daftar siswa per kelas</small>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted"></i>
                        </div>
                    </a>

                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex">
            <div class="side-panel p-4 w-100">
                <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: .8rem; letter-spacing: .03em;">
                    Panduan Singkat
                </h6>
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex gap-2">
                        <span class="badge rounded-circle bg-primary" style="width:22px;height:22px;line-height:14px;">1</span>
                        <small class="text-muted">Buat soal lewat <strong>Bank Soal</strong> sebelum membuat aktivitas.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge rounded-circle bg-primary" style="width:22px;height:22px;line-height:14px;">2</span>
                        <small class="text-muted">Buat ujian/latihan lewat menu <strong>Aktivitas</strong> dan pilih kelas tujuan.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge rounded-circle bg-primary" style="width:22px;height:22px;line-height:14px;">3</span>
                        <small class="text-muted">Pantau hasil belajar siswa lewat <strong>Data Nilai</strong>.</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection