@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <user-create roles="{{$roles}}" countries="{{json_encode($countries)}}"></user-create>

@endsection
