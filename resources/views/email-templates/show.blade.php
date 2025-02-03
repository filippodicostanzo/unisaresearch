@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <email-template-show :item="{{$item->toJson()}}"></email-template-show>

@endsection
