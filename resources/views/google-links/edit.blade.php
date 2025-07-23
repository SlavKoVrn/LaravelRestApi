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
<h2>Edit Google Link</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('google-links.update', $googleLink) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Database Table</label>
        <select name="database_table" class="form-control @error('database_table') is-invalid @enderror" required>
            <option value="">Select Table</option>
            @foreach($tables as $table)
                <option value="{{ $table }}" {{ (old('database_table', $googleLink->database_table) == $table) ? 'selected' : '' }}>
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
               value="{{ old('google_link', $googleLink->google_link) }}" required>
        @error('google_link')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label>Google Config (JSON File)</label>
        <input type="file" name="google_config" class="form-control @error('google_config') is-invalid @enderror" accept=".json">
        <small class="text-muted">Leave empty to keep current config.</small>
        @error('google_config')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if($googleLink->google_config)
        <div class="mb-3">
            <label>Current Config (read-only)</label>
            <div class="d-flex">
            <textarea id="currentConfig" class="form-control flex-grow-1" rows="10" readonly>
                {{ $googleLink->google_config }}
            </textarea>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="copyToClipboard()">
                Copy to Clipboard
            </button>
        </div>

        <script>
            function copyToClipboard() {
                const textarea = document.getElementById('currentConfig');
                textarea.select();
                document.execCommand('copy');
                alert('Copied to clipboard!');
            }
        </script>
    @endif

    <button type="submit" class="btn btn-success">Update</button>
    <a href="{{ route('google-links.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
