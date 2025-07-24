@extends('adminlte::page')
@section('title', 'Create New Record')

@section('content_header')
    <h1>Create New Record in {{ $tableName }}</h1>
    @php
        $breadcrumbs = [
            $tableName => route('google-tables.index', $tableName),
            'Create' => '',
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

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('google-tables.store', $tableName) }}">
                @csrf
                <div class="form-row">
                    @foreach($createColumns as $column)
                        <div class="form-group col-md-6">
                            <label for="{{ $column->Field }}">{{ $column->Field }}</label>
                            @php
                                $type = strtolower(explode('(', $column->Type)[0]);
                                $maxLength = null;
                                if (preg_match('/\((\d+)\)/', $column->Type, $matches)) {
                                    $maxLength = $matches[1];
                                }
                            @endphp

                            @if(in_array($type, ['text', 'mediumtext', 'longtext']))
                                <textarea
                                        name="{{ $column->Field }}"
                                        id="{{ $column->Field }}"
                                        class="form-control @error($column->Field) is-invalid @enderror"
                                        rows="3"
                                >{{ old($column->Field) }}</textarea>
                            @else
                                <input
                                        type="text"
                                        name="{{ $column->Field }}"
                                        id="{{ $column->Field }}"
                                        class="form-control @error($column->Field) is-invalid @enderror"
                                        value="{{ old($column->Field) }}"
                                        @if($maxLength) maxlength="{{ $maxLength }}" @endif
                                />
                            @endif

                            @error($column->Field)
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">Create Record</button>
                    <a href="{{ route('google-tables.index', $tableName) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    @include('sidebar_collapse')
@stop
