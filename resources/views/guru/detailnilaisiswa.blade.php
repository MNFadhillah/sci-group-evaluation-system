@extends('layouts.main')
@section('dataNilai', 'active')

@section('head')
{{-- jika layout punya section head, DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* sedikit styling custom agar rapi */
    .meta-key {
        font-weight: 600;
        color: #495057;
    }

    .meta-value {
        color: #212529;
    }

    .card-activity {
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }

    .badge-nilai {
        font-size: .85rem;
        padding: .35rem .6rem;
        border-radius: .35rem;
    }

    .no-data {
        color: #6c757d;
        font-style: italic;
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <a href="{{ route('data.nilai') }}" class="btn btn-outline-primary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>


    {{-- header / card info aktivitas --}}
    <div class="card card-activity mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            {{ $activity->title }}
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:28px;height:28px" data-bs-toggle="modal" data-bs-target="#modalInfoDetailNilai" title="Informasi Evaluasi">
                                <i class="fas fa-info fa-sm"></i>
                            </button>
                        </h3>

                        <!-- PENANDA MODE UNTUK GURU -->
                        @if($isMode2)
                        <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm">
                            <i class="fas fa-layer-group me-1"></i> Mode 2: Kuis Kelompok (SCI)
                        </span>
                        @elseif($isMode1)
                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm">
                            <i class="fas fa-users me-1"></i> Mode 1: Proyek/Uraian Kelompok
                        </span>
                        @else
                        <span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm">
                            <i class="fas fa-user me-1"></i> Mode Individu Biasa
                        </span>
                        @endif
                    </div>

                    <div class="text-muted mb-2">
                        {{-- Subject, Topic, Class (menggunakan relasi yang ada jika tersedia) --}}
                        <span class="me-3"><span class="meta-key">Mata Pelajaran:</span>
                            <span
                                class="meta-value">{{ optional(optional($activity->topic)->subject)->name ?? '-' }}</span>
                        </span>
                        <span class="me-3"><span class="meta-key">Topik:</span>
                            <span class="meta-value">{{ optional($activity->topic)->title ?? '-' }}</span>
                        </span>
                        <span class="me-3"><span class="meta-key">Kelas:</span>
                            <span class="meta-value">
                                {{ optional(optional($activity->topic)->subject)->id_class
        ? (optional(optional($activity->topic)->subject)->classes->name ?? 'Kelas ' . optional(optional($activity->topic)->subject)->id_class)
        : '-' }}
                            </span>
                        </span>
                    </div>

                    <div class="small text-muted">
                        <span class="me-3"><i class="far fa-calendar-alt me-1"></i>
                            Dibuat: {{ optional($activity->created_at)->format('d M Y H:i') ?? '-' }}
                        </span>
                        <span><i class="far fa-clock me-1"></i>
                            Deadline: {{ optional($activity->deadline)->format('d M Y H:i') ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    {{-- ringkasan angka --}}
                    @php
                    // students: koleksi/array dari controller: setiap elemen ['id','name','nilai']
                    $countStudents = isset($students) ? count($students) : 0;
                    $countWithNilai = 0;
                    $sumNilai = 0;
                    if ($countStudents) {
                    foreach ($students as $st) {
                    if (isset($st['nilai']) && $st['nilai'] !== null && $st['nilai'] !== '') {
                    $countWithNilai++;
                    // pastikan numeric
                    $sumNilai += is_numeric($st['nilai']) ? (float) $st['nilai'] : 0;
                    }
                    }
                    }
                    $avg = $countWithNilai ? round($sumNilai / $countWithNilai, 2) : null;
                    @endphp

                    <div class="d-inline-block text-start">
                        <div class="small text-muted">Siswa</div>
                        <div class="h5 mb-0">{{ $countStudents }}</div>
                    </div>

                    <div class="d-inline-block text-start ms-3">
                        <div class="small text-muted">Tercatat Nilai</div>
                        <div class="h5 mb-0">{{ $countWithNilai }}</div>
                    </div>

                    <div class="d-inline-block text-start ms-3">
                        <div class="small text-muted">Rata-rata</div>
                        <div class="h5 mb-0">{{ $avg ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- tabel nilai --}}
    <div class="card">
        <div class="card-body">
            @if($activity->is_group_activity === 'yes')
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-list text-secondary me-2"></i> Rincian Keseluruhan Siswa</h5>
            @endif
            <!-- ========================================== -->
            @if(empty($students) || count($students) === 0)
            <div class="alert alert-info mb-0">Tidak ada siswa di kelas ini.</div>
            @else
            <div class="table-responsive">
                <table id="nilaiTable" class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Nama Siswa</th>

                            @if($isMode1 || $isMode2)
                            <th style="width:140px" class="text-center">{{ $isMode2 ? 'Nilai Murni' : 'Nilai Kel.' }}</th>
                            <th style="width:100px" class="text-center">SCI</th>
                            <th style="width:140px" class="text-center">Badge</th>
                            @endif

                            <th style="width:140px" class="text-center">Nilai Akhir</th>
                            <th style="width:120px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $s)
                        @php
                        $nilai_akhir = $s['nilai'] ?? null;
                        // PERBAIKAN: Ubah jadi nilai_kolom_pertama sesuai Controller
                        $nilai_kolom_pertama = $s['nilai_kolom_pertama'] ?? '-';
                        $sci = $s['sci'] ?? '-';
                        $badge = $s['badge'] ?? '-';

                        if ($nilai_akhir !== null && $nilai_akhir !== '') {
                        $status = (is_numeric($nilai_akhir) && $nilai_akhir >= ($activity->kkm ?? 75)) ? 'Lulus' : 'Remedial';
                        } else {
                        $status = 'Belum Dinilai';
                        }
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold text-dark">
                                @if($isMode2)
                                <a href="{{ route('guru.koreksi.mode2', ['idActivity' => $activity->id, 'idUser' => $s['id']]) }}"
                                    class="text-decoration-none text-primary d-flex align-items-center gap-2"
                                    title="Koreksi Lembar Kuis Siswa (Mode 2)">
                                    {{ $s['name'] ?? ('Siswa ' . ($s['id'] ?? '')) }}
                                    <i class="fas fa-external-link-alt small opacity-50"></i>
                                </a>
                                @elseif($isMode1)
                                <div class="d-flex align-items-center gap-2">
                                    {{ $s['name'] ?? ('Siswa ' . ($s['id'] ?? '')) }}
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;" title="Mode 1">
                                        Tugas Kelompok
                                    </span>
                                </div>
                                @else
                                {{ $s['name'] ?? ('Siswa ' . ($s['id'] ?? '')) }}
                                @endif
                            </td>

                            @if($isMode1 || $isMode2)
                            <td class="text-center fw-bold text-secondary">{{ $nilai_kolom_pertama }}</td>
                            <td class="text-center fw-bold text-primary">{{ $sci }}</td>
                            <td class="text-center">
                                @if($badge !== '-' && $badge !== '<span class="no-data">-</span>')
                                {!! $badge !!}
                                @else
                                <span class="no-data">-</span>
                                @endif
                            </td>
                            @endif

                            <td class="text-center">
                                @if($nilai_akhir === null || $nilai_akhir === '')
                                <span class="no-data">-</span>
                                @else
                                <span class="text-dark fw-bold">{{ $nilai_akhir }}</span>
                                @endif
                            </td>
                            <td>
                                @if($status === 'Lulus')
                                <span class="badge bg-success">{{ $status }}</span>
                                @elseif($status === 'Remedial')
                                <span class="badge bg-warning text-dark">{{ $status }}</span>
                                @else
                                <span class="badge bg-secondary">{{ $status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            
            {{-- tombol export sederhana --}}
<div class="mt-3 d-flex gap-2">
    <a href="{{ route('detail.nilai', $activity->id) }}?export=xlsx" class="btn btn-sm btn-outline-success">
        <i class="fas fa-file-excel me-1"></i> Export Excel (XLSX)
    </a>

    @if($countWithNilai > 0)
        <form action="{{ route('guru.nilai.hapus', $activity->id) }}" method="POST" class="d-inline hapus-nilai-form">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-nilai">
                <i class="fas fa-trash-alt me-1"></i> Hapus Semua Nilai
            </button>
        </form>
    @endif
</div>
            @endif
        </div>
    </div>
</div>
{{-- MODAL INFO DETAIL NILAI --}}
<div class="modal fade" id="modalInfoDetailNilai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Detail Nilai Aktivitas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <p class="mb-3">
                    Halaman ini menampilkan <strong>hasil nilai siswa</strong> untuk satu
                    <strong>aktivitas evaluasi</strong> tertentu. Data digunakan untuk
                    memantau pencapaian siswa dan melakukan tindak lanjut pembelajaran.
                </p>

                <hr>

                <h6 class="fw-bold text-primary mb-2">
                    <i class="bi bi-layout-text-sidebar me-1"></i>
                    Informasi Aktivitas
                </h6>
                <ul>
                    <li><strong>Mata Pelajaran</strong> → mapel tempat aktivitas dibuat.</li>
                    <li><strong>Topik</strong> → topik pembelajaran aktivitas.</li>
                    <li><strong>Kelas</strong> → kelas yang mengerjakan aktivitas.</li>
                    <li><strong>Deadline</strong> → batas waktu pengerjaan siswa.</li>
                </ul>

                <hr>

                <h6 class="fw-bold text-success mb-2">
                    <i class="bi bi-people me-1"></i>
                    Ringkasan Nilai
                </h6>
                <ul>
                    <li><strong>Siswa</strong> → jumlah total siswa di kelas.</li>
                    <li><strong>Tercatat Nilai</strong> → siswa yang sudah mengerjakan.</li>
                    <li><strong>Rata-rata</strong> → nilai rata-rata siswa yang mengerjakan.</li>
                </ul>

                <hr>

                <h6 class="fw-bold text-warning mb-2">
                    <i class="bi bi-table me-1"></i>
                    Tabel Nilai
                </h6>
                <ul>
                    <li><strong>Nilai Akhir</strong> → skor akhir siswa.</li>
                    <li>
                        <strong>Status</strong>:
                        <ul>
                            <li><span class="badge bg-success">Lulus</span> → nilai memenuhi KKM.</li>
                            <li><span class="badge bg-warning text-dark">Remedial</span> → perlu perbaikan.</li>
                            <li><span class="badge bg-secondary">Belum Mengerjakan</span> → belum submit.</li>
                        </ul>
                    </li>
                </ul>

                <hr>

                <h6 class="fw-bold text-info mb-2">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Export Data
                </h6>
                <ul>
                    <li>Gunakan tombol <strong>Export Excel (XLSX)</strong> untuk mengunduh nilai siswa.</li>
                    <li>File dapat digunakan untuk laporan, arsip, atau pengolahan lanjutan.</li>
                </ul>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- jQuery + DataTables (CDN) - jika layout sudah include jQuery, yang ini tidak perlu --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#nilaiTable').DataTable();

        document.querySelectorAll('.btn-hapus-nilai').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const form = this.closest('.hapus-nilai-form');

                Swal.fire({
                    title: 'Hapus Semua Nilai?',
                    text: 'Semua nilai, jawaban, dan penilaian SCI untuk aktivitas "{{ $activity->title }}" akan dihapus permanen. Siswa perlu mengerjakan ulang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus semua',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection