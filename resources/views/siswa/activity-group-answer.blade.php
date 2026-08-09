@extends('layouts.main')

@section('content')

<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">

        <h3 class="fw-bold">
            {{ $activity->title }}
        </h3>

        <p class="text-muted mb-1">
            Kelompok:
            <strong>{{ $group->name }}</strong>
        </p>

        <p class="text-muted">
            Anggota:
            <strong>{{ $group->members->count() }}</strong>
        </p>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">

    <div>
        <div class="fw-semibold">
            Sudah selesai menjawab?
        </div>
        <small class="text-muted">
            Lanjutkan dengan memberikan penilaian kepada anggota kelompok lainnya.
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


    {{-- Pesan berhasil --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- Daftar soal --}}
    @foreach($questions as $question)

        <div class="card mb-4">

            <div class="card-header">

                <strong>
                    Soal {{ $loop->iteration }}
                </strong>

            </div>


            <div class="card-body">

                {{-- Isi soal --}}
                @php
                    $questionData = is_string($question->question)
                        ? json_decode($question->question)
                        : $question->question;
                @endphp

                <div class="mb-4">

                    <p class="fw-semibold mb-2">
                        {{ $questionData->text ?? '-' }}
                    </p>

                </div>


                {{-- Jawaban setiap anggota --}}
                <h6 class="fw-bold mb-3">
                    Jawaban Anggota Kelompok
                </h6>


                @foreach($group->members as $member)

                    @php

                        $key = $question->id . '_' . $member->id_user;

                        $existingAnswer = $answers->get($key);

                        $isMe = $member->id_user == $currentUser->id;

                    @endphp


                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between mb-2">

                            <strong>
                                {{ $member->user->name ?? 'Nama tidak ditemukan' }}
                            </strong>

                            @if($isMe)
                                <span class="badge bg-primary">
                                    Anda
                                </span>
                            @endif

                        </div>


                        @if($isMe)

                            <form
                                method="POST"
                                action="{{ route(
                                    'activity.group.answer.save',
                                    $activity->id
                                ) }}"
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="question_id"
                                    value="{{ $question->id }}"
                                >


                                <textarea
                                    name="answer"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tulis jawaban Anda..."
                                >{{ $existingAnswer->answer ?? '' }}</textarea>


                                <button
                                    type="submit"
                                    class="btn btn-primary mt-3"
                                >
                                    Simpan Jawaban
                                </button>

                            </form>

                        @else

                            @if($existingAnswer && $existingAnswer->answer !== null)

                                <div class="bg-light rounded p-3">

                                    {{ $existingAnswer->answer }}

                                </div>

                            @else

                                <div class="text-muted">
                                    Belum menjawab.
                                </div>

                            @endif

                        @endif

                    </div>

                @endforeach

            </div>

        </div>

    @endforeach

</div>

@endsection