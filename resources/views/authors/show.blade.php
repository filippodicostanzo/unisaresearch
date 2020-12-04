@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <author-show item="{{json_encode($item)}}"></author-show>
@stop
