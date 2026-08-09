@extends('layouts.main')

@section('content')

<div class="container py-4">

    <div class="mb-4">

        <h3 class="fw-bold">
            Penilaian Kinerja Kelompok
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


    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


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

            <h3 class="fw-bold">
    Penilaian Anggota Kelompok
</h3>

        </div>


        <div class="card-body">

            <p class="text-muted">
                Berikan penilaian terhadap anggota kelompok
                lain berdasarkan kontribusi dan kinerjanya
                selama mengerjakan aktivitas.
            </p>


            @forelse($membersToRate as $member)

                @php
                    $rating = $ratings->get($member->id_user);
                @endphp


                <div class="border rounded p-4 mb-4">

                    <h5 class="fw-bold mb-3">

                        {{ $member->user->name ?? 'Nama tidak ditemukan' }}

                    </h5>


                    <form
                        method="POST"
                        action="{{ route(
                            'activity.group.rating.save',
                            $activity->id
                        ) }}"
                    >

                        @csrf


                        <input
                            type="hidden"
                            name="evaluated_id"
                            value="{{ $member->id_user }}"
                        >


                        {{-- Nilai --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Nilai Kinerja
                            </label>

                            <select
                                name="score"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Nilai --
                                </option>

                                @for($i = 1; $i <= 5; $i++)

                                    <option
                                        value="{{ $i }}"
                                        @selected(
                                            old(
                                                'score',
                                                $rating->score ?? ''
                                            ) == $i
                                        )
                                    >
                                        {{ $i }}
                                    </option>

                                @endfor

                            </select>

                        </div>


                        {{-- Komentar --}}
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Catatan
                            </label>

                            <textarea
                                name="comment"
                                class="form-control"
                                rows="3"
                                maxlength="1000"
                                placeholder="Tuliskan komentar mengenai kinerja anggota..."
                            >{{ old(
                                'comment',
                                $rating->comment ?? ''
                            ) }}</textarea>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            {{ $rating ? 'Perbarui Penilaian' : 'Simpan Penilaian' }}
                        </button>

                    </form>

                </div>

            @empty

                <div class="alert alert-info">
                    Tidak ada anggota lain yang dapat dinilai.
                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection