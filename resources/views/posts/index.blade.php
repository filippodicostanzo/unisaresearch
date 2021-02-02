@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content')
    <div class="notification">
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{session()->get('message')}}
            </div>
        @endif
    </div>
    @php
    use Illuminate\Support\Facades\Auth;
        $user = Auth::user();
        $roles = $user->roles()->first();

    @endphp

    <post-table title="{{$title}}" items="{{json_encode($items)}}" role="{{json_encode($roles)}}" reviews="{{json_encode($reviews)}}" user="{{json_encode($user)}}"></post-table>

@stop
@push('js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js" type="application/javascript"></script>
@endpush
