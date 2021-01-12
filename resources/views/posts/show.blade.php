@extends('adminlte::page')


@section('content')

    <post-show item="{{json_encode($item)}}" role="{{json_encode($role)}}"></post-show>
@stop
