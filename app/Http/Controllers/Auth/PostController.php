<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Edition;
use App\Models\Post;
use App\Models\Review;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

        $this->title = __('titles.posts');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::all();
        $statuses = Status::all();
        $edition = Edition::where('active', 1)->first();
        $source = 'author';

        $items = Post::where('edition', $edition->id)->where('created', Auth::id())->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk')->get();
        return view('posts.index', ['items' => $items, 'title' => $this->title, 'reviews' => $reviews, 'statuses' => $statuses, 'source' => $source]);

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



        $administrators = User::whereRoleIs('superadministrator')->get();

        $post = new Post($request->all());

        //Check consistency of input hidden STATE
        if ($post['state'] != '1' && $post['state'] != '2') {
            abort(403);
        }

        $edition = Edition::where('active', 1)->first();

        $authors_array = $request->input('authors');
        $authors = $request->input('authors');

        if ($authors != null) {
            $authors = implode(',', $authors);
        }

        $post['authors'] = $authors;
        $post['latest_modify'] = Carbon::now();
        $post['created'] = Auth::id();
        $post['edit'] = Auth::id();
        $post['edition'] = $edition->id;


        $res = $post->save();

        if ($res) {
            $post->authors()->sync($authors_array);
        }


        // SEND MAIL FOR PAPER SUBMITTED
        $authors_post = Post::where('id', $post->id)->with('authors')->first();
        $auts = $authors_post->authors()->get();
        $authors_post->authors_fk = $auts;

        // SEND MAIL FOR PAPER SUBMITTED
        if ($request->state === '2') {
            foreach ($administrators as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\NewPaperEmail($post));
            }

            foreach ($auts as $aut) {
                Mail::to($aut->email)->send(new \App\Mail\AddAuthorEmail($aut, $authors_post));
            }
        }

        $message = $res ? 'The Paper ' . $post->title . ' has been saved' : 'The Paper ' . $post->title . ' was not saved';
        session()->flash('message', $message);
        return redirect()->route('posts.author');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post, Request $request)
    {

        $source = $request['source'];
        $item = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'user_fk', 'users')->where('id', $post->id)->first();
        $user = Auth::user();
        $roles = $user->roles()->first();

        return view('posts.show', ['item' => $item, 'role' => $roles, 'title' => $this->title, 'source' => $source]);
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


        if ($item->created === Auth::id() && $item->state === '1') {


            return view('posts.edit', ['title' => $this->title, 'item' => $item]);
        }

        if ($item->supervisor === Auth::id()) {
            return view('posts.edit', ['title' => $this->title, 'item' => $item]);
        } else {

            abort(403);
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
        $administrators = User::whereRoleIs('superadministrator')->get();



        $authors = $request->input('authors');
        $authors_array = $request->input('authors');

        if ($authors != null) {
            $authors = implode(',', $authors);
        }


        $data = $request->all();

        //Check consistency of input hidden STATE
        if ($data['state'] != '1' && $data['state'] != '2') {
            abort(403);
        }
        $data['created'] = Auth::id();
        $data['edit'] = Auth::id();


        $data['authors'] = $authors;
        $data['latest_modify'] = Carbon::now();

        $res = Post::find($post->id)->update($data);

        if ($res) {
            $post->authors()->sync($authors_array);
        }


        $authors_post = Post::where('id', $post->id)->with('authors')->first();
        $auts = $authors_post->authors()->get();
        $authors_post->authors_fk = $auts;

        // SEND MAIL FOR PAPER SUBMITTED
        if ($data['state'] === '2') {
            foreach ($administrators as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\NewPaperEmail($post));
            }
            foreach ($auts as $aut) {
                Mail::to($aut->email)->send(new \App\Mail\AddAuthorEmail($aut, $authors_post));
            }
        }


        $message = $res ? 'The Paper ' . $data['title'] . ' has been saved' : 'The Paper ' . $data['title'] . ' was not saved';
        session()->flash('message', $message);
        return redirect()->route('posts.author');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {

        if (($this->user->hasRole('superadministrator|administrator')) || ($post['created'] === Auth::id())) {
            $res = $post->delete();
            $message = $res ? 'The Post ' . $post->title . ' has been deleted' : 'The Post ' . $post->title . ' was not deleted';
            session()->flash('message', $message);
        } else {
            return abort(403);
        }
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

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function link(Post $post)
    {

        $item = $post;

        $itm = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'user_fk')->where('id', $post->id)->first();

        if ($this->user->hasRole('superadministrator|administrator')) {
            return view('posts.link', ['title' => $this->title, 'item' => $itm]);
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function updatelink(Request $request, Post $post)
    {

        $supervisors = $request->input('supervisors');
        $supervisors_array = $request->input('supervisors');


        if ($supervisors != null) {
            $supervisors = implode(',', $supervisors);
        }


        $data = $request->all();
        $data['supervisors'] = $supervisors;
        $data['latest_modify'] = Carbon::now();


        $res = Post::find($post->id)->update($data);
        $post->users()->sync($supervisors_array);


        //SEND EMAIL TO SUPERVISORS & ADMIN


        $supervisors_post = Post::where('id', $post->id)->with('users')->first();
        if ($data['state'] === '3' && $post->state != '3') {
            Mail::to($post->user_fk->email)->send(new \App\Mail\ReviewPaper($post));
            foreach ($supervisors_post->users as $supervisor) {
                Mail::to($supervisor->email)->send(new \App\Mail\SupervisorPaper($supervisor, $supervisors_post));
            }
        }

        $message = $res ? 'The Paper ' . $post->title . ' has been saved' : 'The Paper ' . $post->title . ' was not saved';
        session()->flash('message', $message);
        return redirect()->route('posts.admin');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function valid(Post $post)
    {

        $item = $post;
        $reviews = Review::where('post', $item->id)->with('user_fk')->get();

        $itm = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'user_fk', 'users')->where('id', $post->id)->first();
        $comment = Comment::where('post_id', $post->id)->first();

        $status = Status::all();

        if ($this->user->hasRole('superadministrator|administrator')) {
            return view('posts.valid', ['title' => $this->title, 'item' => $itm, 'comment' => $comment, 'reviews' => $reviews, 'status' => $status]);
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function validupdate(Request $request, Post $post)
    {

        $data = $request['state'];

        $postfind = Post::find($post->id);

        if ($postfind) {
            $post->state = $data;
            $res = $post->save();

            $post_comment = Comment::where('post_id', $post->id)->first();

            if ($post_comment) {
                $cmt = Comment::find($post_comment->id);
                $cmt->comment = $request['comment'];
                $cmt->save();
            } else {
                $comment = new Comment();
                $comment['comment'] = $request['comment'];
                $comment['user_id'] = Auth::id();
                $comment['post_id'] = $post->id;
                $comment->save();
            }
        }


        //ACCEPTED PAPER
        if ($request['state'] == '4') {
            Mail::to($post->user_fk->email)->send(new \App\Mail\AcceptedPaper($post));
        }

        //REJECTED PAPER
        if ($request['state'] == '5') {
            Mail::to($post->user_fk->email)->send(new \App\Mail\RejectedPaper($post));
        }

        $message = $res ? 'The Paper ' . $post->title . ' has been saved' : 'The Paper ' . $post->title . ' was not saved';
        session()->flash('message', $message);

    }

    function postsadmin()
    {
        $reviews = Review::all();
        $statuses = Status::all();
        $edition = Edition::where('active', 1)->first();
        $source = 'admin';

        $items = Post::where('edition', $edition->id)->where('state', '!=', 1)->orderBy('id', 'ASC')->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk')->get();
        return view('posts.index', ['items' => $items, 'title' => $this->title, 'reviews' => $reviews, 'statuses' => $statuses, 'source' => $source]);

    }

    function postsreviewer()
    {

        $reviews = Review::all();
        $statuses = Status::all();
        $edition = Edition::where('active', 1)->first();
        $source = 'reviewer';


        $items = Post::whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
        })
            ->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk')->where('edition', $edition->id)
            ->get();


        return view('posts.index', ['items' => $items, 'title' => $this->title, 'reviews' => $reviews, 'statuses' => $statuses, 'source' => $source]);


    }

}
