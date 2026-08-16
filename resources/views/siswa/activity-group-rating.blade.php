@extends('layouts.main')

@section('content')
    <div class="container py-4">

        <!-- Header Info & Tombol Panduan -->
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-primary mb-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>

            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    Penilaian Kinerja Kelompok
                </h3>
                <!-- Tombol Modal Info -->
                <button type="button"
                    class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                    style="width:30px;height:30px" data-bs-toggle="modal" data-bs-target="#modalInfoRating"
                    title="Panduan Penilaian">
                    <i class="fas fa-info fw-bold"></i>
                </button>
            </div>
            <p class="text-muted mb-0">Aktivitas: <strong class="text-primary">{{ $activity->title }}</strong> | Kelompok:
                <strong>{{ $group->name }}</strong>
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success shadow-sm">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger shadow-sm">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        @php
            // 1. Tarik data teman dari Controller
            $peers = $membersToRate;

            // 2. Tarik data Diri Sendiri secara paksa dari sistem Login
            $selfId = auth()->id();
            $selfName = auth()->user()->name;
            $selfRating = $ratings->where('id_evaluated', $selfId)->first();
        @endphp

        <!-- FORM PENILAIAN MASAL -->
        <form method="POST" action="{{ route('activity.group.rating.save', $activity->id) }}" id="ratingForm">
            @csrf

            <div class="row g-4">

                <!-- ========================================== -->
                <!-- SISI KIRI: PENILAIAN TEMAN KELOMPOK -->
                <!-- ========================================== -->
                <div class="col-lg-6">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fas fa-users me-2"></i> Penilaian Teman Kelompok
                    </h5>

                    @forelse($peers as $member)
                        @php
                            $rating = $ratings->where('id_evaluated', $member->id_user)->first();
                        @endphp

                        <div class="card shadow-sm border-0 mb-3 rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="fas fa-user text-secondary"></i>
                                    {{ $member->user->name ?? 'Nama tidak ditemukan' }}
                                </h6>

                                <div class="row g-3">
                                    {{-- Input Poin Teman --}}
                                    <div class="col-sm-5">
                                        <label class="form-label fw-bold text-dark small">Porsi Kontribusi</label>
                                        <div class="input-group">
                                            <input type="number" name="ratings[{{ $member->id_user }}][score]"
                                                class="form-control fw-bold" min="0" max="100" required
                                                placeholder="0"
                                                value="{{ old('ratings.' . $member->id_user . '.score', $rating->score ?? '') }}">
                                            <span class="input-group-text bg-light text-muted">Poin</span>
                                        </div>
                                    </div>
                                    {{-- Input Komentar Teman --}}
                                    <div class="col-sm-7">
                                        <label class="form-label fw-bold text-dark small">Catatan (Opsional)</label>
                                        <textarea name="ratings[{{ $member->id_user }}][comment]" class="form-control" rows="1" maxlength="1000"
                                            placeholder="Kinerja teman ini...">{{ old('ratings.' . $member->id_user . '.comment', $rating->comment ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary border-0 shadow-sm rounded-4">Tidak ada teman kelompok yang dapat
                            dinilai.</div>
                    @endforelse
                </div>

                <!-- ========================================== -->
                <!-- SISI KANAN: PENILAIAN DIRI SENDIRI & SUBMIT -->
                <!-- ========================================== -->
                <div class="col-lg-6">
                    <!-- Elemen Sticky agar menempel saat di-scroll -->
                    <div class="sticky-top" style="top: 20px;">

                        <h5 class="fw-bold mb-3 text-success border-bottom pb-2">
                            <i class="fas fa-user-circle me-2"></i> Penilaian Diri Sendiri
                        </h5>

                        <div class="card shadow-sm border-success border-2 mb-4 rounded-4 bg-success-subtle bg-opacity-10">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2 text-success">
                                    <i class="fas fa-user-check"></i>
                                    {{ $selfName }}
                                    <span class="badge bg-success ms-1">Anda Sendiri</span>
                                </h6>

                                <div class="row g-3">
                                    {{-- Input Poin Diri Sendiri --}}
                                    <div class="col-sm-5">
                                        <label class="form-label fw-bold text-dark small">Porsi Kontribusi Saya</label>
                                        <div class="input-group">
                                            <input type="number" name="ratings[{{ $selfId }}][score]"
                                                class="form-control border-success fw-bold text-success" min="0"
                                                max="100" required placeholder="0"
                                                value="{{ old('ratings.' . $selfId . '.score', $selfRating->score ?? '') }}">
                                            <span class="input-group-text bg-white border-success">Poin</span>
                                        </div>
                                    </div>
                                    {{-- Input Komentar Diri Sendiri --}}
                                    <div class="col-sm-7">
                                        <label class="form-label fw-bold text-dark small">Pekerjaan Saya (Ops.)</label>
                                        <textarea name="ratings[{{ $selfId }}][comment]" class="form-control border-success" rows="1"
                                            maxlength="1000" placeholder="Tugas yang saya kerjakan...">{{ old('ratings.' . $selfId . '.comment', $selfRating->comment ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOMBOL SUBMIT -->
                        <div class="card border-0 bg-light shadow-sm rounded-4 mt-4">
                            <div class="card-body p-4 text-center">
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle text-primary me-1"></i> Pastikan total seluruh poin yang
                                    Anda bagikan (Teman + Diri Sendiri) berjumlah tepat <strong>100 Poin</strong>.
                                </p>

                                <!-- Tombol Langsung Aktif -->
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> Simpan Penilaian
                                </button>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>
        <form method="POST" action="{{ route('activity.group.finish', $activity->id) }}" class="mt-3"
            onsubmit="return confirm('Yakin ingin menyelesaikan aktivitas? Pastikan semua soal sudah dijawab dan penilaian anggota sudah disimpan.');">
            @csrf

            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                <i class="fas fa-check-circle me-2"></i>
                Selesaikan Aktivitas
            </button>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- MODAL INFO PANDUAN PENGISIAN -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalInfoRating" tabindex="-1" aria-labelledby="modalInfoRatingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 shadow border-0">
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalInfoRatingLabel">
                        <i class="fas fa-info-circle me-2"></i> Panduan Pembagian Poin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">
                        Anda diberikan total <strong>100 Poin</strong> untuk dibagikan kepada <strong>seluruh anggota
                            kelompok</strong>, termasuk diri Anda sendiri.
                    </p>

                    <div class="bg-light p-3 rounded border mb-3">
                        <p class="fw-bold text-dark mb-2">Contoh Skenario (1 kelompok 2 orang):</p>
                        <ul class="text-muted small mb-0 ps-3">
                            <li class="mb-1">Jika Anda berdua bekerja sama dengan adil, bagikan <strong>50 Poin</strong>
                                untuk teman dan <strong>50 Poin</strong> untuk Anda.</li>
                            <li>Jika Anda merasa mengerjakan tugas jauh lebih banyak, Anda bisa memberi nilai misalnya
                                <strong>70 Poin</strong> untuk diri sendiri dan <strong>30 Poin</strong> untuk teman.
                            </li>
                            <li>Jika Anda merasa mengerjakan tugas jauh lebih sedikit secara sadar, Anda bisa memberi nilai
                                misalnya <strong>30 Poin</strong> untuk diri sendiri dan <strong>70 Poin</strong> untuk
                                teman.</li>
                        </ul>
                    </div>

                    <p class="text-danger small fw-bold mb-0 text-center">
                        * Pastikan total keseluruhan poin yang Anda masukkan tidak kurang atau lebih dari 100 Poin.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[type="number"][name^="ratings"]');
        const btnSubmit = document.querySelector('button[type="submit"]');
        
        // Buat elemen visual untuk total secara dinamis
        const infoText = document.querySelector('.card-body p.text-muted.small.mb-3');
        const totalDisplay = document.createElement('div');
        totalDisplay.className = 'alert alert-info fw-bold mb-3 py-2 fs-5 transition-all';
        totalDisplay.innerHTML = 'Total Poin Terbagi: <span id="currentTotal">0</span> / 100';
        infoText.parentNode.insertBefore(totalDisplay, infoText.nextSibling);
        
        const spanTotal = document.getElementById('currentTotal');

        function calculateTotal() {
            let total = 0;
            inputs.forEach(input => {
                total += parseInt(input.value || 0);
            });
            
            spanTotal.innerText = total;
            
            if(total === 100) {
                totalDisplay.classList.remove('alert-danger', 'alert-info');
                totalDisplay.classList.add('alert-success');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Simpan Penilaian';
            } else {
                totalDisplay.classList.remove('alert-success', 'alert-info');
                totalDisplay.classList.add('alert-danger');
                btnSubmit.disabled = true;
                
                let selisih = 100 - total;
                if(selisih > 0) {
                    btnSubmit.innerHTML = `<i class="fas fa-lock me-2"></i> Kurang ${selisih} Poin Lagi`;
                } else {
                    btnSubmit.innerHTML = `<i class="fas fa-lock me-2"></i> Kelebihan ${Math.abs(selisih)} Poin`;
                }
            }
        }

        // Pasang event listener ke setiap inputan
        inputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });
        
        // Jalankan saat pertama kali halaman dimuat (untuk membaca old input)
        calculateTotal();
    });
</script>
@endpush

@endsection
@endsection
