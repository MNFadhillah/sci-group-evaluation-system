@extends('layouts.main')

@section('dashboardGuru', request()->is('dashboardguru') ? 'active' : '')

@section('content')
<style>
    .stat-card {
        border-radius: 16px;
        transition: all .25s ease;
        cursor: pointer;
        background: #fff;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0,0,0,.08) !important;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #fff;
        flex-shrink: 0;
    }

    .quick-btn {
        border-radius: 14px;
        padding: 1.2rem;
        transition: all .2s ease;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }

    .quick-btn:hover {
        background: #eef2f7;
        transform: translateY(-3px);
        border-color: #dee2e6;
    }
</style>

<div class="container py-4">

    {{-- HERO SECTION --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4" style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);">
        <div class="card-body py-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 text-white">
            <div>
                <h3 class="fw-bold mb-1">
                    Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name }} 👋
                </h3>
                <p class="mb-0 opacity-75" style="font-size: 0.95rem;">
                    Anda berada di <strong>Dashboard Guru</strong>. Pantau aktivitas belajar dan kelola kelas Anda dengan mudah.
                </p>
            </div>
            <div class="d-none d-md-block text-end">
                <div class="bg-white bg-opacity-25 px-3 py-2 rounded-3 fw-bold small">
                    <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTIK (Disesuaikan jadi 4 Menu Saja Sesuai Sidebar) --}}
    <div class="row g-3 mb-4">
        
        <div class="col-6 col-lg-3">
            <a href="{{ route('kelasGuru') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center gap-2">
                        <div class="stat-icon bg-success shadow-sm">
                            <i class="fas fa-school"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">Data Kelas</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('tampilanSoal') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center gap-2">
                        <div class="stat-icon bg-danger shadow-sm">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">Bank Soal</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center gap-2">
                        <div class="stat-icon bg-info shadow-sm">
                            <i class="fas fa-tasks text-white"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">Aktivitas</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('data.nilai') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column align-items-center text-center gap-2">
                        <div class="stat-icon bg-secondary shadow-sm">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">Data Nilai</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- QUICK ACTION (SAMA PERSIS 100%) --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-bolt text-warning me-1"></i> Aksi Cepat
            </h5>

            <div class="row g-3">

                <div class="col-md-4">
                    <a href="{{ route('tambahSoal') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100 text-center text-md-start">
                            <i class="fas fa-plus-circle text-primary fs-3 mb-2 d-block"></i>
                            <div class="fw-semibold">Tambah Soal Manual</div>
                            <small class="text-muted">Buat soal sendiri</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('generateSoal') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100 text-center text-md-start">
                            <i class="fas fa-lightbulb text-success fs-3 mb-2 d-block"></i>
                            <div class="fw-semibold">Buat Soal Semi-Otomatis</div>
                            <small class="text-muted">Cepat & efisien</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100 text-center text-md-start">
                            <i class="fas fa-journal-whills text-info fs-3 mb-2 d-block"></i>
                            <div class="fw-semibold">Buat Aktivitas</div>
                            <small class="text-muted">Ujian / latihan</small>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection