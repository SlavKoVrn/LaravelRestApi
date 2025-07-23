<!-- resources/views/google-rows/index.blade.php -->
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
<div class="container">

    <a href="{{ route('google-tables') }}" class="btn btn-success mb-3">Add New Row</a>

    <a href="{{ route('google-tables') }}" class="btn btn-primary mb-3">Generate 1000 Rows</a>

    <a href="{{ route('google-tables') }}" class="btn btn-danger mb-3">Remove All Rows</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Form to select table -->
    <form method="GET" action="{{ route('google-tables') }}" class="mb-4">
        @csrf
        <div class="row flex-nowrap">
            <!-- Select Field -->
            <div class="col-md-6">
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

            <!-- Button -->
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Select</button>
            </div>
        </div>
    </form>

    <!-- Display Table Data if Available -->
    @if(isset($data) && $data->isNotEmpty())

        <div class="row row-md-12">
            <strong><x-pagination-summary :paginator="$data" /></strong>
        </div>

        @if($data->count() > 0)
            <div class="d-flex justify-content-center">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                <tr>
                    @foreach($columns as $column)
                        <th>{{ $column->Field }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>{{ $row->{$column->Field} ?? '' }}</td>
                        @endforeach
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
