@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <edition-show item="{{json_encode($item)}}"></edition-show>
@stop
