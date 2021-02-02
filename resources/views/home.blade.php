@extends('adminlte::page')

@section('title', 'AdminLTE')

@php
    use App\Models\Post;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

$items=Post::orderBy('id', 'DESC')->limit(20)->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();

$user=Auth::user();
$roles = $user->roles()->first();

$postresearcher =  Post::where('created','=',Auth::id())->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->orderBy('id', 'DESC')->limit(20)->get();
//$postsupervisor = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();

        $postsupervisor = Post::whereHas('users', function($q) {
        $q->where('users.id', Auth::id());
    })
->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->limit(20)
    ->get();


@endphp

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    @role('user')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">Your account must be validated by an administrator</p>
                </div>
            </div>
        </div>
    </div>
    @endrole

    @role('supervisor')
    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$postsupervisor}}" role="{{json_encode($roles)}}"></card-table>
        </div>
    </div>
    @endrole

    @role('researcher')
    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$postresearcher}}" user="{{$user}}" role="{{json_encode($roles)}}"></card-table>
        </div>
    </div>
    @endrole
@stop
