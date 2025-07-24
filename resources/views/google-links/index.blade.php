@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Links' => url('/'),
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<a href="{{ route('google-links.create') }}" class="btn btn-primary mb-3">Add New</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Database Table</th>
            <th>Google Link</th>
            <th>Google Config</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($googleLinks as $link)
            <tr>
                <td>{{ $link->database_table }}</td>
                <td><a href="{{ $link->google_link }}" target="_blank">Open</a></td>
                <td>{{ substr($link->google_config, 0, 50) }}</td>
                <td>
                    <a href="{{ route('google-links.edit', $link) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('google-links.destroy', $link) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
