@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert alert-success">n
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <post-table title="{{$title}}" items="{{json_encode($items)}}"></post-table>

@stop
@push('js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js" type="application/javascript"></script>
@endpush
