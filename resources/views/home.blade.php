@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Welcome to this beautiful admin panel.</p>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        $(function(){
            if (localStorage.getItem('sidebar-collapse') === 'true') {
                $('body').addClass('sidebar-collapse');
            }
            $('a[data-widget="pushmenu"]').click(function(){
                if ($('body').hasClass('sidebar-collapse')) {
                    localStorage.setItem('sidebar-collapse', false);
                } else {
                    localStorage.setItem('sidebar-collapse', true);
                }
            });
        })
    </script>
@stop
