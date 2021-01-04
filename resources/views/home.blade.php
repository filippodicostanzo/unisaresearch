@extends('adminlte::page')

@section('title', 'AdminLTE')

@php
    use App\Models\Post;use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    $items = \App\Models\Post::orderBy('id', 'DESC')->limit(20)->get();

                   foreach ($items as $item) {

                       $postauthor = [];
                       $authors = explode(',', $item['authors']);
                       foreach ($authors as $author) {
                           array_push($postauthor, \App\Models\Author::where('id',$author)->first());
                       }
                       $item['json_authors'] = json_encode($postauthor,true);
                       $item['category'] = $item->category_fk->name;
                       $item['template'] = $item->template_fk->name;
                       $item['state'] = $item->state_fk->name;

               }

$postresearcher =  \App\Models\Post::where('created', \Illuminate\Support\Facades\Auth::id())->orderBy('id', 'DESC')->limit(20)->get();
//$postsupervisor = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')->get();


        $postsupervisor = Post::whereHas('users', function($q) {
        $q->where('users.id', Auth::id());
    })
->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')
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
            <card-table data="{{$postsupervisor}}" ></card-table>
        </div>
    </div>
    @endrole

    @role('researcher')
    <div class="row mt-5">
        <div class="col-12">
            <card-table data="{{$postresearcher}}" ></card-table>
        </div>
    </div>
    @endrole
@stop
