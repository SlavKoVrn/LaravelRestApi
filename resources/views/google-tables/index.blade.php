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

    <a href="{{ route('google-tables.import', $tableName) }}" class="btn btn-info mb-3">Import Google Sheet</a>

    <button id="exportAjaxBtn" class="btn btn-warning mb-3" onclick="startExportAjax('{{ $tableName }}')">
        Export With Progress
    </button>

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

<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">Exporting Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="progressStatus">Preparing export...</p>
                <div class="progress">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    @include('sidebar_collapse')

    <script>
        function startExportAjax(tableName) {
            // Show modal
            $('#progressModal').modal('show');
            $('#progressBar').css('width', '0%').removeClass('bg-success bg-danger').addClass('progress-bar-animated');
            $('#progressStatus').text('Starting export...');

            const url = "{{ route('google-tables.export-ajax', ['tableName' => '__TABLE__']) }}".replace('__TABLE__', encodeURIComponent(tableName));
            const token = '{{ csrf_token() }}';

            const reader = fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: '_token=' + token
            })
                .then(response => {
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder('utf-8');
                    let buffer = '';

                    function read() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                // Done — maybe handle final state
                                if (!buffer.includes('success')) {
                                    $('#progressStatus').text('Completed.');
                                    $('#progressBar').css('width', '100%');
                                }
                                setTimeout(() => $('#progressModal').modal('hide'), 1000);
                                return;
                            }

                            // Append new chunk to buffer
                            buffer += decoder.decode(value, { stream: true });

                            // Try to parse all complete JSON objects
                            const lines = buffer.split('\n');
                            buffer = lines.pop(); // Keep incomplete JSON in buffer

                            lines.forEach(line => {
                                if (line.trim() === '') return;
                                try {
                                    const data = JSON.parse(line);

                                    // Update progress bar
                                    $('#progressBar').css('width', data.progress + '%');
                                    $('#progressStatus').text(`${data.status} (${data.processed || 0}/${data.total || 0})`);

                                    if (data.success === true) {
                                        $('#progressBar').removeClass('progress-bar-animated').addClass('bg-success');
                                        $('#progressStatus').text(data.message);
                                    }

                                    if (data.success === false) {
                                        $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
                                        $('#progressStatus').text('Error: ' + data.message);
                                    }

                                } catch (e) {
                                    // Ignore malformed JSON (incomplete)
                                    console.warn('Failed to parse:', line);
                                }
                            });

                            read(); // Continue reading
                        }).catch(err => {
                            $('#progressStatus').text('Connection error.');
                            $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
                            console.error('Stream error:', err);
                        });
                    }

                    read();
                })
                .catch(err => {
                    $('#progressStatus').text('Request failed.');
                    $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger');
                    console.error('Fetch error:', err);
                });
        }
    </script>

@stop
