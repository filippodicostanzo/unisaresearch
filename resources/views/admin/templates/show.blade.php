@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')

    <template-show item="{{json_encode($item)}}"></template-show>
@stop
