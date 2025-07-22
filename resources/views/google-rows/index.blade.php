<!-- resources/views/google-rows/index.blade.php -->
@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Sheet Rows' => url('/'),
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')
<div class="container">

    <a href="{{ route('google-rows.create') }}" class="btn btn-success mb-3">Add New Row</a>

    <a href="{{ route('google-rows.generate') }}" class="btn btn-primary mb-3">Generate 1000 Rows</a>

    <a href="{{ route('google-rows.remove') }}" class="btn btn-danger mb-3">Remove All Rows</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Google Row</th>
                <th>Text</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($googleRows as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->google_row }}</td>
                <td>{{ $row->text }}</td>
                <td><span class="badge bg-{{ $row->status === 'Allowed' ? 'success' : 'danger' }}">
                    {{ $row->status }}
                </span></td>
                <td>
                    <a href="{{ route('google-rows.show', $row) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('google-rows.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('google-rows.destroy', $row) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $googleRows->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
