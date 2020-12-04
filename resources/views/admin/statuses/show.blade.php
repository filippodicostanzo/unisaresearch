@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')

    <status-show item="{{json_encode($item)}}"></status-show>
@stop
