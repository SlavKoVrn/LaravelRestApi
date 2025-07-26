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
<h2>Create Google Link</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('google-links.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label>Database Table</label>
        <select name="database_table" class="form-control @error('database_table') is-invalid @enderror" required>
            <option value="">Select Table</option>
            @foreach($tables as $table)
                <option value="{{ $table }}" {{ old('database_table') == $table ? 'selected' : '' }}>
                    {{ $table }}
                </option>
            @endforeach
        </select>
        @error('database_table')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label>Google Link</label>
        <input type="url" name="google_link" class="form-control @error('google_link') is-invalid @enderror"
               value="{{ old('google_link') }}" required>
        @error('google_link')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label>SpreadSheet List</label>
        <input type="text" name="spreadsheet_list" class="form-control @error('spreadsheet_list') is-invalid @enderror"
               value="{{ old('spreadsheet_list') }}" required>
        @error('spreadsheet_list')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label>Google Config (JSON File)</label>
        <input type="file" name="google_config" class="form-control @error('google_config') is-invalid @enderror"
               accept=".json" >
        @error('google_config')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('google-links.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
