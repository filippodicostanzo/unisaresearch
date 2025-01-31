@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <email-template-create :item="{{$item->toJson()}}"></email-template-create>

@endsection
