@extends('adminlte::page')

@section('content')
    <div class="container">
        <h2>Edit Record in "{{ $tableName }}" (ID: {{ $id }})</h2>

        <form action="{{ route('google-tables.update', ['tableName' => $tableName, 'id' => $id] ) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                @foreach($columns as $column)
                    @continue($column->Key == 'PRI') {{-- Skip primary key --}}
                    <div class="form-group col-md-6">
                        <label for="{{ $column->Field }}">{{ $column->Field }}</label>

                        @php
                            $value = old($column->Field, $record->{$column->Field} ?? '');
                            $type = strtolower($column->Type);
                            $isText = in_array($type, ['text', 'tinytext', 'mediumtext', 'longtext']);
                        @endphp

                        @if ($isText)
                            <textarea
                                    name="{{ $column->Field }}"
                                    id="{{ $column->Field }}"
                                    class="form-control"
                                    rows="3"
                            >{{ $value }}</textarea>
                        @else
                            <input
                                    type="text"
                                    name="{{ $column->Field }}"
                                    id="{{ $column->Field }}"
                                    value="{{ $value }}"
                                    class="form-control"
                            >
                        @endif

                        @error($column->Field)
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('google-tables.index', $tableName) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
