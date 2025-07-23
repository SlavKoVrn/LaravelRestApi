@extends('adminlte::page')

@section('content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <h1>Dynamic Table Viewer</h1>

    <label for="table_name">Select Table: </label>
    <select id="table_name" name="table_name">
        <option value="">-- Select a Table --</option>
        @php
            $tables = DB::select('SHOW TABLES');
            $key = 'Tables_in_' . env('DB_DATABASE');
        @endphp
        @foreach($tables as $table)
            <option value="{{ $table->$key }}">{{ $table->$key }}</option>
        @endforeach
    </select>

    <div id="grid-container">
        <p>Select a table to view data.</p>
    </div>

    <script>
        $('#table_name').change(function () {
            const tableName = $(this).val();
            console.log(tableName);

            if (!tableName) {
                $('#grid-container').html('<p>Select a table to view data.</p>');
                return;
            }

            $('#grid-container').html('<p class="loading">Loading...</p>');

            $.ajax({
                url: '/get-table-data',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    table_name: tableName
                },
                success: function (response) {
                    if (response.error) {
                        $('#grid-container').html('<p style="color:red;">Error: ' + response.error + '</p>');
                        return;
                    }

                    let columns = response.columns;
                    let rows = response.rows;

                    let tableHtml = '<table><thead><tr>';
                    columns.forEach(col => {
                        tableHtml += `<th>${col}</th>`;
                    });
                    tableHtml += '</tr></thead><tbody>';

                    rows.forEach(row => {
                        tableHtml += '<tr>';
                        columns.forEach(col => {
                            tableHtml += `<td>${row[col] !== null ? row[col] : '<em>NULL</em>'}</td>`;
                        });
                        tableHtml += '</tr>';
                    });

                    tableHtml += '</tbody></table>';
                    $('#grid-container').html(tableHtml);
                },
                error: function () {
                    $('#grid-container').html('<p style="color:red;">Failed to load data.</p>');
                }
            });
        });
    </script>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
