@extends('layouts.app')

@section('title', 'Data Dokumen')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Data Dokumen</h3>

        <div>
            <a href="{{ route('documents.import.form') }}" class="btn btn-success">
                Import CSV
            </a>

            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                Tambah Dokumen
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            @if($documents->count())
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($documents as $document)
                        <tr>
                            <td>{{ $loop->iteration + ($documents->firstItem() - 1) }}</td>

                            <td>{{ $document->title }}</td>

                            <td>{{ $document->category }}</td>

                            <td>{{ $document->created_at->format('d/m/Y') }}</td>

                            <td>
                                <a href="{{ route('documents.show', $document) }}"
                                   class="btn btn-sm btn-info">
                                    Detail
                                </a>

                                <a href="{{ route('documents.edit', $document) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('documents.destroy', $document) }}"
                                      method="POST"
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Hapus dokumen ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

                {{ $documents->links() }}

            @else
                <div class="alert alert-info mb-0">
                    Belum ada dokumen.
                </div>
            @endif

        </div>
    </div>

</div>
@endsection