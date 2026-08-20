@extends('layouts.main')

@section('content')

<div id="kelompokWrap" class="container-fluid px-3 px-md-4 py-3 d-flex flex-column">

    <div class="mb-2">
        <h4 class="fw-bold mb-1">Kelompok Aktivitas</h4>

        <p class="text-muted mb-1">
            Aktivitas:
            <strong>{{ $activity->title }}</strong>
        </p>

        <p class="text-muted mb-0">
            Jumlah siswa dalam kelas:
            <strong>{{ $students->count() }}</strong>
        </p>
    </div>


    {{-- Pesan berhasil --}}
    @if(session('success'))
        <div class="alert alert-success py-2 mb-2">
            {{ session('success') }}
        </div>
    @endif


    {{-- Pesan error --}}
    @if($errors->any())
        <div class="alert alert-danger py-2 mb-2">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ============================= --}}
    {{-- KELOMPOK YANG SUDAH DIBUAT --}}
    {{-- ============================= --}}

    <div class="card mb-2">

        <div class="card-header py-2">
            <h6 class="mb-0 fw-bold">
                Kelompok yang Sudah Dibuat
            </h6>
        </div>

        <div class="card-body py-2">

            @forelse($activity->groups as $group)

                <div class="border rounded p-2 mb-2">

                    <div class="d-flex justify-content-between align-items-center">

    <div>
        <h6 class="mb-1">
            {{ $group->name }}
        </h6>

        <small class="text-muted">
            Metode:
            {{ ucfirst($group->formation_method) }}
        </small>
    </div>

    <div class="d-flex align-items-center gap-2">

    <span class="badge bg-primary">
        {{ $group->members->count() }} anggota
    </span>


    <a
        href="{{ route(
            'guru.activity.groups.edit',
            [
                'activity' => $activity->id,
                'group' => $group->id
            ]
        ) }}"
        class="btn btn-sm btn-outline-primary"
    >
        Edit Anggota
    </a>


    <form
        method="POST"
        action="{{ route(
            'guru.activity.groups.destroy',
            [
                'activity' => $activity->id,
                'group' => $group->id
            ]
        ) }}"
        onsubmit="return confirm('Yakin ingin menghapus kelompok ini?')"
    >

        @csrf
        @method('DELETE')

        <button
            type="submit"
            class="btn btn-sm btn-outline-danger"
        >
            Hapus
        </button>

    </form>

</div>

</div>

                    <hr class="my-2">

                    <ul class="mb-0">

                        @forelse($group->members as $member)

                            <li>
                                {{ $member->user->name ?? 'Nama tidak ditemukan' }}
                            </li>

                        @empty

                            <li class="text-muted">
                                Belum ada anggota.
                            </li>

                        @endforelse

                    </ul>

                </div>

            @empty

                <div class="text-muted">
                    Belum ada kelompok untuk aktivitas ini.
                </div>

            @endforelse

        </div>

    </div>
    {{-- ============================= --}}
{{-- PEMBENTUKAN KELOMPOK RANDOM --}}
{{-- ============================= --}}

<div class="card mb-2">

    <div class="card-header py-2">
        <h6 class="mb-0 fw-bold">
            Pembentukan Kelompok Random
        </h6>
    </div>

    <div class="card-body py-2">

        <p class="text-muted small mb-2">
            Sistem akan mengacak siswa yang belum memiliki
            kelompok dan membaginya berdasarkan jumlah anggota
            per kelompok.
        </p>

        <form
            method="POST"
            action="{{ route('guru.activity.groups.random', $activity->id) }}"
            class="d-flex align-items-end gap-2 flex-wrap"
        >

            @csrf

            <div class="mb-0" style="min-width:220px;">

                <label class="form-label fw-semibold mb-1">
                    Jumlah Anggota per Kelompok
                </label>

                <input
                    type="number"
                    name="members_per_group"
                    class="form-control"
                    min="1"
                    max="{{ max(1, $students->count()) }}"
                    value="2"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-success"
            >
                Acak Kelompok
            </button>

        </form>

    </div>

</div>


    {{-- ============================= --}}
    {{-- BUAT KELOMPOK MANUAL --}}
    {{-- ============================= --}}

    <div class="card">

        <div class="card-header py-2">
            <h6 class="mb-0 fw-bold">
                Buat Kelompok Manual
            </h6>
        </div>

        <div class="card-body py-2">

            <form
                method="POST"
                action="{{ route('guru.activity.groups.store', $activity->id) }}"
            >

                @csrf


                {{-- Nama kelompok --}}
                <div class="mb-2">

                    <label class="form-label fw-semibold mb-1">
                        Nama Kelompok
                    </label>

                    <input
                        type="text"
                        name="group_name"
                        class="form-control"
                        placeholder="Contoh: Kelompok 1"
                        value="{{ old('group_name') }}"
                        required
                    >

                </div>


                {{-- Daftar siswa --}}
                <div class="mb-2">

                    <label class="form-label fw-semibold mb-1">
                        Pilih Anggota Kelompok
                    </label>

                    <div class="border rounded p-2" style="max-height:260px; overflow-y:auto;">

                        @forelse($students as $student)

                            @php
                                $isAssigned = $assignedStudentIds->contains(
                                    $student->id
                                );
                            @endphp

                            <div class="form-check mb-1">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="student_ids[]"
                                    value="{{ $student->id }}"
                                    id="student{{ $student->id }}"

                                    @checked(
                                        in_array(
                                            $student->id,
                                            old('student_ids', [])
                                        )
                                    )

                                    @disabled($isAssigned)
                                >

                                <label
                                    class="form-check-label"
                                    for="student{{ $student->id }}"
                                >

                                    {{ $student->name }}

                                    @if($isAssigned)
                                        <span class="text-muted">
                                            — sudah memiliki kelompok
                                        </span>
                                    @endif

                                </label>

                            </div>

                        @empty

                            <p class="text-muted mb-0">
                                Belum ada siswa yang terdaftar
                                pada kelas aktivitas ini.
                            </p>

                        @endforelse

                    </div>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Kelompok
                </button>

            </form>

        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Isi ruang kosong antara konten dan footer secara dinamis,
        // konsisten dengan halaman Tambah Soal / Generator Soal / Atur Soal.
        function findFooter() {
            return document.querySelector('footer') ||
                document.querySelector('.footer') ||
                document.querySelector('[class*="footer"]');
        }

        function adjustWrapHeight() {
            const wrap = document.getElementById('kelompokWrap');
            if (!wrap) return;

            wrap.style.minHeight = '0px';

            const top = wrap.getBoundingClientRect().top;
            const footer = findFooter();
            const footerHeight = footer ? footer.getBoundingClientRect().height : 0;
            const available = window.innerHeight - top - footerHeight;

            wrap.style.minHeight = Math.max(available, 0) + 'px';
        }

        adjustWrapHeight();
        window.addEventListener('resize', adjustWrapHeight);
        window.addEventListener('load', adjustWrapHeight);
    });
</script>

@endsection