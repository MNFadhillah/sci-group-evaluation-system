@extends('layouts.main')

@section('dashboard')
@if(request()->is('*dashboard*')) active @endif
@endsection

@section('content')
<style>
    .card {
        border-radius: 1rem;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1);
    }

    .profile-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid #4e73df;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    th {
        background-color: #4e73df !important;
        color: white;
        text-align: center;
        vertical-align: middle !important;
    }

    td {
        vertical-align: middle !important;
    }

    .status-card {
        border-left-width: 5px !important;
    }

    /* Modal Khusus Badge */
    .badge-card {
        border-radius: 14px;
        padding: 14px;
        min-height: 150px;
    }

    .badge-card .card-body {
        padding: 0;
    }

    .badge-card .badge-icon {
        width: 64px;
        height: 64px;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .badge-card .badge-title {
        font-weight: 700;
        font-size: 1rem;
    }

    .badge-card .badge-desc {
        color: #6c757d;
        font-size: .9rem;
        margin-top: 4px;
    }

    /* List Matches UI */
    .badge-matches-list {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .badge-matches-list .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.45rem 0.6rem;
        border-radius: 8px;
        border: 1px solid #eef2f6;
        background: #fdfdfd;
        gap: 12px;
        min-height: 44px;
    }

    .badge-matches-list .match-left {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1 1 auto;
    }

    .badge-matches-list .class-name {
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge-matches-list .match-right {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .claimed-pill {
        background: linear-gradient(180deg, #1cc88a, #17a673);
        color: #fff;
        font-size: 0.82rem;
        padding: 0.32rem 0.6rem;
        border-radius: 999px;
        display: inline-block;
    }

    /* Tab Panes transparent fixes */
    .tab-content .tab-pane {
        background: transparent !important;
        color: inherit !important;
        padding: 0.5rem 0;
    }

    .profile-badges-row .card,
    #badgeListModal .badge-card {
        background: transparent !important;
        box-shadow: none !important;
    }
</style>

<div class="container mt-3">
    <!-- 🔹 Profile, Statistik & Leaderboard -->
    <div class="row g-4 mb-4">

        <!-- Kiri: Profile, Stats & Badges -->
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm h-100 border-1 rounded-4">
                <div class="card-body p-4 d-flex flex-column">

                    <!-- Header Profil & Tombol -->
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-light">
                        <div class="d-flex align-items-center gap-3">
                            <img src="https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png"
                                alt="Foto Profile" class="rounded-circle shadow-sm" style="width: 75px; height: 75px; border: 3px solid #4e73df; object-fit: cover;">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                                <span class="text-muted small"><i class="fas fa-envelope text-secondary me-1"></i> {{ $user->email }}</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGabungKelas">
                            <i class="bi bi-plus-circle me-1"></i> Gabung Kelas
                        </button>
                    </div>

                    <!-- Grid Statistik -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-center border border-primary-subtle h-100 d-flex flex-column justify-content-center">
                                <h3 class="fw-bold text-primary mb-0">{{ $jumlahAktivitas }}</h3>
                                <span class="small text-muted fw-medium">Aktivitas</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-4 text-center border border-danger-subtle h-100 d-flex flex-column justify-content-center">
                                <h3 class="fw-bold text-danger mb-0">{{ $jumlahRemedial }}</h3>
                                <span class="small text-muted fw-medium">Remedial</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="bg-success bg-opacity-10 p-3 rounded-4 text-center border border-success-subtle h-100 d-flex flex-column justify-content-center">
                                <span class="fw-bold text-success text-truncate px-1 mb-1" style="font-size: 0.95rem;" title="{{ $kelasList->pluck('name')->implode(', ') }}">
                                    {{ $kelasList->isNotEmpty() ? $kelasList->pluck('name')->implode(', ') : '-' }}
                                </span>
                                <span class="small text-muted fw-medium">Kelas Aktif</span>
                            </div>
                        </div>
                    </div>

                    <!-- Etalase Badge -->
                    <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0">Perolehan Badge</h6>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#badgeListModal">
                                    <i class="fas fa-search me-1"></i> Info & Klaim
                                </button>
                            </div>

                        {{-- Tabs Kelas --}}
                        <ul class="nav nav-pills nav-sm mb-3 gap-2" id="badgeTabs" role="tablist">
                            @foreach($kelasList as $k)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill px-3 py-1" style="font-size: 0.85rem; font-weight: 500;" id="badge-tab-{{ $k->id }}" data-bs-toggle="pill"
                                    data-bs-target="#badge-pane-{{ $k->id }}" type="button" role="tab"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $k->name }}</button>
                            </li>
                            @endforeach
                        </ul>

                        {{-- Panes Badge --}}
                        <div class="tab-content bg-light rounded-4 p-3 border border-light">
                            @foreach($kelasList as $k)
                            @php $key = 'class_' . $k->id; @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="badge-pane-{{ $k->id }}" role="tabpanel">
                                <div class="row g-2 profile-badges-row">
                                    @if(!empty($badgesByClass[$key]))
                                    @foreach($badgesByClass[$key] as $ub)
                                    @php
                                    $icon = $ub->path_icon ? asset($ub->path_icon) : asset('img/default.png');
                                    $modalTarget = 'modalDetailBadge_' . $ub->id . '_' . $k->id;
                                    @endphp
                                    <div class="col-6 col-sm-4 text-center">
                                        <!-- Card Badge yang bisa diklik -->
                                        <div class="bg-white p-3 rounded-4 shadow-sm border border-light h-100 transition-hover position-relative"
                                            style="cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#{{ $modalTarget }}"
                                            title="Klik untuk melihat riwayat aktivitas">

                                            <!-- Angka Multiplier (Muncul seperti notifikasi merah di pojok kanan atas) -->
                                            @if($ub->jumlah_diperoleh > 1)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white border-2" style="font-size: 0.75rem; z-index: 5;">
                                                x{{ $ub->jumlah_diperoleh }}
                                            </span>
                                            @endif

                                            <img src="{{ $icon }}" alt="{{ $ub->name }}" class="mb-2" style="width: 55px; height: 55px; object-fit: contain;">

                                            <div class="fw-bold text-dark lh-sm mt-1" style="font-size: 0.85rem;">
                                                {{ $ub->name }}
                                            </div>

                                            <!-- Petunjuk Visual agar siswa tahu ini bisa diklik -->
                                            <div class="mt-2 text-primary fw-medium" style="font-size: 0.7rem;">
                                                <i class="fas fa-hand-pointer me-1"></i>Lihat Riwayat
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="col-12 text-center py-3 empty-badge-msg">
                                        <div class="text-muted small fst-italic">Belum ada badge di kelas ini. Selesaikan aktivitas untuk meraih badge!</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Leaderboard -->
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm h-100 border-1 rounded-4">
                <div class="card-body p-4 d-flex flex-column">

                    <!-- Header Leaderboard Dirapikan -->
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-light">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="fas fa-trophy text-warning me-2"></i> Leaderboard
                        </h5>

                        @if($kelasList->count() > 1)
                        <select id="kelasSelector" class="form-select form-select-sm border-secondary-subtle bg-light fw-medium rounded-pill px-3 shadow-sm" style="width: auto; max-width: 150px;">
                            @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">
                            {{ $kelasList->first()->name ?? '-' }}
                        </span>
                        @endif
                    </div>

                    <!-- Area Konten Leaderboard -->
                    <div id="leaderboardArea" class="px-2 flex-grow-1" style="max-height: 420px; overflow-y: auto;">
                        <!-- JS Inject Here -->
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- 🔹 Daftar Nilai -->
    <div class="card shadow-sm border-1 rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4 text-primary">
                <i class="bi bi-bar-chart-line me-2"></i> Daftar Nilai
            </h5>

            <!-- Area Filter & Jumlah Data -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="filterKelas" class="small text-muted mb-0 fw-medium" style="white-space: nowrap;">Filter Kelas:</label>
                    <select id="filterKelas" class="form-select form-select-sm border-secondary-subtle shadow-sm fw-medium" style="min-width: 200px;">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                        <option value="{{ e($k->name) }}">{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-light px-3 py-1 rounded-pill border border-light shadow-sm">
                    <small class="text-muted">Jumlah Data: <strong id="countVisible" class="text-dark">{{ $nilaiList->count() }}</strong></small>
                </div>
            </div>

            <div class="table-responsive">
                @if($nilaiList->isEmpty())
                <div class="text-center text-muted py-5 bg-light rounded-4 border border-light">
                    <i class="bi bi-inboxes fs-1 d-block mb-3 opacity-50"></i>
                    <span class="fw-medium">Belum ada data nilai.</span>
                </div>
                @else
                {{-- DESKTOP: DataTable --}}
                <div class="d-none d-md-block">
                    <table id="nilaiTable" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Topik</th>
                                <th>Nama Aktivitas</th>
                                <th>Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nilaiList as $index => $n)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($n->result_created_at)->format('d M Y H:i') }}</td>
                                <td>{{ $n->kelas ?? '-' }}</td>
                                <td>{{ $n->mapel ?? '-' }}</td>
                                <td>{{ $n->topik ?? $n->aktivitas ?? '-' }}</td>
                                <td>{{ $n->aktivitas ?? '-' }}</td>
                                <td>
                                    {{ is_null($n->nilai_akhir) || $n->nilai_akhir === '-' ? 'Belum Mengerjakan' : $n->nilai_akhir }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE: Card List --}}
                <div class="d-block d-md-none">
                    @foreach($nilaiList as $n)
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body">
                            <div class="fw-bold mb-1">{{ $n->aktivitas ?? '-' }}</div>
                            <div class="small text-muted mb-2">{{ \Carbon\Carbon::parse($n->result_created_at)->format('d M Y H:i') }}</div>
                            <div class="mb-2">
                                <div><strong>Kelas:</strong> {{ $n->kelas ?? '-' }}</div>
                                <div><strong>Mapel:</strong> {{ $n->mapel ?? '-' }}</div>
                                <div><strong>Topik:</strong> {{ $n->topik ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="badge {{ is_numeric($n->nilai_akhir) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ is_numeric($n->nilai_akhir) ? 'Nilai: ' . $n->nilai_akhir : 'Belum Mengerjakan' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Informasi & Klaim Badge -->
<div class="modal fade" id="badgeListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="badgeListModalLabel">
                    <i class="fas fa-medal text-warning me-2"></i> Pusat Informasi & Klaim Badge
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                @if(!isset($allBadges) || $allBadges->isEmpty())
                <div class="text-center text-muted py-4">Belum ada data badge di sistem.</div>
                @else
                <div class="row g-3">
                    @foreach($allBadges as $b)
                    @php
                    $icon = $b->path_icon ? asset($b->path_icon) : asset('img/default.png');
                    // Deteksi apakah ini badge Kelompok (Otomatis) atau Mandiri (Manual)
                    $isOtomatis = in_array($b->id, [4, 5, 6]);
                    @endphp

                    <div class="col-12 col-sm-6 col-md-4" id="badge-card-{{ $b->id }}">
                        <div class="card h-100 shadow-sm badge-card bg-light border-0">
                            <div class="card-body d-flex gap-3">
                                <img src="{{ $icon }}" alt="{{ $b->name }}" class="badge-icon bg-white p-1">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="badge-title mb-1 text-dark">{{ $b->name }}</div>
                                    <div class="badge-desc small text-muted mb-2 lh-sm">{{ $b->description }}</div>

                                    @if($isOtomatis)
                                    <!-- Jika Badge Kelompok, tidak perlu diklaim manual -->
                                    <div class="small text-primary fw-bold mt-2">
                                        Dibagikan Otomatis oleh Sistem
                                    </div>
                                    @else
                                    <!-- Jika Badge Mandiri, render wrapper untuk JS AJAX klaim -->
                                    <div class="badge-matches-wrapper"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Kumpulan Modal Detail Perolehan Badge -->
@foreach($kelasList as $k)
@php $key = 'class_' . $k->id; @endphp
@if(!empty($badgesByClass[$key]))
@foreach($badgesByClass[$key] as $ub)
@php
$icon = $ub->path_icon ? asset($ub->path_icon) : asset('img/default.png');
$modalTarget = 'modalDetailBadge_' . $ub->id . '_' . $k->id;
@endphp
<div class="modal fade text-start" id="{{ $modalTarget }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4">
                <img src="{{ $icon }}" alt="{{ $ub->name }}" class="mb-3" style="width: 70px; height: 70px; object-fit: contain;">
                <h5 class="fw-bold text-dark mb-1">{{ $ub->name }}</h5>
                <p class="text-muted small mb-4">{{ $ub->description }}</p>

                <div class="text-start">
                    <h6 class="fw-bold text-secondary mb-2" style="font-size: 0.85rem;">
                        <i class="fas fa-list-check me-1"></i> Diraih dari Aktivitas:
                    </h6>
                    <ul class="list-group list-group-flush border rounded-3 overflow-auto shadow-sm" style="max-height: 150px;">
                        @php
                        $aktivitasArray = explode('||', $ub->daftar_aktivitas);
                        @endphp
                        @foreach($aktivitasArray as $actName)
                        <li class="list-group-item bg-light border-light small py-2">
                            <i class="fas fa-check-circle text-success me-2"></i> {{ trim($actName) }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif
@endforeach

<!-- modal gabung kelas -->
<div class="modal fade" id="modalGabungKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('student.gabungKelas') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGabungKelasLabel">Gabung Kelas dengan Token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Masukkan Token Kelas</label>
                        <input type="text" name="token" class="form-control form-control-sm" placeholder="Token kelas" required>
                    </div>
                    <div class="small text-muted">Token biasanya diberikan oleh guru. Pastikan memasukkan token dengan benar.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Gabung</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#nilaiTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                paginate: {
                    previous: "←",
                    next: "→"
                },
                zeroRecords: "Tidak ditemukan data yang sesuai."
            },
            order: [],
            columnDefs: [{
                orderable: false,
                targets: 0
            }]
        });

        function escapeRegex(str) {
            return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        $('#filterKelas').on('change', function() {
            var val = $(this).val();
            if (!val) {
                table.column(2).search('').draw();
            } else {
                var regex = '^' + escapeRegex(val) + '$';
                table.column(2).search(regex, true, false).draw();
            }
        });

        table.on('draw.dt', function() {
            var info = table.page.info();
            table.column(0, {
                search: 'applied',
                order: 'applied',
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = info.start + i + 1;
            });
            $('#countVisible').text(table.rows({
                search: 'applied'
            }).count());
        });

        table.draw();
    });
</script>

<div id="serverData"
    data-leaderboards="{{ json_encode($leaderboardsPerClass) }}"
    data-userid="{{ $user->id }}"
    class="d-none"></div>

<script>
    const serverDataElem = document.getElementById('serverData');
    const leaderboardsPerClass = JSON.parse(serverDataElem.getAttribute('data-leaderboards'));
    const myUserId = parseInt(serverDataElem.getAttribute('data-userid'));

    function renderLeaderboardForClass(classId) {
        const block = leaderboardsPerClass.find(c => c.class_id == classId);
        const area = document.getElementById('leaderboardArea');

        if (!block || !block.students || block.students.length === 0) {
            area.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-medal fs-1 opacity-25 mb-3 d-block"></i>Belum ada data skor di kelas ini.</div>`;
            return;
        }

        let html = '<ul class="list-group list-group-flush border-top-0">';
        block.students.forEach((row, idx) => {
            const isMe = (row.id == myUserId);
            const rank = idx + 1;

            let rankDisplay = `<div class="fw-bold text-secondary text-center" style="width:30px; font-size: 1.1rem;">${rank}</div>`;
            if (rank === 1) rankDisplay = `<div class="text-center" style="width:30px;"><i class="fas fa-medal fs-3" style="color: #FFD700;"></i></div>`;
            if (rank === 2) rankDisplay = `<div class="text-center" style="width:30px;"><i class="fas fa-medal fs-4" style="color: #C0C0C0;"></i></div>`;
            if (rank === 3) rankDisplay = `<div class="text-center" style="width:30px;"><i class="fas fa-medal fs-5" style="color: #CD7F32;"></i></div>`;

            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center border-0 mb-2 rounded-3 shadow-sm px-3 py-2 ${isMe ? 'bg-primary bg-opacity-10 border-start border-4 border-primary' : 'bg-white border border-light'}">
                    <div class="d-flex align-items-center gap-3">
                        ${rankDisplay}
                        <div class="fw-bold ${isMe ? 'text-primary' : 'text-dark'}" style="font-size: 0.95rem;">${row.name}</div>
                    </div>
                    <div class="text-end bg-light px-3 py-1 rounded-pill border border-light">
                        <span class="fw-bold text-dark">${Number(row.total_score).toLocaleString()}</span>
                        <small class="text-muted ms-1" style="font-size: 0.75rem;">pts</small>
                    </div>
                </li>`;
        });
        html += '</ul>';
        area.innerHTML = html;
    }

    if (leaderboardsPerClass.length > 0) {
        renderLeaderboardForClass(leaderboardsPerClass[0].class_id);
    }

    const sel = document.getElementById('kelasSelector');
    if (sel) {
        sel.addEventListener('change', function() {
            renderLeaderboardForClass(this.value);
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const badgeModal = document.getElementById('badgeListModal');

        async function checkEligibilityFor(badgeId) {
            try {
                const res = await fetch("{{ url('/badges') }}/" + badgeId + "/eligibility", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                return await res.json();
            } catch (err) {
                console.error('fetch error', err);
                return {
                    eligible: false,
                    reason: 'Gagal mengecek syarat (network).'
                };
            }
        }

        async function refreshBadgeEligibility() {
            // Bersihkan loading sebelumnya
            document.querySelectorAll('.badge-matches-wrapper').forEach(w => w.innerHTML = '');

            const cards = Array.from(document.querySelectorAll('[id^="badge-card-"]'));
            for (const card of cards) {
                const badgeId = card.id.replace('badge-card-', '').trim();
                const wrapper = card.querySelector('.badge-matches-wrapper');

                // Lewati badge otomatis yang tidak punya wrapper
                if (!wrapper) continue;

                // Tampilkan loading
                wrapper.innerHTML = '<div class="small text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Memeriksa syarat…</div>';

                const json = await checkEligibilityFor(badgeId);
                wrapper.innerHTML = '';

                if (json.eligible && Array.isArray(json.matches) && json.matches.length) {
                    const allClaimed = json.matches.every(m => !!m.already_claimed);

                    // Jika semua tugas yang memenuhi syarat sudah diklaim
                    if (allClaimed) {
                        wrapper.innerHTML = '<div class="small text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Sudah diklaim semua.</div>';
                        continue;
                    }

                    // Tampilkan tugas mandiri mana yang bisa diklaim
                    const list = document.createElement('div');
                    list.className = 'badge-matches-list';

                    json.matches.forEach(m => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item p-2';

                        const left = document.createElement('div');
                        left.className = 'match-left';
                        left.innerHTML = `<div class="class-name text-wrap lh-sm" title="${escapeHtml(m.activity_title)}">
                                            <span class="fw-bold text-dark">${escapeHtml(m.class_name)}</span><br>
                                            <small class="text-primary" style="font-size:0.75rem;">${escapeHtml(m.activity_title)}</small>
                                          </div>`;

                        const right = document.createElement('div');
                        if (m.already_claimed) {
                            right.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                        } else {
                            const btn = document.createElement('button');
                            btn.className = 'btn btn-sm btn-primary btn-claim-class px-2 py-1';
                            btn.style.fontSize = '0.75rem';
                            btn.dataset.badgeId = badgeId;
                            btn.dataset.classId = m.class_id;
                            btn.dataset.activityId = m.activity_id; // Parameter Activity ID (Baru)
                            btn.type = 'button';
                            btn.textContent = 'Klaim';
                            right.appendChild(btn);
                        }

                        item.appendChild(left);
                        item.appendChild(right);
                        list.appendChild(item);
                    });
                    wrapper.appendChild(list);
                } else {
                    const reason = json.reason || 'Belum memenuhi syarat.';
                    wrapper.innerHTML = `<div class="small text-muted fst-italic">${escapeHtml(reason)}</div>`;
                }
            }
        }

        // Delegated click handler untuk proses klaim ke Backend
        document.addEventListener('click', function(e) {
            const t = e.target;
            if (t && t.classList.contains('btn-claim-class')) {
                const badgeId = t.dataset.badgeId;
                const classId = t.dataset.classId;
                const activityId = t.dataset.activityId; // Tangkap ID aktivitas
                const originalText = t.innerText;

                t.disabled = true;
                t.innerText = 'Tunggu...';

                fetch("{{ route('badges.claim') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            badge_id: badgeId,
                            class_id: classId,
                            activity_id: activityId // Kirim ke Backend
                        })
                    })
                    .then(r => r.json().catch(() => ({
                        success: false,
                        message: 'Invalid JSON'
                    })))
                    .then(res => {
                        if (res && res.success) {
                            if (typeof Swal !== 'undefined') Swal.fire('Sukses', res.message, 'success');

                            // Ubah tombol jadi teks terklaim
                            const right = t.parentElement;
                            right.innerHTML = '<span class="claimed-pill">Terklaim</span>';

                            // Reload halaman agar Etalase Profil diupdate otomatis
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);

                        } else {
                            if (typeof Swal !== 'undefined') Swal.fire('Gagal', res.message || 'Gagal klaim', 'error');
                            t.disabled = false;
                            t.innerText = originalText;
                        }
                    })
                    .catch(err => {
                        console.error('claim error', err);
                        if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal menghubungi server', 'error');
                        t.disabled = false;
                        t.innerText = originalText;
                    });
            }
        });

        // Trigger loading badge saat modal dibuka
        if (badgeModal) badgeModal.addEventListener('show.bs.modal', refreshBadgeEligibility);

        // Helper cegah XSS
        function escapeHtml(str) {
            if (typeof str !== 'string') return str || '';
            return str.replace(/[&<>"'`=\/]/g, function(s) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                    '=': '&#x3D;'
                })[s];
            });
        }
    });
</script>
@endpush

{{-- SWEETALERT FLASH MESSAGE (Bebas Decorator VS Code Error) --}}
@if(session('swal_error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('swal_error') }}",
            confirmButtonColor: '#e74a3b'
        });
    });
</script>
@endif

@if(session('swal_warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: "{{ session('swal_warning') }}",
            confirmButtonColor: '#f6c23e'
        });
    });
</script>
@endif

@if(session('swal_success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('swal_success') }}",
            confirmButtonColor: '#1cc88a'
        });
    });
</script>
@endif
@endsection