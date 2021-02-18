@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')

    <post-show item="{{json_encode($item)}}" role="{{json_encode($role)}}" source="{{$source}}"></post-show>
@stop
