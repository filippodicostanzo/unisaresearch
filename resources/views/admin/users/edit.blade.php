@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <user-create item="{{$item}}" role="{{$role}}" roles="{{$roles}}" countries="{{json_encode($countries)}}" password="{{$password}}"></user-create>

@endsection
