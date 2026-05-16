@extends('layouts.main')
@section('dataMatrix','active')

@section('content')
<div class="container py-4">

    {{-- ===== HEADER ===== --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-grid-3x3-gap-fill fs-4 text-primary"></i>
        <h4 class="fw-bold mb-0">Matriks Aktivitas</h4>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">
                                <i class="bi bi-mortarboard-fill me-1 text-secondary"></i>
                                Kelas
                            </th>
                            <th class="text-start">
                                <i class="bi bi-journal-text me-1 text-secondary"></i>
                                Aktivitas
                            </th>
                            <th style="width:160px">
                                <i class="bi bi-eye-fill me-1 text-secondary"></i>
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($classes as $class)
                            @php
                                $activities = DB::table('activities')
                                    ->join('topics','topics.id','=','activities.id_topic')
                                    ->join('subject','subject.id','=','topics.id_subject')
                                    ->where('subject.id_class',$class->id)
                                    ->select('activities.id','activities.title')
                                    ->get();
                            @endphp

                            @foreach($activities as $act)
                                <tr>
                                    {{-- KELAS --}}
                                    <td class="text-start fw-semibold">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                            <i class="bi bi-people-fill me-1"></i>
                                            {{ $class->name }}
                                        </span>
                                    </td>

                                    {{-- AKTIVITAS --}}
                                    <td class="text-start">
                                        <i class="bi bi-clipboard-check me-1 text-success"></i>
                                        {{ $act->title }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td>
                                        <a href="{{ route('activity.matrix', [$act->id, $class->id]) }}"
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-table me-1"></i>
                                            Lihat Matriks
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted py-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Belum ada data aktivitas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>
@endsection
