@extends('layouts.main')

@section('content')

<div class="container-fluid py-4">

    <div class="mb-4">

        <h3 class="fw-bold">
            Edit Anggota Kelompok
        </h3>

        <p class="text-muted mb-1">
            Aktivitas:
            <strong>{{ $activity->title }}</strong>
        </p>

        <p class="text-muted">
            Kelompok:
            <strong>{{ $group->name }}</strong>
        </p>

    </div>


    {{-- Error --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">
                Pilih Anggota Kelompok
            </h5>

        </div>


        <div class="card-body">

            <form
                method="POST"
                action="{{ route(
                    'guru.activity.groups.update',
                    [
                        'activity' => $activity->id,
                        'group' => $group->id
                    ]
                ) }}"
            >

                @csrf
                @method('PUT')


                <div class="border rounded p-3 mb-4">

                    @forelse($students as $student)

                        @php

                            $isCurrentMember =
                                in_array(
                                    $student->id,
                                    $currentMemberIds
                                );

                            $isInOtherGroup =
                                in_array(
                                    $student->id,
                                    $assignedToOtherGroups
                                );

                        @endphp


                        <div class="form-check mb-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $student->id }}"
                                id="student{{ $student->id }}"

                                @checked(
                                    $isCurrentMember
                                    ||
                                    in_array(
                                        $student->id,
                                        old('student_ids', [])
                                    )
                                )

                                @disabled($isInOtherGroup)
                            >

                            <label
                                class="form-check-label"
                                for="student{{ $student->id }}"
                            >

                                {{ $student->name }}

                                @if($isInOtherGroup)

                                    <span class="text-muted">
                                        — sudah memiliki kelompok lain
                                    </span>

                                @elseif($isCurrentMember)

                                    <span class="text-success">
                                        — anggota saat ini
                                    </span>

                                @endif

                            </label>

                        </div>

                    @empty

                        <p class="text-muted mb-0">
                            Tidak ada siswa dalam kelas ini.
                        </p>

                    @endforelse

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Perubahan
                </button>


                <a
                    href="{{ route(
                        'guru.activity.groups',
                        $activity->id
                    ) }}"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

            </form>

        </div>

    </div>

</div>

@endsection