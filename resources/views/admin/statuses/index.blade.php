@extends('adminlte::page')


@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <status-table title="{{$title}}" items="{{json_encode($items)}}"></status-table>
@stop
