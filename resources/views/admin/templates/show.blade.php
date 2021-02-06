@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <template-show item="{{json_encode($item)}}"></template-show>
@stop
