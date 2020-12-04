@extends('adminlte::page')


@section('content')

    <post-show item="{{json_encode($item)}}"></post-show>
@stop
