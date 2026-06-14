@extends('layouts.app')

@section('title', 'Tambah Dokumen')

@section('content')
<div class="container">

    <div class="card">
        <div class="card-header">
            <h4>Tambah Dokumen</h4>
        </div>

        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Isi Dokumen</label>
                    <textarea name="content"
                              class="form-control"
                              rows="8"
                              required>{{ old('content') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text"
                           name="category"
                           class="form-control"
                           value="{{ old('category') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Penulis</label>
                    <input type="text"
                           name="author"
                           class="form-control"
                           value="{{ old('author') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Sumber</label>
                    <input type="text"
                           name="source"
                           class="form-control"
                           value="{{ old('source') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Publikasi</label>
                    <input type="date"
                           name="published_at"
                           class="form-control"
                           value="{{ old('published_at') }}">
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan
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