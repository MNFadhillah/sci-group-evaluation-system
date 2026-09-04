@extends('layouts.main')

@section('aktivitas')
@if (request()->is('*aktivitassiswa*'))
active
@endif
@endsection

@section('content')

<style>
    /* =========================================================
           AKTIVITAS SISWA
           ========================================================= */

    .activity-page {
        padding: 8px 4px 30px;
    }

    /* ---------- Header ---------- */

    .activity-page-header {
        margin-bottom: 28px;
    }

    .activity-page-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 5px;
        color: #1f2937;
        font-size: 1.65rem;
        font-weight: 700;
    }

    .activity-page-title i {
        color: #4e73df;
        font-size: 1.5rem;
    }

    .activity-page-description {
        margin: 0;
        color: #6b7280;
        font-size: .98rem;
    }

    .activity-info-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 50%;
        font-size: .9rem;
    }


    /* =========================================================
           SECTION
           ========================================================= */

    .activity-section {
        margin-bottom: 40px;
    }

    .activity-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 17px;
    }

    .activity-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0;
        color: #1f2937;
        font-size: 1.12rem;
        font-weight: 700;
    }

    .activity-section-title i {
        color: #dc3545;
    }


    /* =========================================================
           CLASS HEADER
           ========================================================= */

    .class-header {
        display: flex;
        align-items: center;
        gap: 12px;

        padding: 11px 16px;

        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 12px;

        box-shadow: 0 3px 10px rgba(0, 0, 0, .035);
    }

    .class-header-icon {
        width: 36px;
        height: 36px;

        display: flex;
        align-items: center;
        justify-content: center;

        flex-shrink: 0;

        background: #edf3ff;
        color: #4e73df;

        border-radius: 10px;

        font-size: 1rem;
    }

    .class-header h6 {
        margin: 0;
        color: #263449;
        font-size: 1rem;
        font-weight: 700;
    }

    .class-sub {
        margin-top: 2px;
        color: #7b8491;
        font-size: .82rem;
    }


    /* =========================================================
           ACTIVITY CARD
           ========================================================= */

    .activity-card {
        position: relative;

        display: flex;
        flex-direction: column;

        height: 100%;
        min-height: 405px;

        overflow: hidden;

        background: #fff;

        border: 1px solid #e2e7ee;
        border-radius: 16px;

        box-shadow: 0 3px 12px rgba(15, 23, 42, .045);

        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease;
    }

    .activity-card:hover {
        transform: translateY(-3px);
        border-color: #cfd9eb;
        box-shadow: 0 9px 24px rgba(15, 23, 42, .08);
    }


    /* ---------- Image ---------- */

    .activity-image-wrapper {
        position: relative;
        height: 150px;
        overflow: hidden;
        background: #eef2f7;
    }

    .activity-card .card-img-top {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;

        transition: transform .3s ease;
    }

    .activity-card:hover .card-img-top {
        transform: scale(1.025);
    }

    .activity-image-overlay {
        position: absolute;
        inset: 0;

        background: linear-gradient(to bottom,
                rgba(0, 0, 0, 0) 50%,
                rgba(0, 0, 0, .18));

        pointer-events: none;
    }


    /* =========================================================
           CARD BODY
           ========================================================= */

    .activity-card .card-body {
        display: flex;
        flex-direction: column;

        flex: 1;

        padding: 18px;
    }

    .activity-title {
        min-height: 46px;
        margin: 0 0 12px;

        color: #1769ff;

        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.4;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;

        overflow: hidden;
    }


    /* =========================================================
           BADGES
           ========================================================= */

    .activity-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;

        margin-bottom: 13px;
    }

    .activity-badges .badge {
        max-width: 145px;

        padding: 5px 9px;

        border-radius: 999px;

        font-size: .73rem;
        font-weight: 600;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    /* =========================================================
           META INFORMATION
           ========================================================= */

    .activity-meta {
        display: flex;
        align-items: flex-start;
        gap: 9px;

        margin-bottom: 8px;

        color: #6b7280;

        font-size: .88rem;
        line-height: 1.4;
    }

    .activity-meta i {
        width: 17px;
        flex-shrink: 0;

        margin-top: 2px;

        color: #7d8794;
        text-align: center;
    }

    .activity-meta strong,
    .activity-meta .meta-value {
        color: #374151;
        font-weight: 600;
    }


    /* =========================================================
           STATUS AREA
           ========================================================= */

    .activity-status-box {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;

        margin-top: 6px;
        padding: 10px 12px;

        background: #f8fafc;

        border: 1px solid #edf0f4;
        border-radius: 10px;
    }

    .activity-status-label {
        color: #7b8491;
        font-size: .77rem;
    }

    .activity-status-value {
        margin-top: 1px;

        color: #252b33;
        font-size: .92rem;
        font-weight: 700;
    }

    .activity-status-box .badge {
        padding: 7px 10px;
        border-radius: 999px;
        font-size: .72rem;
        white-space: nowrap;
    }


    /* =========================================================
           DEADLINE
           ========================================================= */

    .activity-deadline {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-top: 10px;

        color: #6b7280;
        font-size: .82rem;
    }

    .activity-deadline i {
        color: #7d8794;
    }

    .activity-deadline.is-late {
        color: #dc3545;
        font-weight: 600;
    }


    /* =========================================================
           ACTION BUTTON
           ========================================================= */

    .activity-action {
        margin-top: auto;
        padding-top: 16px;
    }

    .activity-action .btn {
        min-height: 42px;

        border-radius: 10px;

        font-size: .88rem;
        font-weight: 700;
    }

    .activity-action .btn-success {
        box-shadow: 0 3px 8px rgba(25, 135, 84, .12);
    }

    .activity-action .btn-warning {
        box-shadow: 0 3px 8px rgba(255, 193, 7, .12);
    }

    .activity-action .btn-secondary {
        color: #fff;
        background: #9aa3af;
        border-color: #9aa3af;
    }


    /* =========================================================
           BELUM ADA AKTIVITAS
           ========================================================= */

    .empty-activity {
        padding: 55px 20px;

        background: #fff;

        border: 1px dashed #d8dee7;
        border-radius: 16px;

        text-align: center;
    }

    .empty-activity i {
        color: #b8c0cb;
        font-size: 2.5rem;
    }

    .empty-activity p {
        margin: 12px 0 0;
        color: #7b8491;
        font-size: .95rem;
    }


    /* =========================================================
           MODAL INFORMASI
           ========================================================= */

    #modalInfoAktivitas .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .16);
    }

    #modalInfoAktivitas .modal-header {
        padding: 18px 22px;

        background: #4e73df;
        color: #fff;

        border: 0;
    }

    #modalInfoAktivitas .modal-title {
        font-size: 1.08rem;
        font-weight: 700;
    }

    #modalInfoAktivitas .modal-body {
        padding: 24px;
    }

    #modalInfoAktivitas .modal-body section {
        padding: 2px 0;
    }

    #modalInfoAktivitas .modal-body h6 {
        display: flex;
        align-items: center;
        gap: 7px;

        color: #4e73df;
        font-size: .98rem;
    }

    #modalInfoAktivitas .modal-body p,
    #modalInfoAktivitas .modal-body li {
        color: #6b7280;
        font-size: .9rem;
        line-height: 1.65;
    }

    #modalInfoAktivitas .modal-body hr {
        margin: 20px 0;
        border-color: #edf0f4;
    }

    #modalInfoAktivitas .list-group-item {
        background: transparent;
        color: #6b7280;
        font-size: .88rem;
        border-color: #edf0f4;
    }

    #modalInfoAktivitas .calculation-box {
        padding: 15px;

        background: #f7f9fc;

        border: 1px solid #e9edf3;
        border-radius: 12px;
    }

    #modalInfoAktivitas .modal-footer {
        padding: 14px 22px;
        border-color: #edf0f4;
    }


    /* =========================================================
           RESPONSIVE
           ========================================================= */

    @media (max-width: 991.98px) {

        .activity-page {
            padding-left: 2px;
            padding-right: 2px;
        }

        .activity-card {
            min-height: 390px;
        }
    }


    @media (max-width: 767.98px) {

        .activity-page-title {
            font-size: 1.4rem;
        }

        .activity-page-description {
            font-size: .9rem;
        }

        .activity-section {
            margin-bottom: 32px;
        }

        .activity-card {
            min-height: 0;
        }

        .activity-image-wrapper {
            height: 145px;
        }

        .activity-card .card-body {
            padding: 16px;
        }
    }


    @media (max-width: 575.98px) {

        .activity-page {
            padding: 5px 0 25px;
        }

        .activity-page-header {
            margin-bottom: 22px;
        }

        .activity-page-title {
            font-size: 1.25rem;
        }

        .activity-page-title i {
            font-size: 1.2rem;
        }

        .activity-page-description {
            font-size: .85rem;
        }

        .activity-section-title {
            font-size: 1rem;
        }

        .class-header {
            padding: 10px 12px;
        }

        .class-header h6 {
            font-size: .92rem;
        }

        .class-sub {
            font-size: .76rem;
        }

        .activity-image-wrapper {
            height: 130px;
        }

        .activity-title {
            font-size: 1rem;
        }

        .activity-meta {
            font-size: .84rem;
        }

        .activity-status-box {
            padding: 9px 10px;
        }

        #modalInfoAktivitas .modal-body {
            padding: 19px;
        }
    }
</style>


<div class="container-fluid px-4 py-4">

    <div class="activity-page">

        <!-- =====================================================
                 HEADER
                 ===================================================== -->

        <div class="activity-page-header">

            <h1 class="activity-page-title">

                <i class="bi bi-journal-check"></i>

                Aktivitas

                <button type="button" class="btn btn-outline-primary activity-info-btn" data-bs-toggle="modal"
                    data-bs-target="#modalInfoAktivitas" title="Informasi Evaluasi">

                    <i class="bi bi-info-lg"></i>

                </button>

            </h1>

            <p class="activity-page-description">
                Lihat dan kerjakan aktivitas pembelajaranmu di sini.
            </p>

        </div>


        <!-- =====================================================
                 BELUM DIKERJAKAN
                 ===================================================== -->

        @if (!empty($belumDikerjakan) && $belumDikerjakan->count())
        <section class="activity-section">

            <div class="activity-section-header">

                <h2 class="activity-section-title">

                    <i class="bi bi-exclamation-circle"></i>

                    Belum Dikerjakan — Deadline Terdekat

                </h2>

            </div>


            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                @foreach ($belumDikerjakan as $sub)
                @php
                $nilai = $sub->result ?? '-';
                $status = $sub->result_status ?? '-';
                $statusLower = strtolower($status);

                // Tambahkan 'selesai' ke dalam pengecekan warna badge
                $cls = $statusLower === 'remedial' ? 'danger'
                : (in_array($statusLower, ['pass', 'selesai']) ? 'success' : 'secondary');

                $isAlreadyGraded = $nilai !== '-';
                $isSelesai = in_array($statusLower, ['pass', 'selesai']);

                $isPastDeadline = false;
                if (!empty($sub->deadline)) {
                try {
                $isPastDeadline = \Carbon\Carbon::parse($sub->deadline)->isPast();
                } catch (\Exception $e) {
                $isPastDeadline = false;
                }
                }

                // BUG FIX: Tambahkan $isSelesai agar sistem tahu aktivitas sudah beres
                $cannotStart = $isAlreadyGraded || $isSelesai || $isPastDeadline;
                @endphp


                <div class="col">

                    <article class="activity-card">

                        <div class="activity-image-wrapper">

                            <img class="card-img-top"
                                src="https://picsum.photos/800/400?random=belum{{ $loop->iteration }}"
                                alt="Gambar Aktivitas">

                            <div class="activity-image-overlay"></div>

                        </div>


                        <div class="card-body">

                            <h5 class="activity-title" title="{{ $sub->aktivitas }}">

                                {{ $sub->aktivitas }}

                            </h5>


                            <div class="activity-meta">

                                <i class="bi bi-book"></i>

                                <div>
                                    Mata Pelajaran:
                                    <span class="meta-value">
                                        {{ $sub->mapel ?? '-' }}
                                    </span>
                                </div>

                            </div>


                            <div class="activity-meta">

                                <i class="bi bi-bookmark"></i>

                                <div>
                                    Topik:
                                    <span class="meta-value">
                                        {{ $sub->topik ?? '-' }}
                                    </span>
                                </div>

                            </div>


                            <div class="activity-meta">

                                <i class="bi bi-people"></i>

                                <div>
                                    Kelas:
                                    <span class="meta-value">
                                        {{ $sub->nama_kelas ?? '-' }}
                                    </span>
                                </div>

                            </div>


                            <div class="activity-meta">

                                <i class="bi bi-person-workspace"></i>

                                <div>
                                    Pengerjaan:

                                    @if ($sub->is_group_activity === 'yes')
                                    <span class="badge bg-primary ms-1">
                                        <i class="bi bi-people-fill me-1"></i>
                                        Kelompok
                                    </span>
                                    @else
                                    <span class="badge bg-secondary ms-1">
                                        <i class="bi bi-person-fill me-1"></i>
                                        Individu
                                    </span>
                                    @endif

                                </div>

                            </div>


                            @php
                            $tanggal = $sub->deadline ?? $sub->created_at;
                            @endphp


                            <div class="activity-deadline {{ $isPastDeadline ? 'is-late' : '' }}">

                                <i class="bi bi-calendar-event"></i>

                                <span>

                                    {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y H:i') : '-' }}

                                    @if ($isPastDeadline)
                                    — deadline lewat
                                    @endif

                                </span>

                            </div>


                            <div class="activity-status-box">

                                <div>

                                    <div class="activity-status-label">
                                        Status
                                    </div>

                                    <div class="activity-status-value">
                                        {{ ucfirst($status) }}
                                    </div>

                                </div>


                                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">

                                    @if ($isPastDeadline)
                                    <span class="badge bg-danger" title="Deadline sudah lewat">

                                        Deadline Lewat

                                    </span>
                                    @endif

                                    <span class="badge bg-{{ $cls }}">

                                        {{ ucfirst($status) }}

                                    </span>

                                </div>

                            </div>


                            <div class="activity-meta mt-2 mb-0">

                                <i class="bi bi-clock-history"></i>

                                <div>

                                    Dibuat:

                                    <span class="meta-value">

                                        {{ $sub->created_at ? \Carbon\Carbon::parse($sub->created_at)->format('d M Y') : '-' }}

                                    </span>

                                </div>

                            </div>


                            <div class="activity-action">
                                @if ($cannotStart)
                                <button class="btn btn-secondary w-100" disabled
                                    title="{{ ($isAlreadyGraded || $isSelesai) ? 'Sudah diselesaikan' : ($isPastDeadline ? 'Deadline sudah lewat' : '') }}">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ ($isAlreadyGraded || $isSelesai) ? 'Sudah Selesai' : 'Tidak Bisa Dikerjakan' }}
                                </button>
                                @elseif($statusLower === 'belum menilai teman')
                                <a href="{{ route('activity.group.rating', $sub->id_activity) }}"
                                    class="btn btn-warning text-dark w-100">
                                    <i class="bi bi-star-fill me-1"></i>
                                    Nilai Teman Kelompok
                                </a>
                                @else
                                <button class="btn btn-success w-100"
                                    onclick="mulaiAktivitas('{{ $sub->id_activity }}', '{{ $sub->is_group_activity }}')">
                                    <i class="bi bi-play-fill me-1"></i>
                                    Kerjakan Sekarang
                                </button>
                                @endif
                            </div>

                        </div>

                    </article>

                </div>
                @endforeach

            </div>

        </section>
        @endif


        <!-- =====================================================
                 AKTIVITAS PER KELAS
                 ===================================================== -->

        @forelse ($activitiesByClass as $kelas)
        <section class="activity-section">

            <div class="activity-section-header">

                <div class="class-header">

                    <div class="class-header-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <div>

                        <h6>
                            Kelas {{ $kelas->nama_kelas }}
                        </h6>

                        <div class="class-sub">

                            Level {{ $kelas->level_kelas }}

                            • {{ $kelas->list->count() }} aktivitas

                        </div>

                    </div>

                </div>

            </div>


            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                @foreach ($kelas->list as $sub)
                @php
                $nilai = $sub->result ?? '-';
                $status = $sub->result_status ?? '-';
                $statusLower = strtolower($status);

                // Tambahkan 'selesai' ke dalam pengecekan warna badge
                $cls = $statusLower === 'remedial' ? 'danger'
                : (in_array($statusLower, ['pass', 'selesai']) ? 'success' : 'secondary');

                $isAlreadyGraded = $nilai !== '-';
                $isSelesai = in_array($statusLower, ['pass', 'selesai']);

                $isPastDeadline = false;
                if (!empty($sub->deadline)) {
                try {
                $isPastDeadline = \Carbon\Carbon::parse($sub->deadline)->isPast();
                } catch (\Exception $e) {
                $isPastDeadline = false;
                }
                }

                // BUG FIX: Masukkan variabel $isSelesai
                $cannotStart = $isAlreadyGraded || $isSelesai || $isPastDeadline;
                @endphp


                <div class="col">

                    <article class="activity-card">

                        <div class="activity-image-wrapper">

                            <img class="card-img-top"
                                src="https://picsum.photos/800/400?random={{ $kelas->id_class }}{{ $loop->iteration }}"
                                alt="Gambar Aktivitas">

                            <div class="activity-image-overlay"></div>

                        </div>


                        <div class="card-body">

                            <h5 class="activity-title" title="{{ $sub->aktivitas }}">

                                {{ $sub->aktivitas }}

                            </h5>


                            <div class="activity-badges">

                                <span class="badge bg-primary" title="{{ $sub->mapel ?? '' }}">

                                    {{ $sub->mapel ?? '-' }}

                                </span>


                                <span class="badge bg-info text-white" title="{{ $sub->topik ?? '' }}">

                                    {{ $sub->topik ?? '-' }}

                                </span>


                                <span class="badge bg-warning text-dark"
                                    title="Kelas {{ $kelas->nama_kelas ?? '' }}">

                                    Kls {{ $kelas->nama_kelas ?? '-' }}

                                </span>


                                <span class="badge bg-secondary" title="{{ ucfirst($status) }}">

                                    {{ ucfirst($status) }}

                                </span>

                            </div>


                            <div class="activity-meta">

                                <i class="bi bi-collection"></i>

                                <div>

                                    Status:

                                    <span class="meta-value">

                                        {{ ucfirst($status) }}

                                    </span>

                                </div>

                            </div>


                            @php
                            $tanggal = $sub->deadline ?? $sub->created_at;
                            @endphp


                            <div class="activity-deadline {{ $isPastDeadline ? 'is-late' : '' }}">

                                <i class="bi bi-calendar-event"></i>

                                <span>

                                    {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y H:i') : '-' }}

                                    @if ($isPastDeadline)
                                    — deadline lewat
                                    @endif

                                </span>

                            </div>


                            <div class="activity-status-box">

                                <div>

                                    <div class="activity-status-label">
                                        Nilai
                                    </div>

                                    <div class="activity-status-value">

                                        @if ($nilai !== null)
                                        {{ $nilai }}
                                        @else
                                        <span class="text-muted">
                                            Belum Ada
                                        </span>
                                        @endif

                                    </div>

                                </div>


                                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">

                                    @if ($isPastDeadline)
                                    <span class="badge bg-danger" title="Deadline sudah lewat">

                                        Deadline Lewat

                                    </span>
                                    @endif


                                    <span class="badge bg-{{ $cls }}">

                                        {{ ucfirst($status) }}

                                    </span>

                                </div>

                            </div>


                            <div class="activity-action">
                                @if ($cannotStart)
                                <button class="btn btn-secondary w-100" disabled
                                    title="{{ ($isAlreadyGraded || $isSelesai) ? 'Sudah diselesaikan' : ($isPastDeadline ? 'Deadline sudah lewat' : '') }}">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ ($isAlreadyGraded || $isSelesai) ? 'Sudah Selesai' : 'Tidak Bisa Dikerjakan' }}
                                </button>
                                @elseif($statusLower === 'belum menilai teman')
                                <a href="{{ route('activity.group.rating', $sub->id_activity) }}"
                                    class="btn btn-warning text-dark w-100">
                                    <i class="bi bi-star-fill me-1"></i>
                                    Nilai Teman Kelompok
                                </a>
                                @else
                                <button class="btn btn-success w-100"
                                    onclick="mulaiAktivitas('{{ $sub->id_activity }}', '{{ $sub->is_group_activity }}')">
                                    <i class="bi bi-play-fill me-1"></i>
                                    Kerjakan Sekarang
                                </button>
                                @endif
                            </div>

                        </div>

                    </article>

                </div>
                @endforeach

            </div>

        </section>

        @empty

        <div class="empty-activity">

            <i class="bi bi-emoji-frown"></i>

            <p>
                Belum ada aktivitas untukmu.
            </p>

        </div>
        @endforelse

    </div>

</div>


<!-- =============================================================
     MODAL INFORMASI AKTIVITAS
     ============================================================= -->

    <div class="modal fade" id="modalInfoAktivitas" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-info-circle me-2"></i>

                        Informasi Aktivitas

                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup">
                    </button>

                </div>


                <!-- BODY -->
                <div class="modal-body">

                    <!-- AKTIVITAS -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-journal-check"></i>

                            Tentang Aktivitas

                        </h6>

                        <p class="text-muted mb-0">

                            Aktivitas merupakan tugas atau evaluasi pembelajaran
                            yang diberikan oleh guru. Aktivitas dapat digunakan
                            untuk mengukur pemahaman siswa terhadap materi yang
                            telah dipelajari.

                        </p>

                    </section>


                    <hr>


                    <!-- CARA PENGERJAAN -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-person-workspace"></i>

                            Jenis Pengerjaan

                        </h6>

                        <p class="text-muted mb-2">

                            Setiap aktivitas memiliki jenis pengerjaan yang
                            dapat berupa individu maupun kelompok.

                        </p>

                        <ul class="text-muted ps-3 mb-0">

                            <li>
                                <strong>Individu</strong> — aktivitas dikerjakan
                                secara mandiri.
                            </li>

                            <li>
                                <strong>Kelompok</strong> — aktivitas dikerjakan
                                bersama anggota kelompok.
                            </li>

                        </ul>

                    </section>


                    <hr>


                    <!-- DEADLINE -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-calendar-event"></i>

                            Deadline

                        </h6>

                        <p class="text-muted mb-0">

                            Perhatikan tanggal dan waktu yang tercantum pada
                            setiap aktivitas. Aktivitas yang telah melewati
                            deadline tidak dapat dikerjakan kembali.

                        </p>

                    </section>


                    <hr>


                    <!-- STATUS -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-clipboard-check"></i>

                            Status Aktivitas

                        </h6>

                        <p class="text-muted mb-2">

                            Status pada setiap aktivitas menunjukkan kondisi
                            pengerjaan dan hasil evaluasi siswa.

                        </p>

                        <ul class="text-muted ps-3 mb-0">

                            <li>
                                <strong>Belum dikerjakan</strong> — aktivitas
                                belum diselesaikan.
                            </li>

                            <li>
                                <strong>Pass</strong> — hasil evaluasi telah
                                memenuhi ketentuan.
                            </li>

                            <li>
                                <strong>Remedial</strong> — siswa perlu mengikuti
                                evaluasi perbaikan.
                            </li>

                            <li>
                                <strong>Belum menilai teman</strong> — pada
                                aktivitas kelompok, siswa masih perlu memberikan
                                penilaian kepada anggota kelompok.
                            </li>

                        </ul>

                    </section>


                    <hr>


                    <!-- NILAI -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-bar-chart-line"></i>

                            Nilai

                        </h6>

                        <p class="text-muted mb-0">

                            Setelah aktivitas dinilai, hasil evaluasi akan
                            ditampilkan pada bagian <strong>Nilai</strong>.
                            Jika aktivitas belum dikerjakan atau belum dinilai,
                            nilai akan ditampilkan sebagai <strong>Belum Ada</strong>.

                        </p>

                    </section>


                    <hr>


                    <!-- SISTEM ADAPTIF -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-sliders"></i>

                            Aktivitas Adaptif

                        </h6>

                        <p class="text-muted mb-0">

                            Beberapa aktivitas menggunakan sistem soal adaptif.
                            Pada aktivitas tersebut, tingkat kesulitan soal dapat
                            menyesuaikan dengan pola jawaban siswa selama
                            pengerjaan.

                        </p>

                    </section>


                    <hr>


                    <!-- REMEDIAL -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-arrow-repeat"></i>

                            Remedial

                        </h6>

                        <p class="text-muted mb-0">

                            Jika hasil evaluasi belum memenuhi ketentuan,
                            siswa dapat mengikuti remedial apabila aktivitas
                            tersebut menyediakan kesempatan perbaikan.
                            Informasi mengenai status remedial akan ditampilkan
                            pada aktivitas terkait.

                        </p>

                    </section>


                    <hr>


                    <!-- LEADERBOARD -->
                    <section>

                        <h6 class="fw-bold mb-2">

                            <i class="bi bi-trophy"></i>

                            Leaderboard

                        </h6>

                        <p class="text-muted mb-0">

                            Hasil aktivitas tertentu dapat berkontribusi terhadap
                            perolehan poin siswa. Peringkat poin dapat dilihat
                            melalui <strong>Leaderboard</strong> pada Dashboard.

                        </p>

                    </section>

                </div>


                <!-- FOOTER -->
                <div class="modal-footer">

                    <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">

                        Tutup

                    </button>

                </div>

            </div>

        </div>

    </div>


@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function mulaiAktivitas(id, jenisPengerjaan) {

        Swal.fire({

            icon: 'info',

            title: 'Mulai Aktivitas',

            html: 'Kamu akan memulai aktivitas dengan ID: <strong>' +
                id +
                '</strong>',

            showCancelButton: true,

            confirmButtonText: 'Lanjut',

            cancelButtonText: 'Batal',

            confirmButtonColor: '#198754',

            cancelButtonColor: '#6c757d'

        }).then((result) => {

            if (result.isConfirmed) {

                if (jenisPengerjaan === 'yes') {

                    window.location.href =
                        `/activity/${id}/group/answer`;

                } else {

                    window.location.href =
                        `/activity/${id}`;

                }

            }

        });

    }
</script>
@endpush