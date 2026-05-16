@extends('layouts.main')
@section('dataMatrix', 'active')

@section('content')
    <div class="container py-4">

        {{-- ===== JUDUL + EXPORT ===== --}}
        <div class="d-flex align-items-center gap-2 mb-4">

            <i class="bi bi-grid-3x3-gap-fill fs-4 text-primary"></i>
            <h4 class="fw-bold mb-0">Matriks Hasil Aktivitas</h4>

            {{-- PUSH KE KANAN --}}
            <a href="{{ route('activity.matrix.export', [$activityId, $classId]) }}"
                class="btn btn-success rounded-pill px-4 ms-auto">
                <i class="bi bi-file-earmark-excel-fill me-1"></i>
                Export Excel
            </a>

        </div>



        <div class="table-responsive shadow-sm rounded-4 overflow-hidden">
            <table class="table table-bordered align-middle text-center mb-0">

                {{-- ===== HEADER ===== --}}
                <thead class="table-light">
                    <tr>
                        <th class="text-start bg-white">
                            <i class="bi bi-question-circle me-1 text-secondary"></i>
                            Soal
                        </th>

                        @foreach($students as $s)
                            <th>
                                <i class="bi bi-person-circle me-1 text-primary"></i>
                                {{ $s->name }}
                            </th>
                        @endforeach

                        <th class="bg-light">
                            <i class="bi bi-bar-chart-fill me-1 text-success"></i>
                            Total Benar
                        </th>
                    </tr>
                </thead>

                <tbody>

                    {{-- ===== SOAL ===== --}}
                    @foreach($questions as $q)
                        <tr>
                            <td class="text-start small">
                                {!! data_get(json_decode($q->question, true), 'text', 'Soal') !!}
                            </td>

                            @foreach($students as $s)
                                @php
                                    $val = $matrix[$q->id][$s->id] ?? null;
                                @endphp
                                <td>
                                    @if($val === 1)
                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            <i class="bi bi-check-circle-fill me-1"></i> Benar
                                        </span>
                                    @elseif($val === 0)
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                            <i class="bi bi-x-circle-fill me-1"></i> Salah
                                        </span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- TOTAL BENAR PER SOAL --}}
                            <td class="fw-bold text-success">
                                <i class="bi bi-check-all me-1"></i>
                                {{ $totalCorrectPerQuestion[$q->id] ?? 0 }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- ===== TOTAL BENAR PER SISWA ===== --}}
                    <tr class="table-warning fw-semibold">
                        <td class="text-start">
                            <i class="bi bi-people-fill me-2"></i>
                            Total Benar Siswa
                        </td>

                        @foreach($students as $s)
                            <td>
                                <span class="badge bg-dark px-3 py-2">
                                    {{ $totalCorrectPerStudent[$s->id] ?? 0 }}
                                </span>
                            </td>
                        @endforeach
                        <td>-</td>
                    </tr>

                    {{-- ===== NILAI AKHIR ===== --}}
                    <tr class="table-secondary fw-bold">
                        <td class="text-start">
                            <i class="bi bi-award-fill me-2"></i>
                            Nilai Akhir
                        </td>

                        @foreach($students as $s)
                            <td>
                                <span class="badge bg-primary px-3 py-2">
                                    {{ $finalScores[$s->id] ?? '-' }}
                                </span>
                            </td>
                        @endforeach
                        <td>-</td>
                    </tr>

                </tbody>
            </table>
        </div>

    </div>
@endsection