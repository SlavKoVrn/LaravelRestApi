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

    {{ $googleRows->links() }} <!-- Pagination -->
</div>
@endsection