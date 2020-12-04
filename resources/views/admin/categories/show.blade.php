@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')

    <category-show item="{{json_encode($item)}}"></category-show>
@stop
