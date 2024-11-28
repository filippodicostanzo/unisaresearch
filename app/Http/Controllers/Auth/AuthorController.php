<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\AuthorExport;
use App\Models\PaperExport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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

        $this->title = __('titles.co-authors');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $source='author';

            $items= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get();

            return view('authors.index', ['items' => $items, 'title' => $this->title, 'source'=>$source]);


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
        try {
            // Validazione
            $this->validate($request, [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email'
            ]);

            // Verifica se l'autore esiste già
            $author_find = Author::where('email', $request->email)->first();

            if ($author_find) {
                // L'autore esiste già, aggiorna la relazione
                $author_find->users()->syncWithoutDetaching([Auth::id()]);

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'author' => [
                        'id' => $author_find->id,
                        'firstname' => $author_find->firstname,
                        'lastname' => $author_find->lastname,
                        'email' => $author_find->email
                    ],
                    'message' => 'Author already exists and has been added to your co-authors list'
                ]);
            } else {
                // Crea nuovo autore
                $author = new Author($request->all());
                $author->user_id = Auth::id();
                $saved = $author->save();

                if (!$saved) {
                    throw new \Exception('Failed to save author');
                }

                // Crea la relazione con l'utente corrente
                $author->users()->attach(Auth::id());

                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'author' => [
                        'id' => $author->id,
                        'firstname' => $author->firstname,
                        'lastname' => $author->lastname,
                        'email' => $author->email
                    ],
                    'message' => 'New author has been created and added to your co-authors list'
                ]);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating/updating author',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author, Request $request)
    {

        $source= $request['source'];

        if ($this->user->hasRole('superadministrator|administrator')) {
            $item = $author;
            return view('authors.show', ['item' => $item,  'title' => $this->title, 'source'=>$source]);
        }

        else {
            $items= Author::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get()->toArray();



            if (in_array($author->toArray(), $items)) {
                $item = $author;
                return view('authors.show', ['item' => $item,  'title' => $this->title, 'source'=>$source]);
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
     * @throws \Exception
     */
    public function destroy(Author $author)
    {


        $find = DB::table('author_post')->where('author_id','=', $author->id)->count();




        if ($this->user->hasRole('superadministrator|administrator')) {


            try {

                if (!$find) {
                    $author->users()->detach($author->user_id);
                    $res = $author->delete();
                    $message = $res ? 'The Author ' . $author->firstname . ' ' . $author->lastname . ' has been deleted' : 'The Author ' . $author->firstname . ' ' . $author->lastname . ' was not deleted';
                    session()->flash('message', $message);
                    session()->flash('alert-class', 'alert-success');
                }

                else {
                    $message = 'The Author ' . $author->firstname . ' ' . $author->lastname . ' cannot be deleted because he belongs to the list of co-authors of someone.';
                    session()->flash('message', $message);
                    session()->flash('alert-class', 'alert-danger');
                }


            } catch (\Exception $e) {
                if ($e->getCode() == '23000') {
                    $message = 'The author cannot be deleted because he is present in the list of authors inserted by the researchers';
                    session()->flash('message', $message);
                    session()->flash('alert-class', 'alert-danger');
                }
            }
        } else {
            $items = Author::whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            })->orderBy('id', 'ASC')->get()->toArray();




            if (in_array($author->toArray(), $items)) {


                $posts =  DB::table('author_post')->where('author_id','=', $author->id)->get();


                $flag = false;

                foreach ($posts as $post) {

                    $alba = Post::where('id','=',$post->post_id)->get();



                        foreach($alba as $alb) {
                                $array = explode(',', $alb->authors);
                                if (in_array($author->id, $array)) {
                                    $flag=true;
                                }
                        }


                }

                if ($flag) {
                    $message = 'ERROR! You cannot delete a co-author ' . $author->firstname . ' ' . $author->lastname . '  that you have added to your manuscript(s). If you still want to delete a co-author, please check if you can delete the related manuscript first. ';
                    session()->flash('message', $message);
                    session()->flash('alert-class', 'alert-danger');
                }
                else {
                    $author->users()->detach(Auth::id());
                    $message = 'The Author ' . $author->firstname . ' ' . $author->lastname . ' has been deleted';
                    session()->flash('message', $message);
                }
            } else {
                abort(403);
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


    public function authorsadmin() {
        $source = 'admin';
        $items = Author::orderBy('id', 'ASC')->get();
        return view('authors.index', ['items' => $items, 'title' => $this->title, 'source' => $source]);
    }

    public function generate(Request $request)
    {
        $authors = $request->get('authors');
        $file = new Spreadsheet;
        $active_sheet = $file->getActiveSheet();

        $array = (array)$authors[0];
        $keys = array_keys($array);
        $allauthors = [];

        $col = 1;
        foreach ($keys as $key) {
            $active_sheet->setCellValueByColumnAndRow($col, 1, $key);
            $col++;
        }

        foreach ($authors as $a) {

            $author = new AuthorExport();
            $author->id = $a['id'];
            $author->firstname = $a['firstname'];
            $author->lastname = $a['lastname'];
            $author->email = $a['email'];
            $ar = get_object_vars($author);
            array_push($allauthors, $ar);
        }


        $col = 1;
        $row = 2;


        foreach ($allauthors as $authors) {

            foreach ($authors as $a) {
                $active_sheet->setCellValueExplicitByColumnAndRow($col, $row, $a, DataType::TYPE_STRING);
                $col++;
            }
            $row++;
            $col = 1;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($file);

        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);
        $writer->setUseBOM(true);

        $file_name = 'papers.csv';


        $writer->save($file_name);


        header('Content-Type: application/x-www-form-urlencoded');

        header('Content-Transfer-Encoding: Binary');

        header("Content-disposition: attachment; filename=\"" . $file_name . "\"");

        readfile($file_name);

        unlink($file_name);

    }

    public function checkExists(Request $request)
    {
        // Trova l'autore per email
        $author = Author::where('email', $request->email)->first();

        if (!$author) {
            return response()->json([
                'exists' => false,
                'message' => 'Author not found'
            ]);
        }

        // Ottieni l'utente autenticato
        $user = auth()->user();

        // Verifica se esiste già la relazione nella tabella pivot
        $relationExists = $author->users()->where('user_id', $user->id)->exists();

        if ($relationExists) {
            return response()->json([
                'exists' => true,
                'relationExists' => true,
                'author' => $author,
                'message' => 'This author is already in your co-authors list'
            ]);
        }

        // Se l'autore esiste ma non c'è la relazione, la creiamo
        try {
            $author->users()->attach($user->id);

            return response()->json([
                'exists' => true,
                'relationExists' => false,
                'author' => $author,
                'message' => 'Author found and added to your co-authors list'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exists' => true,
                'relationExists' => false,
                'error' => true,
                'message' => 'Error adding author to your co-authors list'
            ], 500);
        }
    }

}
