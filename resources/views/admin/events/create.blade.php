@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <event-create title="{{$title}}" rooms="{{json_encode($rooms)}}" posts="{{json_encode($posts)}}" events="{{json_encode($events)}}" edition="{{json_encode($edition)}}"></event-create>
@endsection
