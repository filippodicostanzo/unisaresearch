@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <user-table title="{{$title}}" items="{{json_encode($items)}}"></user-table>
    <!-- <user-table title="{{$title}}" items="{{json_encode($items)}}"></user-table> -->
@stop
