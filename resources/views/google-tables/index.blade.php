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

    <a href="{{ route('google-tables.export', $tableName) }}" class="btn btn-warning mb-3">Export Sheet</a>

    <a href="{{ route('google-tables.import', $tableName) }}" class="btn btn-info mb-3">Import Sheet</a>

    <a href="javascript:void(0);" class="btn btn-warning mb-3" onclick="startExportProgressive('{{ $tableName }}')">
        Export with Progress
    </a>

    <a href="javascript:void(0);" class="btn btn-info mb-3" onclick="startImportProgressive('{{ $tableName }}')">
        Import with Progress
    </a>

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

<!-- Export Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporting to Google Sheets</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="progressStatus">Fetching total rows...</p>
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

<!-- Import Progress Modal -->
<div class="modal fade" id="importProgressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importing from Google Sheets</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="importProgressStatus">Fetching total rows...</p>
                <div class="progress">
                    <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
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
        async function startExportProgressive(tableName) {
            const $modal = $('#progressModal');
            const $bar = $('#progressBar');
            const $status = $('#progressStatus');

            $modal.modal('show');
            $bar.css('width', '0%').removeClass('bg-success bg-danger').addClass('progress-bar-animated');
            $status.text('Initializing...');

            const token = '{{ csrf_token() }}';
            const initUrl = "{{ route('google-tables.export-init', ['tableName' => '__TABLE__']) }}".replace('__TABLE__', encodeURIComponent(tableName));
            const chunkUrl = "{{ route('google-tables.export-chunk', ['tableName' => '__TABLE__']) }}".replace('__TABLE__', encodeURIComponent(tableName));

            try {
                // Step 1: Get total count
                const initRes = await fetch(initUrl);
                const initJson = await initRes.json();

                if (initRes.status !== 200 || initJson.error) {
                    throw new Error(initJson.error || 'Failed to get row count');
                }

                const total = initJson.total;
                if (total === 0) {
                    $status.text('No data to export.');
                    return;
                }

                const NUM_CHUNKS = 10;
                const delta = Math.ceil(total / NUM_CHUNKS);
                let processed = 0;

                $status.text(`Total: ${total} rows → ${NUM_CHUNKS} chunks of ~${delta} rows`);

                // Step 2: Send 100 sequential chunk requests
                for (let i = 0; i < NUM_CHUNKS; i++) {
                    const begin = i * delta;

                    try {
                        const res = await fetch(chunkUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token
                            },
                            body: `begin=${begin}&delta=${delta}&_token=${token}`
                        });

                        const data = await res.json();

                        if (!data.success) {
                            throw new Error(data.error || 'Chunk failed');
                        }

                        processed += data.written;

                        // Update progress
                        const progress = Math.round(((i + 1) / NUM_CHUNKS) * 100);
                        $bar.css('width', progress + '%');
                        $status.text(`Uploaded chunk ${i + 1}/${NUM_CHUNKS} → ${processed}/${total} rows`);

                        // Optional: small delay to avoid rate limits
                        await new Promise(r => setTimeout(r, 100));

                    } catch (err) {
                        $bar.removeClass('progress-bar-animated').addClass('bg-danger');
                        $status.text(`Error at chunk ${i + 1}: ${err.message}`);
                        console.error(err);
                        return;
                    }
                }

                // Final success
                $bar.removeClass('progress-bar-animated').addClass('bg-success');
                $status.text(`✅ Successfully exported ${processed} rows!`);
                setTimeout(() => $modal.modal('hide'), 1500);

            } catch (err) {
                $bar.removeClass('progress-bar-animated').addClass('bg-danger');
                $status.text(`Failed: ${err.message}`);
                console.error(err);
            }
        }

        async function startImportProgressive(tableName) {
            const $modal = $('#importProgressModal');
            const $bar = $('#importProgressBar');
            const $status = $('#importProgressStatus');

            $modal.modal('show');
            $bar.css('width', '0%').removeClass('bg-success bg-danger').addClass('progress-bar-animated');
            $status.text('Initializing...');

            const token = '{{ csrf_token() }}';
            const initUrl = "{{ route('google-tables.import-init', ['tableName' => '__TABLE__']) }}".replace('__TABLE__', encodeURIComponent(tableName));
            const chunkUrl = "{{ route('google-tables.import-chunk', ['tableName' => '__TABLE__']) }}".replace('__TABLE__', encodeURIComponent(tableName));

            try {
                // Step 1: Get total count
                const initRes = await fetch(initUrl);
                const initJson = await initRes.json();

                if (initRes.status !== 200 || initJson.error) {
                    throw new Error(initJson.error || 'Failed to get row count');
                }

                const total = initJson.total;
                if (total === 0) {
                    $status.text('No data to import.');
                    return;
                }

                const NUM_CHUNKS = 10;
                const delta = Math.ceil(total / NUM_CHUNKS);
                let imported = 0;

                $status.text(`Total: ${total} rows → ${NUM_CHUNKS} chunks of ~${delta} rows`);

                // Step 2: Import 100 chunks
                for (let i = 0; i < NUM_CHUNKS; i++) {
                    const begin = i * delta;

                    try {
                        const res = await fetch(chunkUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': token
                            },
                            body: `begin=${begin}&delta=${delta}&_token=${token}`
                        });

                        const data = await res.json();

                        if (!data.success) {
                            throw new Error(data.error || 'Chunk failed');
                        }

                        imported += data.written;

                        // Update progress
                        const progress = Math.round(((i + 1) / NUM_CHUNKS) * 100);
                        $bar.css('width', progress + '%');
                        $status.text(`Imported chunk ${i + 1}/${NUM_CHUNKS} → ${imported}/${total} rows`);

                        // Small delay to avoid rate limits
                        await new Promise(r => setTimeout(r, 100));

                    } catch (err) {
                        $bar.removeClass('progress-bar-animated').addClass('bg-danger');
                        $status.text(`Error at chunk ${i + 1}: ${err.message}`);
                        console.error(err);
                        return;
                    }
                }

                // Final success
                $bar.removeClass('progress-bar-animated').addClass('bg-success');
                $status.text(`✅ Successfully imported ${imported} rows!`);
                $modal.on('hidden.bs.modal', function () {
                    location.reload();
                });
                setTimeout(() => $modal.modal('hide'), 1500);

            } catch (err) {
                $bar.removeClass('progress-bar-animated').addClass('bg-danger');
                $status.text(`Failed: ${err.message}`);
                console.error(err);
            }
        }

    </script>

@stop