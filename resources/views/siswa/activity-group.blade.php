@extends('layouts.main')

@section('content')

<div class="container py-4">

    <h3 class="fw-bold">
        {{ $activity->title }}
    </h3>

    <p class="text-muted">
        Kelompok: <strong>{{ $group->name }}</strong>
    </p>

    <div class="card">
    <div class="card-header">
        <h5 class="mb-0">Anggota Kelompok</h5>
    </div>

    <div class="card-body">

        <ul class="mb-3">
            @foreach($group->members as $member)
                <li>
                    {{ $member->user->name ?? 'Nama tidak ditemukan' }}

                    @if($member->id_user == auth()->id())
                        <strong>(Anda)</strong>
                    @endif
                </li>
            @endforeach
        </ul>

        {{-- Menu penilaian anggota --}}
        @if($group->members->count() > 1)
            <div class="border-top pt-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>
                        <div class="fw-semibold">
                            Penilaian Anggota
                        </div>

                        <small class="text-muted">
                            Berikan penilaian terhadap kontribusi dan kinerja anggota kelompok lainnya.
                        </small>
                    </div>

                    <a
                        href="{{ route('activity.group.rating', $activity->id) }}"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-star-fill me-1"></i>
                        Nilai Anggota
                    </a>

                </div>
            </div>
        @endif

    </div>
</div>

</div>

@endsection