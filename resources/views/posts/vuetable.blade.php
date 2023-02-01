@extends('adminlte::page')

@section ('title_prefix',  __($title).' - ')

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


    <posts-vue-table title="{{$title}}" items="{{json_encode($items)}}" role="{{json_encode($roles)}}" reviews="{{json_encode($reviews)}}" user="{{json_encode($user)}}" statuses="{{json_encode($statuses)}}" source="{{$source}}" categories="{{json_encode($categories)}}"></posts-vue-table>



@stop
@push('js')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js" type="application/javascript"></script>
@endpush
