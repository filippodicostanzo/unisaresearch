@extends('adminlte::page')

@section('title', 'The Naples Fos')

@php
    use App\Models\Post;
    use App\Models\Edition;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

$items=Post::orderBy('id', 'DESC')->limit(20)->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();

$user=Auth::user();
$roles = $user->roles()->first();
$activeEdition = Edition::where('active', true)->first();



$postresearcher =  Post::where('created','=',Auth::id())->where('edition', $activeEdition->id)
->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')
->orderBy('id', 'DESC')
->limit(20)
->get();
//$postsupervisor = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();

$postsupervisor = Post::whereHas('users', function($q) {
        $q->where('users.id', Auth::id());
    })
    ->where('edition', $activeEdition->id)
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

    @if(count($postresearcher)==0)
        <div class="row mt-5">

            <welcome user="{{$user}}"></welcome>

        </div>
    @else
        <div class="row mt-5">
            <div class="col-12">
                <card-table data="{{$postresearcher}}" user="{{$user}}" role="{{json_encode($roles)}}"></card-table>
            </div>
        </div>
    @endif
    @endrole
@stop
