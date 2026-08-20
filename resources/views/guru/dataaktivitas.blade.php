@extends('layouts.main')
@section('dataAktivitas', request()->is('dataaktivitas') ? 'active' : '')
@section('content')
    <div class="container-fluid py-3 px-4 d-flex flex-column">

        <div class="d-flex align-items-center gap-2 mb-4">
            <h3 class="fw-bold mb-0">Aktivitas</h3>
            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoAktivitas"
                title="Informasi Aktivitas">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success text-center shadow-sm">{{ session('success') }}</div>
        @endif

        {{-- FORM TAMBAH AKTIVITAS --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-plus-circle me-2"></i> Tambah Aktivitas
            </div>
            <div class="card-body">
                <form action="{{ route('guru.aktivitas.simpan') }}" method="POST">
                    @csrf

                    {{-- BAGIAN 1: INFORMASI DASAR --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Informasi Dasar</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Judul Aktivitas</label>
                            <input type="text" name="title" class="form-control shadow-sm"
                                placeholder="Masukkan judul aktivitas..." required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Topik</label>
                            <select name="id_topic" class="form-select shadow-sm" required>
                                <option value="">Pilih Topik</option>
                                @foreach (\App\Models\Topic::with('subject')->where('created_by', Auth::id())->get() as $topicOption)
                                    <option value="{{ $topicOption->id }}">
                                        {{ $topicOption->title }} ({{ $topicOption->subject->name ?? 'Tanpa Subject' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_topic')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Deadline</label>
                            <input type="datetime-local" name="deadline" class="form-control shadow-sm" required>
                            @error('deadline')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Durasi (menit)</label>
                            <input type="number" name="durasi_pengerjaan" class="form-control shadow-sm" min="1"
                                placeholder="Isi durasi yang Anda inginkan" required>
                            @error('durasi_pengerjaan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">KKM</label>
                            <input type="number" name="kkm" class="form-control shadow-sm" min="0" max="100"
                                placeholder="Isi nilai KKM yang Anda inginkan" required>
                            @error('kkm')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BAGIAN 2: PENGATURAN PENGERJAAN --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Pengaturan Pengerjaan</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Pengerjaan</label>
                            <div class="d-flex gap-4 border rounded p-2 bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_group_activity"
                                        id="individual_activity_add" value="no" checked>
                                    <label class="form-check-label" for="individual_activity_add">Individu</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_group_activity"
                                        id="group_activity_add" value="yes">
                                    <label class="form-check-label" for="group_activity_add">Kelompok</label>
                                </div>
                            </div>
                            @error('is_group_activity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode Adaptif</label>
                            <div class="border rounded p-2 bg-light">
                                <div class="form-check mb-0">
                                    <input type="hidden" name="addaptive" value="no">
                                    <input class="form-check-input" type="checkbox" name="addaptive" value="yes"
                                        id="adaptiveToggle">
                                    <label class="form-check-label" for="adaptiveToggle">Aktifkan soal
                                        adaptif</label>
                                </div>
                            </div>
                            @error('addaptive')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BAGIAN 3: MODE EVALUASI --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Mode Evaluasi</h6>
                    <div class="mb-4">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="evaluation_mode"
                                    id="evaluation_mode_1" value="mode1" checked>
                                <label class="form-check-label" for="evaluation_mode_1">
                                    <strong>Mode 1</strong>
                                    <div class="text-muted small">Menggunakan sistem pengerjaan aktivitas seperti
                                        biasa.</div>
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="evaluation_mode"
                                    id="evaluation_mode_2" value="mode2">
                                <label class="form-check-label" for="evaluation_mode_2">
                                    <strong>Mode 2</strong>
                                    <div class="text-muted small">Setiap siswa mendapatkan paket soal yang dapat
                                        berbeda.</div>
                                </label>
                            </div>
                        </div>
                        @error('evaluation_mode')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        {{-- KONFIGURASI MODE 2 --}}
                        <div class="d-none mt-3" id="mode2Config">
                            <div class="card border-primary shadow-sm">
                                <div class="card-header bg-primary text-white fw-semibold">
                                    <i class="bi bi-sliders me-2"></i> Konfigurasi Mode 2
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Jumlah Soal</label>
                                            <input type="number" name="jumlah_soal" id="jumlah_soal"
                                                class="form-control" value="10" min="1" max="100">
                                            <small class="text-muted">Jumlah soal per siswa.</small>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Jenis Soal</label>
                                            <div class="d-flex gap-4 mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="mode2_question_types[]" value="MultipleChoice"
                                                        id="mode2_mc" checked>
                                                    <label class="form-check-label" for="mode2_mc">Pilihan Ganda</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="mode2_question_types[]" value="ShortAnswer"
                                                        id="mode2_sa" checked>
                                                    <label class="form-check-label" for="mode2_sa">Isian
                                                        Singkat</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Pengacakan Soal</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="mode2_random_questions" value="1"
                                                    id="mode2_random_questions" checked>
                                                <label class="form-check-label" for="mode2_random_questions">Acak
                                                    soal</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Urutan Soal</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="mode2_random_order" value="1" id="mode2_random_order"
                                                    checked>
                                                <label class="form-check-label" for="mode2_random_order">Acak
                                                    urutan soal</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Sumber Soal</label>
                                            <select name="mode2_question_source" class="form-select">
                                                <option value="bank" selected>Bank Soal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button class="btn btn-success shadow-sm px-4 py-2">
                            <i class="bi bi-check-circle me-1"></i> Simpan Aktivitas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABEL AKTIVITAS (DESKTOP) --}}
        <div class="d-none d-md-block">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">

                    {{-- Kontrol kustom: Tampilkan entri (kiri) & Cari (kanan) --}}
                    <div id="dtControlsRow" class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="dtPageLength" class="fw-semibold mb-0 text-nowrap">Tampilkan</label>
                            <select id="dtPageLength" class="form-select form-select-sm" style="width:auto">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-nowrap">entri</span>
                        </div>

                        <div class="ms-auto" style="min-width:260px">
                            <input type="search" id="dtSearchInput" class="form-control form-control-sm"
                                placeholder="Cari aktivitas, topik, subject, atau kelas...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="activitiesTable" class="table table-striped table-bordered" style="width:100%">
                            <thead class="table-secondary text-center">
                                <tr>
                                    <th style="width:60px">No</th>
                                    <th>Judul</th>
                                    <th>Deadline</th>
                                    <th>Adaptif</th>
                                    <th>Topik</th>
                                    <th>Mapel</th>
                                    <th>Kelas</th>
                                    <th>Semester</th>
                                    <th>Jenis Pengerjaan</th>
                                    <th style="width:320px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $r)
                                    <tr>
                                        <td class="text-center align-middle"></td>
                                        <td class="align-middle">{{ $r->title }}</td>
                                        <td class="align-middle">
                                            {{ $r->deadline ? date('Y-m-d H:i', strtotime($r->deadline)) : '-' }}
                                        </td>
                                        <td class="align-middle text-center">
                                            @if ($r->addaptive === 'yes')
                                                <span class="badge bg-success">Ya</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="align-middle col-title">
                                            <div class="cell-inner" title="{{ $r->title }}">{{ $r->title }}</div>
                                        </td>
                                        <td class="align-middle col-subject hide-sm">
                                            <div class="cell-inner" title="{{ $r->subject_name ?? '-' }}">
                                                {{ $r->subject_name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="align-middle col-class hide-sm">
                                            <div class="cell-inner" title="{{ $r->class_name ?? '-' }}">
                                                {{ $r->class_name ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if ($r->semester === 'odd')
                                                <span class="badge bg-info text-dark">Ganjil</span>
                                            @elseif($r->semester === 'even')
                                                <span class="badge bg-secondary">Genap</span>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @if ($r->is_group_activity === 'yes')
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-people-fill me-1"></i> Kelompok
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-person-fill me-1"></i> Individu
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="action-group" role="group" aria-label="Aksi aktivitas">
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modalEdit{{ $r->id }}" title="Edit"
                                                    aria-label="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                @if ($r->is_group_activity !== 'yes')
                                                    <button type="button"
                                                        class="btn btn-success btn-sm btn-create-package"
                                                        data-url="{{ route('activity.package.create', $r->id) }}"
                                                        title="Buat Paket Soal">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ url('/guru/aktivitas/' . $r->id . '/atur-soal?topic=' . $r->topic_id) }}"
                                                    class="btn btn-warning btn-sm" title="Atur Soal"
                                                    aria-label="Atur Soal">
                                                    <i class="bi bi-gear"></i>
                                                </a>

                                                @if ($r->is_group_activity === 'yes')
                                                    <a href="{{ route('guru.activity.groups', $r->id) }}"
                                                        class="btn btn-primary btn-sm" title="Kelola Kelompok"
                                                        aria-label="Kelola Kelompok">
                                                        <i class="bi bi-people-fill"></i>
                                                    </a>
                                                @endif

                                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                                                    data-bs-target="#lihatSoal{{ $r->id }}" title="Lihat Soal"
                                                    aria-label="Lihat Soal">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <form action="{{ route('guru.aktivitas.hapus', $r->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                        title="Hapus" aria-label="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU AKTIVITAS (MOBILE) --}}
        <div class="d-block d-md-none">
            @foreach ($rows as $r)
                <div class="card shadow-sm mb-3 border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">{{ $r->title }}</h6>

                        <div class="small text-muted mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-bookmark text-secondary"></i>
                                <span><strong>Topik:</strong> {{ $r->topic_title ?? '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-journal-bookmark text-secondary"></i>
                                <span><strong>Mapel:</strong> {{ $r->subject_name ?? '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-building text-secondary"></i>
                                <span><strong>Kelas:</strong> {{ $r->class_name ?? '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-secondary"></i>
                                <span><strong>Deadline:</strong>
                                    {{ $r->deadline ? date('d M Y H:i', strtotime($r->deadline)) : '-' }}</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if ($r->addaptive === 'yes')
                                <span class="badge bg-success">Adaptif</span>
                            @else
                                <span class="badge bg-secondary">Non-adaptif</span>
                            @endif

                            @if ($r->semester === 'odd')
                                <span class="badge bg-info text-dark">Ganjil</span>
                            @elseif($r->semester === 'even')
                                <span class="badge bg-secondary">Genap</span>
                            @endif

                            @if ($r->is_group_activity === 'yes')
                                <span class="badge bg-primary">
                                    <i class="bi bi-people-fill me-1"></i> Kelompok
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-person-fill me-1"></i> Individu
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalEdit{{ $r->id }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            @if ($r->is_group_activity !== 'yes')
                                <button type="button" class="btn btn-success btn-sm btn-create-package"
                                    data-url="{{ route('activity.package.create', $r->id) }}">
                                    <i class="bi bi-archive"></i> Paket
                                </button>
                            @endif

                            <a href="{{ url('/guru/aktivitas/' . $r->id . '/atur-soal?topic=' . $r->topic_id) }}"
                                class="btn btn-warning btn-sm" title="Atur Soal" aria-label="Atur Soal">
                                <i class="bi bi-gear"></i> Atur Soal
                            </a>

                            @if ($r->is_group_activity === 'yes')
                                <a href="{{ route('guru.activity.groups', $r->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-people-fill"></i> Kelompok
                                </a>
                            @endif

                            <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                                data-bs-target="#lihatSoal{{ $r->id }}">
                                <i class="bi bi-eye"></i> Lihat
                            </button>

                            <form action="{{ route('guru.aktivitas.hapus', $r->id) }}" method="POST"
                                class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm btn-delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================= GLOBAL MODALS ================= --}}
        @foreach ($rows as $r)
            {{-- MODAL EDIT --}}
            <div class="modal fade" id="modalEdit{{ $r->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('guru.aktivitas.ubah', $r->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Aktivitas — {{ $r->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="title" class="form-control"
                                        value="{{ $r->title }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deadline</label>
                                    <input type="datetime-local" name="deadline" class="form-control"
                                        value="{{ $r->deadline ? date('Y-m-d\TH:i', strtotime($r->deadline)) : '' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Durasi (menit)</label>
                                    <input type="number" name="durasi_pengerjaan" class="form-control"
                                        value="{{ $r->durasi_pengerjaan ?? '' }}" min="1" placeholder="30">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">KKM</label>
                                    <input type="number" name="kkm" class="form-control"
                                        value="{{ $r->kkm }}" min="0" max="100" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Jenis Pengerjaan</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_group_activity"
                                            id="individual_activity_{{ $r->id }}" value="no"
                                            {{ $r->is_group_activity === 'no' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="individual_activity_{{ $r->id }}">
                                            Individu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_group_activity"
                                            id="group_activity_{{ $r->id }}" value="yes"
                                            {{ $r->is_group_activity === 'yes' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="group_activity_{{ $r->id }}">
                                            Kelompok
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Topik</label>
                                    <select name="id_topic" class="form-select" required>
                                        @foreach (\App\Models\Topic::with('subject')->where('created_by', Auth::id())->get() as $topicOpt)
                                            <option value="{{ $topicOpt->id }}"
                                                {{ $topicOpt->id == $r->topic_id ? 'selected' : '' }}>
                                                {{ $topicOpt->title }}
                                                ({{ $topicOpt->subject->name ?? 'Tanpa Subject' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-check mb-1">
                                    <input type="hidden" name="addaptive" value="no">
                                    <input class="form-check-input" type="checkbox" name="addaptive" value="yes"
                                        {{ $r->addaptive === 'yes' ? 'checked' : '' }}>
                                    <label class="form-check-label">Adaptif</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- MODAL LIHAT SOAL --}}
            <div class="modal fade" id="lihatSoal{{ $r->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-list-check me-2"></i> Daftar Soal – {{ $r->title }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @php
                                $selectedQuestions = $questionsMap[$r->id] ?? collect();
                            @endphp

                            @if ($selectedQuestions->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inboxes fs-1 d-block mb-2"></i>
                                    Belum ada soal untuk aktivitas ini.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="12%">Tipe</th>
                                                <th width="12%">Kesulitan</th>
                                                <th>Pertanyaan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($selectedQuestions as $s)
                                                @php $sData = json_decode($s->question); @endphp
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                    <td class="text-center"><span
                                                            class="badge bg-primary">{{ $s->type }}</span></td>
                                                    <td class="text-center">
                                                        @if (in_array($s->difficulty, ['easy', 'mudah']))
                                                            <span class="badge bg-success">Mudah</span>
                                                        @elseif(in_array($s->difficulty, ['medium', 'sedang']))
                                                            <span class="badge bg-warning text-dark">Sedang</span>
                                                        @else
                                                            <span class="badge bg-danger">Sulit</span>
                                                        @endif
                                                    </td>
                                                    <td>{!! nl2br(e($sData->text ?? '-')) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- MODAL INFO AKTIVITAS (dipindah keluar loop — cukup satu instance) --}}
        <div class="modal fade" id="modalInfoAktivitas" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow rounded-4 border-0">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle"></i> Informasi Data Evaluasi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-4">
                            Halaman ini digunakan untuk membuat, mengelola, dan mendistribusikan aktivitas
                            evaluasi (kuis/tes) kepada siswa berdasarkan topik pembelajaran.
                        </p>
                        <hr>

                        <section class="mb-4">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Aktivitas
                            </h6>
                            <ul class="ps-3 mb-0">
                                <li>Membuat evaluasi baru dengan judul, topik, deadline, dan durasi pengerjaan.</li>
                            </ul>
                        </section>
                        <hr>

                        <section class="mb-4">
                            <h6 class="fw-bold text-success mb-2">
                                <i class="bi bi-shuffle me-2"></i>Mode Adaptif
                            </h6>
                            <p>Setiap siswa mendapat alur soal yang menyesuaikan kemampuannya secara otomatis.</p>
                            <ul class="ps-3 mb-3">
                                <li>Semua siswa mulai dari soal <strong>sedang</strong>.</li>
                                <li>2 jawaban benar berturut-turut → soal berikutnya <strong>sulit</strong>.</li>
                                <li>2 jawaban salah berturut-turut → soal berikutnya <strong>mudah</strong>.</li>
                                <li>Jawaban bergantian → tetap <strong>sedang</strong>.</li>
                            </ul>
                            <div class="bg-light rounded p-3 mb-2">
                                <p class="fw-semibold mb-1">Contoh jika jumlah soal = 5:</p>
                                <p class="mb-0 text-muted">
                                    Sistem menyiapkan 11 soal: 5 sedang, 3 mudah, 3 sulit (rumus: Sedang = n,
                                    Mudah = n−2, Sulit = n−2).
                                </p>
                            </div>
                            <p class="text-muted mb-0">
                                Jika Mode Adaptif tidak diaktifkan, semua siswa mengerjakan soal yang sama.
                            </p>
                        </section>
                        <hr>

                        <section class="mb-4">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="bi bi-bar-chart me-2"></i>Penilaian
                            </h6>
                            <p class="text-muted mb-2">
                                Nilai akhir = (total poin diperoleh ÷ total poin maksimum) × 100.
                            </p>
                            <ul class="ps-3 mb-2">
                                <li>Soal mudah: <strong>10 poin</strong></li>
                                <li>Soal sedang: <strong>20 poin</strong></li>
                                <li>Soal sulit: <strong>30 poin</strong></li>
                            </ul>
                            <div class="bg-light rounded p-3">
                                <p class="mb-1 text-muted">Contoh: 2 soal sedang + 3 soal sulit = 40 + 90 = 130
                                    poin maksimum.</p>
                                <p class="mb-0"><strong>(130 ÷ 130) × 100 = 100</strong></p>
                            </div>
                        </section>
                        <hr>

                        <section class="mb-4">
                            <h6 class="fw-bold text-warning mb-2">
                                <i class="bi bi-gear me-2"></i>Aksi Aktivitas
                            </h6>
                            <ul class="ps-3 mb-0">
                                <li><i class="bi bi-pencil text-primary me-1"></i><strong>Edit</strong> — mengubah
                                    data aktivitas</li>
                                <li><i class="bi bi-archive text-success me-1"></i><strong>Buat Paket
                                        Soal</strong> — mengemas soal berdasarkan topik</li>
                                <li><i class="bi bi-sliders text-warning me-1"></i><strong>Atur Soal</strong> —
                                    menentukan soal yang digunakan</li>
                                <li><i class="bi bi-people text-primary me-1"></i><strong>Kelompok</strong> —
                                    mengatur pembagian anggota</li>
                                <li><i class="bi bi-eye text-info me-1"></i><strong>Lihat Soal</strong> — melihat
                                    daftar soal</li>
                                <li><i class="bi bi-trash text-danger me-1"></i><strong>Hapus</strong> —
                                    menghapus aktivitas permanen</li>
                            </ul>
                        </section>
                        <hr>

                        <section>
                            <h6 class="fw-bold text-secondary mb-2">
                                <i class="bi bi-calendar-event me-2"></i>Informasi Tambahan
                            </h6>
                            <ul class="ps-3 mb-0">
                                <li><strong>Semester</strong> → periode pembelajaran</li>
                                <li><strong>Kelas</strong> → target siswa</li>
                                <li><strong>Mapel</strong> → mata pelajaran terkait</li>
                            </ul>
                        </section>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- ================= END GLOBAL MODALS ================= --}}

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        .text-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .text-wrap {
            white-space: normal;
            word-wrap: break-word;
        }

        td.col-title {
            max-width: 220px;
        }

        td.col-topic {
            max-width: 180px;
        }

        td.col-subject {
            max-width: 140px;
        }

        td.col-class {
            max-width: 120px;
        }

        td.col-title>.cell-inner,
        td.col-topic>.cell-inner,
        td.col-subject>.cell-inner,
        td.col-class>.cell-inner {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-group {
            display: flex;
            gap: .35rem;
            align-items: center;
            white-space: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: .15rem 0;
        }

        .action-group .btn {
            flex: 0 0 auto;
        }

        @media (max-width: 768px) {
            .hide-sm {
                display: none !important;
            }
        }

        .dt-scroll-wrapper {
            overflow-x: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    let form = this.closest('form');

                    Swal.fire({
                        title: 'Yakin hapus aktivitas ini?',
                        text: "Data yang dihapus tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = $('#activitiesTable').DataTable({
                dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap mt-3"ip>',
                responsive: {
                    details: {
                        type: 'column',
                        target: -1
                    }
                },
                scrollX: true,
                autoWidth: false,
                lengthChange: true,
                pageLength: 10,
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [0, 9]
                    },
                    {
                        searchable: false,
                        targets: 0
                    },
                    {
                        responsivePriority: 1,
                        targets: 1
                    },
                    {
                        responsivePriority: 2,
                        targets: 9
                    }
                ],
                language: {
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    }
                },
                drawCallback: function() {
                    var tlist = [].slice.call(document.querySelectorAll('[title]'));
                    tlist.map(function(el) {
                        return new bootstrap.Tooltip(el);
                    });
                }
            });

            // sambungkan kontrol kustom (Tampilkan entri & Cari) ke DataTables
            $('#dtPageLength').on('change', function() {
                table.page.len(parseInt(this.value, 10)).draw();
            });

            var searchTimer;
            $('#dtSearchInput').on('keyup search input', function() {
                clearTimeout(searchTimer);
                var val = this.value;
                searchTimer = setTimeout(function() {
                    table.search(val).draw();
                }, 200);
            });

            table.on('order.dt search.dt draw.dt', function() {
                table.column(0, {
                    search: 'applied',
                    order: 'applied'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            $(document).on('click', '.btn-view-row', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                if ($tr.hasClass('child')) {
                    $tr = $tr.prev();
                }
                var rowData = table.row($tr).data();
                var html = '<dl class="row">';
                html += '<dt class="col-sm-3">Judul</dt><dd class="col-sm-9">' + $('<div>').text(rowData[1])
                    .html() + '</dd>';
                html += '<dt class="col-sm-3">Deadline</dt><dd class="col-sm-9">' + $('<div>').text(rowData[
                    2]).html() + '</dd>';
                html += '<dt class="col-sm-3">Adaptif</dt><dd class="col-sm-9">' + $('<div>').text(rowData[
                    3]).html() + '</dd>';
                html += '<dt class="col-sm-3">Topik</dt><dd class="col-sm-9">' + $('<div>').text(rowData[4])
                    .html() + '</dd>';
                html += '<dt class="col-sm-3">Subject</dt><dd class="col-sm-9">' + $('<div>').text(rowData[
                    5]).html() + '</dd>';
                html += '<dt class="col-sm-3">Kelas</dt><dd class="col-sm-9">' + $('<div>').text(rowData[6])
                    .html() + '</dd>';
                html += '</dl>';
                $('#rowDetailModal .modal-body').html(html);
                var modal = new bootstrap.Modal(document.getElementById('rowDetailModal'));
                modal.show();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-create-package').forEach(btn => {
                btn.addEventListener('click', function() {
                    let url = this.dataset.url;

                    Swal.fire({
                        title: 'Buat paket soal?',
                        text: 'Paket akan berisi semua soal dalam topik.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, buat',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang membuat paket soal',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (!res.success) {
                                    throw new Error(res.message ?? 'Gagal membuat paket');
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Paket soal berhasil dibuat',
                                });
                            })
                            .catch(err => {
                                Swal.fire('Error', err.message, 'error');
                            });
                    });
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mode1 = document.getElementById('evaluation_mode_1');
            const mode2 = document.getElementById('evaluation_mode_2');
            const mode2Config = document.getElementById('mode2Config');

            function updateEvaluationMode() {
                if (mode2.checked) {
                    mode2Config.classList.remove('d-none');
                } else {
                    mode2Config.classList.add('d-none');
                }
            }

            mode1.addEventListener('change', updateEvaluationMode);
            mode2.addEventListener('change', updateEvaluationMode);
            updateEvaluationMode();
        });
    </script>
@endpush