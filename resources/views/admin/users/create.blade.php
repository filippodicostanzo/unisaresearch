@extends('adminlte::page')

@section ('title_prefix', 'User - ')

@section('content')
    <user-create roles="{{$roles}}" countries="{{json_encode($countries)}}"></user-create>

@endsection
