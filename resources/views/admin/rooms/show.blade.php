@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <room-show item="{{json_encode($item)}}"></room-show>
@stop
