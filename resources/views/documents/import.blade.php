@extends('layouts.app')

@section('title', 'Import Dokumen')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Import Dataset CSV</h4>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        Upload File CSV
                    </label>

                    <input type="file"
                           name="csv_file"
                           class="form-control"
                           accept=".csv"
                           required>

                    <small class="text-muted">
                        Format CSV:
                        title, content, category, author, source, published_at
                    </small>
                </div>

                <button type="submit"
                        class="btn btn-primary">
                    Import Data
                </button>

                <a href="{{ route('documents.index') }}"
                   class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>
</div>
@endsection