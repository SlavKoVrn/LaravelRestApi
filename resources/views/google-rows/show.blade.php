<!-- resources/views/google-rows/show.blade.php -->
@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Rows' => route('google-rows.index'),
            'Row Details' => ''
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')
<div class="container">
    <table class="table">
        <tr><th>ID</th><td>{{ $googleRow->id }}</td></tr>
        <tr><th>Google Row</th><td>{{ $googleRow->google_row }}</td></tr>
        <tr><th>Text</th><td>{{ $googleRow->text }}</td></tr>
        <tr><th>Status</th><td><span class="badge bg-{{ $googleRow->status === 'Allowed' ? 'success' : 'danger' }}">{{ $googleRow->status }}</span></td></tr>
        <tr><th>Created At</th><td>{{ $googleRow->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $googleRow->updated_at }}</td></tr>
    </table>

    <a href="{{ route('google-rows.edit', $googleRow) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('google-rows.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
