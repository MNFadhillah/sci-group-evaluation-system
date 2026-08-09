@extends('layouts.main')

@section('content')

<div class="container-fluid py-4">

    <div class="mb-4">
        <h3 class="fw-bold">Kelompok Aktivitas</h3>

        <p class="text-muted mb-1">
            Aktivitas:
            <strong>{{ $activity->title }}</strong>
        </p>

        <p class="text-muted">
            Jumlah siswa dalam kelas:
            <strong>{{ $students->count() }}</strong>
        </p>
    </div>


    {{-- Pesan berhasil --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- Pesan error --}}
    @if($errors->any())
        <div class="alert alert-danger">
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

    <div class="card mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                Kelompok yang Sudah Dibuat
            </h5>
        </div>

        <div class="card-body">

            @forelse($activity->groups as $group)

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between align-items-center">

    <div>
        <h5 class="mb-1">
            {{ $group->name }}
        </h5>

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

                    <hr>

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

<div class="card mb-4">

    <div class="card-header">
        <h5 class="mb-0">
            Pembentukan Kelompok Random
        </h5>
    </div>

    <div class="card-body">

        <p class="text-muted">
            Sistem akan mengacak siswa yang belum memiliki
            kelompok dan membaginya berdasarkan jumlah anggota
            per kelompok.
        </p>

        <form
            method="POST"
            action="{{ route('guru.activity.groups.random', $activity->id) }}"
        >

            @csrf

            <div class="mb-3">

                <label class="form-label fw-semibold">
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

        <div class="card-header">
            <h5 class="mb-0">
                Buat Kelompok Manual
            </h5>
        </div>

        <div class="card-body">

            <form
                method="POST"
                action="{{ route('guru.activity.groups.store', $activity->id) }}"
            >

                @csrf


                {{-- Nama kelompok --}}
                <div class="mb-4">

                    <label class="form-label fw-semibold">
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
                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Pilih Anggota Kelompok
                    </label>

                    <div class="border rounded p-3">

                        @forelse($students as $student)

                            @php
                                $isAssigned = $assignedStudentIds->contains(
                                    $student->id
                                );
                            @endphp

                            <div class="form-check mb-2">

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

@endsection