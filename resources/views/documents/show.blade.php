@extends('layouts.app')

@section('title', 'Detail Dokumen')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h3>Detail Dokumen</h3>

        <div>
            <a href="{{ route('documents.edit', $document) }}"
               class="btn btn-warning">
                Edit
            </a>

            <a href="{{ route('documents.index') }}"
               class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Informasi Dokumen
        </div>

        <div class="card-body">

            <h4>{{ $document->title }}</h4>

            <table class="table">
                <tr>
                    <th width="200">Kategori</th>
                    <td>{{ $document->category ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Penulis</th>
                    <td>{{ $document->author ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Sumber</th>
                    <td>{{ $document->source ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Tanggal Publikasi</th>
                    <td>
                        {{ $document->published_at
                            ? \Carbon\Carbon::parse($document->published_at)->format('d-m-Y')
                            : '-' }}
                    </td>
                </tr>
            </table>

            <hr>

            <h5>Isi Dokumen</h5>

            <div class="border rounded p-3">
                {!! nl2br(e($document->content)) !!}
            </div>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Hasil Preprocessing
        </div>

        <div class="card-body">

            @if(!empty($preprocessingResult))
                <pre>{{ print_r($preprocessingResult, true) }}</pre>
            @else
                <div class="alert alert-info mb-0">
                    Belum ada hasil preprocessing.
                </div>
            @endif

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Top 20 TF-IDF
        </div>

        <div class="card-body">

            @if($tfidfData->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>TF</th>
                            <th>IDF</th>
                            <th>TF-IDF</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($tfidfData as $item)
                        <tr>
                            <td>{{ $item->term->term ?? '-' }}</td>
                            <td>{{ $item->tf }}</td>
                            <td>{{ number_format($item->idf, 4) }}</td>
                            <td>{{ number_format($item->tfidf, 4) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info mb-0">
                    Belum ada data TF-IDF.
                </div>
            @endif

        </div>
    </div>

</div>
@endsection