@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2>Indexing TF-IDF</h2>

    <form action="{{ route('indexing.build') }}" method="POST">
        @csrf

        <button type="submit" class="btn btn-primary">
            Build Index
        </button>
    </form>

</div>
@endsection