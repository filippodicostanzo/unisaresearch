@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <author-table title="{{$title}}" items="{{json_encode($items)}}"></author-table>
@stop
