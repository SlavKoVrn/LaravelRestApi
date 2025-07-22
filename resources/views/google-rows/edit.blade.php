<!-- resources/views/google-rows/edit.blade.php -->
@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Rows' => route('google-rows.index'),
            'Edit Row' => ''
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')
<div class="container">
    <form action="{{ route('google-rows.update', $googleRow) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Google Row</label>
            <input type="text" name="google_row" class="form-control" value="{{ old('google_row', $googleRow->google_row) }}" required>
        </div>
        <div class="mb-3">
            <label>Text</label>
            <input type="text" name="text" class="form-control" value="{{ old('text', $googleRow->text) }}" required>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="Allowed" {{ $googleRow->status == 'Allowed' ? 'selected' : '' }}>Allowed</option>
                <option value="Prohibited" {{ $googleRow->status == 'Prohibited' ? 'selected' : '' }}>Prohibited</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('google-rows.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
