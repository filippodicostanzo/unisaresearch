@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <user-show item="{{json_encode($item)}}" role="{{$role}}"></user-show>
@stop
