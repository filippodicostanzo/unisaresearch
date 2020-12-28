<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    private $title;
    private $user;


    /**
     * Create a constrcut of class
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user(); // returns user
            return $next($request);
        });

        $this->title = 'posts';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ($this->user->hasRole('superadministrator|administrator')) {
            $items = Post::orderBy('id', 'ASC')->get();

            foreach ($items as $item) {

                $postauthor = [];
                $authors = explode(',', $item['authors']);
                foreach ($authors as $author) {
                    array_push($postauthor, Author::where('id', $author)->first());
                }
                $item['json_authors'] = json_encode($postauthor, true);
                $item['category'] = $item->category_fk->name;
                $item['template'] = $item->template_fk->name;

            }

            return view('posts.index', ['items' => $items, 'title' => $this->title]);
        } else if ($this->user->hasRole('supervisor')) {
            $items = Post::where('supervisor', $this->user->id)->orderBy('id', 'ASC')->get();

            foreach ($items as $item) {

                $postauthor = [];
                $authors = explode(',', $item['authors']);
                foreach ($authors as $author) {
                    array_push($postauthor, Author::where('id', $author)->first());
                }
                $item['json_authors'] = json_encode($postauthor, true);
                $item['category'] = $item->category_fk->name;
                $item['template'] = $item->template_fk->name;

            }

            return view('posts.index', ['items' => $items, 'title' => $this->title]);
        } else {
            $items = Post::where('created', $this->user->id)->orderBy('id', 'ASC')->get();

            foreach ($items as $item) {

                $postauthor = [];
                $authors = explode(',', $item['authors']);
                foreach ($authors as $author) {
                    array_push($postauthor, Author::where('id', $author)->first());
                }
                $item['json_authors'] = json_encode($postauthor, true);
                $item['category'] = $item->category_fk->name;
                $item['template'] = $item->template_fk->name;

            }

            return view('posts.index', ['items' => $items, 'title' => $this->title]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create', ['title' => $this->title]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $post = new Post($request->all());

        $authors = $request->input('authors');

        if ($authors != null) {
            $authors = implode(',', $authors);
        }


        $post['authors'] = $authors;
        $post['state'] = 1;
        $post['latest_modify'] = Carbon::now();


        $res = $post->save();
        $message = $res ? 'The Post ' . $post->title . ' has been saved' : 'The Post ' . $post->title . ' was not saved';
        session()->flash('message', $message);
        return redirect()->route('posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {

        $item = $post;

        $postauthor = [];
        $authors = explode(',', $item['authors']);
        foreach ($authors as $author) {
            array_push($postauthor, Author::where('id', $author)->first());
        }
        $item['json_authors'] = json_encode($postauthor, true);
        $item['category'] = $post->category_fk->name;
        $item['template'] = $post->template_fk->name;
        $item['template_fields'] = $post->template_fk->fields;

        return view('posts.show', ['item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $item = $post;

        if ($this->user->hasRole('superadministrator|administrator')) {
            return view('posts.edit', ['title' => $this->title, 'item' => $item]);
        } else {
            if ($item->created === Auth::id()) {

                return view('posts.edit', ['title' => $this->title, 'item' => $item]);
            }

            if ($item->supervisor === Auth::id()) {
                return view('posts.edit', ['title' => $this->title, 'item' => $item]);
            } else {

                abort(403);
            }
        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $authors = $request->input('authors');

        if ($authors != null) {
            $authors = implode(',', $authors);
        }

        $data = $request->all();
        $data['authors'] = $authors;
        $data['latest_modify'] = Carbon::now();

        $res = Post::find($post->id)->update($data);
        $message = $res ? 'The Post ' . $data['title'] . ' has been saved' : 'The Post ' . $data['title'] . ' was not saved';
        session()->flash('message', $message);
        return redirect()->route('posts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //
    }


    public function single($request)
    {
        $item = Post::where('id', $request)->first();

        $postauthor = [];
        $authors = explode(',', $item['authors']);
        foreach ($authors as $author) {
            array_push($postauthor, Author::where('id', $author)->first());
        }
        $item['json_authors'] = json_encode($postauthor, true);
        $item['category'] = $item->category_fk->name;
        $item['template'] = $item->template_fk->name;
        $item['template_fields'] = $item->template_fk->fields;

        return view('posts.show', ['item' => $item]);
    }
}
