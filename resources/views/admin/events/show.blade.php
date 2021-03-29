@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <event-show item="{{json_encode($item)}}"></event-show>
@stop
