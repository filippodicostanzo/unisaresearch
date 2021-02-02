<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
            $items= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get();

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
            'email' => 'required'
        ]);


        /**
         * CHECK IS AUTHOR IS PRESENT
         *
         */

        $email = $request['email'];
        $author_find = Author::where('email', '=', $email)->first();

        if ($author_find) {
            $author_find->users()->sync(Auth::id(), false);
            $message = 'The author is already present. You can enter him as the author of your papers but you cannot change his name and surname';
            session()->flash('message', $message);
        } else {


            $author = new Author($request->all());
            $author['user_id'] = Auth::id();

            $res = $author->save();

            $author->users()->sync(Auth::id());

            if ($res) {
                Mail::to($author->email)->send(new \App\Mail\AddAuthorEmail($author));
            }

            $message = $res ? 'The Author ' . $author->name . ' has been saved' : 'The Author ' . $author->name . ' was not saved';
            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-success');
        }
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

        if ($this->user->hasRole('superadministrator|administrator')) {
            $item = $author;
            return view('authors.show', ['item' => $item]);
        }

        else {
            $items= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get()->toArray();



            if (in_array($author->toArray(), $items)) {
                $item = $author;
                return view('authors.show', ['item' => $item]);
            }
            else {
                abort( 403) ;
            }
        }


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
            'email' => 'required'
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
        if ($this->user->hasRole('superadministrator|administrator')) {

            try {
                $res = $author->delete();
                $message = $res ? 'The Author ' . $author->firstname . ' ' .$author->lastname. ' has been deleted' : 'The Author ' . $author->firstname . ' ' .$author->lastname. ' was not deleted';
                session()->flash('message', $message);
                session()->flash('alert-class', 'alert-success');
            } catch (\Exception $e) {
                if ($e->getCode() =='23000') {
                    $message = 'The author cannot be deleted because he is present in the list of authors inserted by the researchers';
                    session()->flash('message', $message);
                    session()->flash('alert-class', 'alert-danger');
                }
            }
        }
        else {
            $items= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get()->toArray();



            if (in_array($author->toArray(), $items)) {
                $author->users()->detach(Auth::id());
                $message = 'The Author ' . $author->firstname . ' ' .$author->lastname. ' has been deleted';
                session()->flash('message', $message);
            }
            else {
                abort( 403) ;
            }
        }

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

            if (count($authors) > 0) {
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
