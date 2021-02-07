@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <edition-create title="{{$title}}"></edition-create>

@endsection
