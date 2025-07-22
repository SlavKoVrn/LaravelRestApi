<!-- resources/views/google-rows/create.blade.php -->
@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Rows' => route('google-rows.index'),
            'Add New Row' => ''
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')
<div class="container">
    <form action="{{ route('google-rows.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Google Row</label>
            <input type="text" name="google_row" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Text</label>
            <input type="text" name="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="Allowed">Allowed</option>
                <option value="Prohibited">Prohibited</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('google-rows.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
