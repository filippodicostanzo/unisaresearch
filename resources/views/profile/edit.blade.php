@extends('adminlte::page')

@section ('title_prefix', 'User - ')

@section('content')
    <user-create item="{{$item}}" role="{{$role}}" roles="{{$roles}}" countries="{{json_encode($countries)}}" profile=true password="{{$password}}"></user-create>
@endsection
