@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <status-show item="{{json_encode($item)}}"></status-show>
@stop
