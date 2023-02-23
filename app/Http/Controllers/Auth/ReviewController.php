<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Edition;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReviewController extends Controller
{

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

        $this->title = __('titles.reviews');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $posts = Post::whereHas('users', function ($q) {
            $q->where('users.id', Auth::id());
        })
            ->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users')
            ->get();


        return view('reviews.index', ['items' => $posts, 'title' => $this->title]);

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)

    {
        if ($request['id'] != null) {


            $item = Post::where('id', $request['id'])->with('state_fk', 'category_fk', 'template_fk', 'authors')->first();

            if ($item->state != '3') {
                abort(403);
            }


            $authors = $item->authors;
            $array_authors = [];
            $authors = explode(',', $authors);


            foreach ($authors as $author) {
                $aut = Author::where('id', '=', $author)->first();
                array_push($array_authors, $aut);
            }


            //dd($item->state_fk->name);

            /*

            $item = DB::table('posts')
                ->leftJoin('templates', 'posts.template', '=', 'templates.id')
                ->leftJoin('categories', 'posts.category', '=', 'categories.id')
                ->select('posts.id', 'posts.title', 'posts.supervisors', 'templates.name as template', 'categories.name as category')
                ->where('posts.id', '=', $request['id'])
                ->first();

            */

            $supervisors = explode(',', $item->supervisors);


            if (in_array($this->user->id, $supervisors)) {


                $review = Review::where('post', $request['id'])->where('supervisor', Auth::id())->first();


                return view('reviews.create', ['item' => $item, 'oldreview' => $review, 'title' => $this->title]);

            } else {
                abort(403);
            }

        }


        abort(403);
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

        $review = new Review($request->all());

        $review['supervisor'] = Auth::id();

        $check = Review::where('post', $review['post'])->where('supervisor', $review['supervisor'])->first();

        if ($check) {
            $data = $request->all();
            $data['supervisor'] = Auth::id();
            $res = Review::find($check->id)->update($data);
        } else {
            $res = $review->save();
        }

        if ($review->result !== 'review') {
            foreach ($administrators as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\SupervisorReview($review->user_fk, $review->post_fk));
            }
        }

        $message = $res ? 'The Review has been saved' : 'The Review was not saved';
        session()->flash('message', $message);

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Review $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Review $review
     * @return \Illuminate\Http\Response
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Review $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Review $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        //
    }


    public function count()
    {

        $supervisors = User::whereRoleIs(['supervisor', 'superadministrator'])->orderBy('id', 'ASC')->get();
        $edition = Edition::where('active', 1)->first();


        foreach ($supervisors as $supervisor) {

            $count = DB::table("posts")
                ->select("posts.*")->where('edition', $edition->id)
                ->whereRaw("find_in_set('" . $supervisor->id . "',posts.supervisors)")
                ->count();

            $supervisor->count = $count;
        }

        $items = $supervisors;

        return view('admin.rewiewers.count', ['items' => $items, 'title' => 'Assigned Reviews']);
    }
}
