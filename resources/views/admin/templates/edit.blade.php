@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <template-create item="{{$item}}"></template-create>

@endsection
