<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
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

        $this->title = 'authors';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ($this->user->hasRole('superadministrator|administrator')) {
            $items = Author::orderBy('id', 'ASC')->get();
            return view('authors.index', ['items' => $items, 'title' => $this->title]);
        } else {
            $items = Author::where('user_id', $this->user->id)->orderBy('id', 'ASC')->get();
            return view('authors.index', ['items' => $items, 'title' => $this->title]);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('authors.create', ['title' => $this->title]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /*
                $validator = Validator::make($request->all(), [
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'email' => 'required|unique:authors'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => "Password does't match",
                    ], 422);
        //            return response()->json(['isValid'=>false,'errors'=>$validator->messages()]);
                }
                else {


        */

        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|unique:authors'
        ]);

        $author = new Author($request->all());
        $author['user_id'] = Auth::id();

        $res = $author->save();

        if ($res) {
            Mail::to($author->email)->send(new \App\Mail\AddAuthorEmail($author));
        }

        $message = $res ? 'The Author ' . $author->name . ' has been saved' : 'The Author ' . $author->name . ' was not saved';
        session()->flash('message', $message);
        //}
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author)
    {
        $item = $author;
        return view('authors.show', ['item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function edit(Author $author)
    {
        $item = $author;


        if ($this->user->hasRole('superadministrator|administrator')) {
            return view('authors.edit', ['title' => $this->title, 'item' => $item]);
        } else {
            if ($item->user_id === Auth::id()) {
                return view('authors.edit', ['item' => $item, 'title' => $this->title]);
            } else {
                abort(403);
            }
        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Author $author)
    {
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|unique:authors'
        ]);


        $data = $request->all();
        $res = Author::find($author->id)->update($data);
        $message = $res ? 'The Category ' . $author->firstname . ' è stato modificato' : 'Il Documento ' . $author->firstname . ' non è stata modificato';
        session()->flash('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function destroy(Author $author)
    {
        //
    }

    /**
     * Search Authors
     *
     * @param \App\Models\Author $request
     * @return \Illuminate\Http\Response
     */

    public function search(Request $request)
    {


        if ($request->ajax()) {
            $output = "";
            $authors = DB::table('authors')->where('email', '=', $request->search)->get();

            if (count($authors)>0) {
                foreach ($authors as $key => $author) {

                    $output .= '<div class="item col-md-6 col-xs-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="' . $author->id . '"
                                                   name="authors[]" value="' . $author->id . '">
                                            <label class="form-check-label"
                                                   for="exampleCheck1">' . $author->firstname . ' ' . $author->lastname . '</label>
                                        </div>
                                    </div>';
                }
            } else {
                $output .= '<div class="alert alert-danger" role="alert">The author is not present</div>';
            }

            return Response($output);
        }

    }

}
