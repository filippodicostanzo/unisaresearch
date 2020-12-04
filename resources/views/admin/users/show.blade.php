@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <user-show item="{{json_encode($item)}}" role="{{$role}}"></user-show>
@stop
