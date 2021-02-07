@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert {{ session()->get('alert-class', 'alert-info') }}">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    <edition-table title="{{$title}}" items="{{json_encode($items)}}"></edition-table>
@stop
