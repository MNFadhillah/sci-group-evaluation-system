@extends('layouts.main')
@section('dataSoal', request()->is('datasoal') ? 'active' : '')

@section('content')

<style>
    .badge-mudah {
        background: #198754;
        color: #fff;
    }

    .badge-sedang {
        background: #ffc107;
        color: #212529;
    }

    .badge-sulit {
        background: #dc3545;
        color: #fff;
    }

    .topic-cell {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .topic-label {
        max-width: 260px;
        white-space: normal;
        word-break: break-word;
    }

    .question-cell {
        max-width: 320px;
    }

    .action-btns .btn {
        width: 34px;
    }
</style>

<div class="container-fluid py-3 px-4 d-flex flex-column">

    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <h3 class="fw-bold mb-0">Daftar Soal</h3>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px"
            data-bs-toggle="modal" data-bs-target="#modalInfoSoal" title="Informasi Daftar Soal">
            <i class="bi bi-info-lg"></i>
        </button>
    </div>

    {{-- TOMBOL AKSI UTAMA --}}
    <div class="d-flex flex-column flex-md-row gap-2 mb-4">
        <a href="{{ route('tambahSoal') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Soal Manual
        </a>
        <a href="{{ route('generateSoal') }}" class="btn btn-success d-none">
            <i class="bi bi-lightbulb me-1"></i> Buat Soal Otomatis
        </a>
    </div>

    {{-- FILTER --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
            <label for="filterTopik" class="fw-semibold mb-0">Filter Topik:</label>
            <select id="filterTopik" class="form-select" style="max-width:280px">
                <option value="">Semua Topik</option>
                @foreach ($topics as $t)
                <option value="{{ $t->id }}">{{ $t->title }}</option>
                @endforeach
            </select>
            <button id="resetFilterBtn" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
            <span id="totalSoal" class="ms-auto fw-semibold text-muted">
                Total: {{ $data->count() ?? count($data) }} soal
            </span>
        </div>
    </div>

    {{-- TABEL (DESKTOP) --}}
    <div class="card shadow-sm border-0 d-none d-md-block">
        <div class="card-body">
            <table id="soalTable" class="table table-striped table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Tipe</th>
                        <th>Pertanyaan</th>
                        <th>Topik</th>
                        <th>Kesulitan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                    @php
                    $topicObj = $topics->firstWhere('id', $item->id_topic);
                    $topicTitle = $topicObj ? $topicObj->title : '-';
                    @endphp
                    <tr data-question-id="{{ $item->id }}" data-topic-title="{{ $topicTitle }}"
                        data-id_topic="{{ $item->id_topic ?? '' }}">
                        <td class="fw-bold"></td>
                        <td>
                            <span class="badge bg-secondary">{{ $item->type }}</span>
                        </td>
                        <td class="question-cell">
                            {!! nl2br(e(Str::limit(strip_tags($item->question->text ?? ($item->question['text'] ?? '-')), 150))) !!}
                        </td>
                        <td>
                            <div class="topic-cell">
                                <span class="topic-label" title="{{ $topicTitle }}">{{ $topicTitle }}</span>
                                <button class="btn btn-sm btn-outline-primary btn-edit-topic" type="button"
                                    data-id="{{ $item->id }}" data-topic-id="{{ $item->id_topic ?? '' }}"
                                    title="Ubah topik">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $item->difficulty }}">
                                {{ ucfirst($item->difficulty) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1 action-btns">
                                <button class="btn btn-outline-primary btn-sm view-soal" data-bs-toggle="modal"
                                    data-bs-target="#modalLihatSoal"
                                    data-q="{{ base64_encode(json_encode($item->question)) }}"
                                    data-opt="{{ base64_encode(json_encode($item->MC_option)) }}"
                                    data-mcanswer="{{ $item->MC_answer }}"
                                    data-sa="{{ base64_encode(json_encode($item->SA_answer)) }}"
                                    data-type="{{ $item->type }}" title="Lihat">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                <a href="{{ route('editSoal', $item->id) }}" class="btn btn-outline-warning btn-sm"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('hapusSoal', $item->id) }}" method="POST"
                                    class="d-inline form-delete-soal">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-soal"
                                        title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
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

    {{-- KARTU (MOBILE) --}}
    <div class="d-block d-md-none">
        @foreach ($data as $item)
        @php
        $topicObj = $topics->firstWhere('id', $item->id_topic);
        $topicTitle = $topicObj ? $topicObj->title : '-';
        @endphp
        <div class="card shadow-sm mb-3 soal-card" data-id_topic="{{ $item->id_topic ?? '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-secondary">{{ $item->type }}</span>
                    <span class="badge badge-{{ $item->difficulty }} d-none">
                        {{ ucfirst($item->difficulty) }}
                    </span>
                </div>
                <p class="fw-semibold mb-2">
                    {{ Str::limit(strip_tags($item->question->text ?? '-'), 120) }}
                </p>
                <small class="text-muted d-block mb-3">Topik: {{ $topicTitle }}</small>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-fill view-soal" data-bs-toggle="modal"
                        data-bs-target="#modalLihatSoal" data-q="{{ base64_encode(json_encode($item->question)) }}"
                        data-opt="{{ base64_encode(json_encode($item->MC_option)) }}"
                        data-mcanswer="{{ $item->MC_answer }}"
                        data-sa="{{ base64_encode(json_encode($item->SA_answer)) }}"
                        data-type="{{ $item->type }}">
                        <i class="bi bi-eye"></i> Lihat
                    </button>
                    <a href="{{ route('editSoal', $item->id) }}" class="btn btn-outline-warning btn-sm flex-fill">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('hapusSoal', $item->id) }}" method="POST" class="flex-fill">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-delete-soal">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- MODAL: DETAIL SOAL --}}
    <div class="modal fade" id="modalLihatSoal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-card-text me-2"></i>Detail Soal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h5 id="soalText" class="fw-bold mb-3"></h5>
                    <div id="soalImage" class="mb-3 text-center"></div>
                    <hr>
                    <div id="soalPilihan" class="mb-3"></div>
                    <hr>
                    <strong>Jawaban Benar:</strong>
                    <p id="soalJawaban" class="text-success fw-semibold"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: EDIT TOPIK --}}
    <div class="modal fade" id="modalEditTopic" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formEditTopic">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Topik Soal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modalQuestionId" name="question_id" value="">

                        <div class="mb-3">
                            <label class="form-label">Pilih topik yang sudah ada</label>
                            <select id="modalTopicSelect" class="form-select">
                                <option value="">-- Pilih Topik --</option>
                                @foreach ($topics as $t)
                                <option value="{{ $t->id }}" data-id_subject="{{ $t->id_subject }}">
                                    {{ $t->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-center text-muted small mb-3">— atau —</div>

                        <div class="mb-2">
                            <label class="form-label">Buat topik baru</label>
                            <select id="modalSubjectSelect" class="form-select mb-2">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach ($subjects as $s)
                                <option value="{{ $s->id }}" data-id_class="{{ $s->id_class }}">
                                    {{ $s->name }}
                                </option>
                                @endforeach
                            </select>
                            <input id="modalNewTopic" type="text" class="form-control"
                                placeholder="Judul topik baru">
                            <div class="form-text">Wajib pilih Mata Pelajaran jika mengisi judul topik baru.</div>
                        </div>

                        <div id="modalEditAlert" class="alert d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="modalSaveBtn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- MODAL: INFO --}}
    <div class="modal fade" id="modalInfoSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow rounded-4">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Informasi Daftar Soal
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <p class="mb-4">
                        Halaman ini digunakan untuk mengelola soal yang terhubung
                        dengan topik dan mata pelajaran.
                    </p>

                    <hr>

                    {{-- JENIS SOAL --}}
                    <h6 class="fw-bold mb-3">
                        Jenis Soal
                    </h6>

                    <ul class="mb-4">

                        <li class="mb-2">
                            <strong>Pilihan Ganda</strong> —
                            siswa memilih satu jawaban yang benar dari beberapa pilihan.
                        </li>

                        <li class="mb-2">
                            <strong>Isian Singkat</strong> —
                            siswa menuliskan jawaban secara singkat sesuai dengan pertanyaan.
                        </li>

                        <li>
                            <strong>Esai</strong> —
                            siswa menuliskan jawaban dalam bentuk uraian sesuai dengan pertanyaan.
                        </li>

                    </ul>

                    <hr>

                    {{-- AKSI --}}
                    <h6 class="fw-bold mb-3">
                        Aksi pada Soal
                    </h6>

                    <ul class="mb-4">

                        <li class="mb-2">
                            <i class="bi bi-eye text-primary me-1"></i>
                            <strong>Lihat</strong> —
                            melihat detail soal dan jawaban.
                        </li>

                        <li class="mb-2">
                            <i class="bi bi-pencil-square text-warning me-1"></i>
                            <strong>Edit</strong> —
                            mengubah isi soal.
                        </li>

                        <li>
                            <i class="bi bi-trash text-danger me-1"></i>
                            <strong>Hapus</strong> —
                            menghapus soal.
                        </li>

                    </ul>

                    <hr>

                    {{-- TINGKAT KESULITAN (Disembunyikan sementara) --}}
                    <div class="d-none">
                        <h6 class="fw-bold mb-3">
                            Tingkat Kesulitan
                        </h6>

                        <ul class="mb-0">
                            <li class="mb-2">
                                <span class="badge badge-mudah">Mudah</span>
                                — pemahaman dasar.
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-sedang">Sedang</span>
                                — pemahaman menengah.
                            </li>
                            <li>
                                <span class="badge badge-sulit">Sulit</span>
                                — pemahaman tingkat lanjut.
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>



</div>

@endsection

@push('head')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<!-- jQuery sudah dimuat oleh layout -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $.fn.DataTable !== 'function') {
            console.error('DataTables tidak terdeteksi — cek urutan skrip di layout.');
            return;
        }

        var dt = $('#soalTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    className: 'text-center fw-bold'
                },
                {
                    targets: 4, // Sembunyikan kolom Kesulitan (Fitur Adaptif)
                    visible: false
                },
                {
                    targets: 5,
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                // penomoran otomatis mengikuti urutan/filter yang sedang aktif
                this.api().column(0, {
                    search: 'applied'
                }).nodes().each(function(cell, i) {
                    cell.textContent = i + 1;
                });
            }
        });

        function updateTotalLabel() {
            var visibleCount = dt.rows({
                search: 'applied'
            }).count();
            var totalEl = document.getElementById('totalSoal');
            if (totalEl) totalEl.textContent = 'Total: ' + visibleCount + ' soal';
        }
        updateTotalLabel();

        function filterMobileCards(topicId) {
            var visibleCount = 0;
            document.querySelectorAll('.soal-card').forEach(function(card) {
                var cardTopic = card.getAttribute('data-id_topic') || '';
                if (!topicId || String(cardTopic) === String(topicId)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            var totalEl = document.getElementById('totalSoal');
            if (totalEl) totalEl.textContent = 'Total: ' + visibleCount + ' soal';
        }

        var currentTopicFilter = '';
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'soalTable') return true;
            if (!currentTopicFilter) return true;
            var rowNode = dt.row(dataIndex).node();
            var rowTopic = rowNode ? (rowNode.getAttribute('data-id_topic') || '') : '';
            return String(rowTopic) === String(currentTopicFilter);
        });

        $('#filterTopik').on('change', function() {
            currentTopicFilter = $(this).val() || '';
            dt.draw();
            filterMobileCards(currentTopicFilter);
        });

        $('#resetFilterBtn').on('click', function() {
            $('#filterTopik').val('');
            currentTopicFilter = '';
            dt.search('');
            dt.order([
                [1, 'asc']
            ]);
            dt.page.len(10);
            dt.draw();
            filterMobileCards('');
            $('#filterTopik').focus();
        });

        dt.on('draw.dt', updateTotalLabel);

        // Modal: lihat detail soal
        $(document).on('click', '.view-soal', function() {
            var btn = this;
            var decode = function(v) {
                return v ? JSON.parse(atob(v)) : null;
            };
            var q = decode(btn.dataset.q);
            var opt = decode(btn.dataset.opt);
            var sa = decode(btn.dataset.sa);
            var type = btn.dataset.type;
            var mcAns = btn.dataset.mcanswer;

            $('#soalText').text(q?.text ?? "-");
            $('#soalImage').html(q?.URL ?
                `<img src="${q.URL}" class="img-fluid rounded" style="max-height:250px">` :
                "");

            var pilihan = $('#soalPilihan').empty();
            if (type === "MultipleChoice" && opt) {
                opt.forEach(function(o) {
                    var label = Object.keys(o)[0];
                    var d = o[label];
                    pilihan.append(`
                            <div class="border p-2 mb-2 rounded">
                                <strong>${label.toUpperCase()}.</strong> ${d.teks}
                                ${d.url ? `<br><img src="${d.url}" class="img-thumbnail mt-2" style="max-height:100px">` : ""}
                            </div>
                        `);
                });
            } else {
                pilihan.html("<em>Tidak ada pilihan jawaban.</em>");
            }

            $('#soalJawaban').text(type === "MultipleChoice" ? mcAns : (sa?.join(", ") ?? "-"));
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        var modalEl = document.getElementById('modalEditTopic');
        if (!modalEl) {
            console.error('Modal Edit Topik tidak ditemukan.');
            return;
        }

        $(document).on('click', '.btn-edit-topic', function() {
            var qid = $(this).data('id');
            var topicId = $(this).data('topic-id') || '';

            $('#modalQuestionId').val(qid);
            $('#modalTopicSelect').val(topicId);
            $('#modalNewTopic').val('');
            $('#modalSubjectSelect').val('');
            $('#modalEditAlert').addClass('d-none').removeClass('alert-success alert-danger').text('');

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        $('#modalNewTopic').on('input', function() {
            if ($(this).val().trim() !== '') $('#modalTopicSelect').val('');
        });

        $('#modalTopicSelect').on('change', function() {
            if ($(this).val() !== '') {
                $('#modalNewTopic').val('');
                $('#modalSubjectSelect').val('');
            }
        });

        $('#formEditTopic').on('submit', function(e) {
            e.preventDefault();

            var qid = $('#modalQuestionId').val();
            var chosenTopic = $('#modalTopicSelect').val();
            var newTitle = $('#modalNewTopic').val().trim();
            var subjectForNew = $('#modalSubjectSelect').val();

            if (!chosenTopic && !newTitle) {
                $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger')
                    .text('Pilih topik atau isi judul topik baru.');
                return;
            }
            if (newTitle && !subjectForNew) {
                $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger')
                    .text('Pilih Mata Pelajaran jika akan membuat topik baru.');
                return;
            }

            var payload = newTitle ? {
                topic_title: newTitle,
                id_subject: subjectForNew
            } : {
                id_topic: chosenTopic
            };

            $('#modalSaveBtn').prop('disabled', true).text('Menyimpan...');

            fetch(`/edit-topik-soal/${qid}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        var $tr = $(`tr[data-question-id='${qid}']`);
                        if ($tr.length) {
                            if (res.id_topic) $tr.attr('data-id_topic', res.id_topic);

                            var newLabel = res.title || newTitle;
                            if (newLabel) {
                                $tr.attr('data-topic-title', newLabel);
                                $tr.find('.topic-label').text(newLabel).attr('title', newLabel);
                            }

                            if (res.id_topic && res.title &&
                                $('#filterTopik option[value="' + res.id_topic + '"]').length === 0
                            ) {
                                $('#filterTopik').append(
                                    `<option value="${res.id_topic}">${res.title}</option>`);
                            }

                            if (typeof $.fn.DataTable === 'function' && $.fn.DataTable.isDataTable(
                                    '#soalTable')) {
                                $('#soalTable').DataTable().row($tr).invalidate().draw(false);
                            }
                        }

                        $('#modalEditAlert').removeClass('d-none alert-danger').addClass(
                                'alert-success')
                            .text('Topik berhasil diperbarui.');

                        setTimeout(function() {
                            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                            $('#modalEditAlert').addClass('d-none').removeClass(
                                'alert-success').text('');
                        }, 700);
                    } else {
                        $('#modalEditAlert').removeClass('d-none alert-success').addClass(
                                'alert-danger')
                            .text(res.message || 'Gagal menyimpan topik.');
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    $('#modalEditAlert').removeClass('d-none alert-success').addClass(
                            'alert-danger')
                        .text('Kesalahan jaringan.');
                })
                .finally(function() {
                    $('#modalSaveBtn').prop('disabled', false).text('Simpan');
                });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-soal').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                var form = this.closest('form');
                var row = this.closest('tr') || this.closest('.soal-card');
                var soalText = row?.querySelector('td:nth-child(3), .fw-semibold')?.innerText ??
                    'soal ini';
                soalText = soalText.length > 120 ? soalText.substring(0, 120) + '…' : soalText;

                Swal.fire({
                    title: 'Hapus Soal?',
                    html: `
                            <div class="text-start">
                                <p class="mb-2">Anda akan menghapus:</p>
                                <blockquote class="small border-start ps-2 text-muted">${soalText}</blockquote>
                                <small class="text-danger">⚠️ Soal yang dihapus tidak dapat dikembalikan.</small>
                            </div>
                        `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush