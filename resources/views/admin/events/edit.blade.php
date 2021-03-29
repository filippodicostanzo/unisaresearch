@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <event-create title="{{$title}}" item="{{$item}}" rooms="{{json_encode($rooms)}}" posts="{{json_encode($posts)}}" events="{{json_encode($events)}}"></event-create>
@endsection
