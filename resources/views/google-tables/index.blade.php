@extends('adminlte::page')

@section('content_header')
    @php
        $breadcrumbs = [
            'Google Table view' => url('/'),
        ];
    @endphp

    <nav aria-label="breadcrumb">
        @include('partials.breadcrumbs')
    </nav>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="container">

    <a href="{{ route('google-tables.create', ['tableName' => $tableName]) }}" class="btn btn-success mb-3">Add New Row</a>

    <a href="{{ route('google-tables.generate', $tableName) }}" class="btn btn-primary mb-3">Generate 1000 Rows</a>

    <a href="{{ route('google-tables.truncate', $tableName) }}" class="btn btn-danger mb-3">Remove All Rows</a>

    <a href="{{ route('google-tables.export', $tableName) }}" class="btn btn-warning mb-3">Export Google Sheet</a>

    <a href="{{ route('google-tables.import', $tableName) }}" class="btn btn-secondary mb-3">Import Google Sheet</a>

    <!-- Form to select table -->
    <form method="GET" action="{{ route('google-tables') }}" class="mb-4">
        @csrf
        <div class="row flex-nowrap">
            <!-- Select Field -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="database_table">Select Table</label>
                    <select name="database_table" id="table_id" class="form-control" required>
                        <option value="">-- Choose a Table --</option>
                        @foreach($googleLinks as $link)
                            <option value="{{ $link->database_table }}"
                                    {{ (isset($googleLink) && $googleLink->database_table == $link->database_table) ? 'selected' : '' }}>
                                {{ $link->database_table }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Search -->
            <div class="col-md-4">
                <label>Search</label>
                <input name="search" id="search" class="form-control" value="{{ $search }}" />
            </div>

            <!-- Button -->
            <div class="col-md-2">
                <label>show records</label>
                <button type="submit" class="btn btn-success btn-block">Show</button>
            </div>

            @php
                $link = optional($googleLink)->google_link;
            @endphp

            @if($link)
                <div class="col-md-2">
                    <label>show google sheet</label>
                    <a href="{{ $link }}" target="_blank" class="btn btn-primary mb-3">Google Sheet</a>
                </div>
            @endif

        </div>
    </form>

    <!-- Display Table Data if Available -->
    @if(isset($data) && $data->isNotEmpty())

        @if($data->count() > 0)
            <div class="d-flex justify-content-center">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        @endif

        <div class="row row-md-12">
            <strong><x-pagination-summary :paginator="$data" /></strong>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                <tr>
                    @foreach($columns as $column)
                        <th>{{ $column->Field }}</th>
                    @endforeach
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>{{ $row->{$column->Field} ?? '' }}</td>
                        @endforeach
                        <td>
                            {{-- Edit Button --}}
                            <a href="{{ route('google-tables.edit', [$tableName, $row->id]) }}"
                               class="btn btn-sm btn-warning text-white">
                                Edit
                            </a>

                            {{-- Delete Form --}}
                            <form action="{{ route('google-tables.destroy', [$tableName, $row->id]) }}"
                                  method="POST"
                                  style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this record?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if($data->count() > 0)
            <div class="d-flex justify-content-center">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        @endif

    @elseif(isset($data))
        <div class="alert alert-info">No data found in this table.</div>
    @endif

</div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
