<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SingleEmail;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Edition;
use App\Models\Post;
use App\Models\Review;
use App\Models\Status;
use App\Models\User;
use App\Models\PaperExport;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        //$authors_array = $request->input('authors');

        $authors = $request->input('coauthors');


        /*if ($authors != null) {
            $authors = implode(',', $authors);
        }*/

        $post['authors'] = $authors;
        $post['latest_modify'] = Carbon::now();
        $post['created'] = Auth::id();
        $post['edit'] = Auth::id();
        $post['edition'] = $edition->id;


        $res = $post->save();


        if ($res && $authors != null) {
            $authors_array = explode(',', $request->input('coauthors'));
            $post->authors()->sync($authors_array);
        }


        // SEND MAIL FOR PAPER SUBMITTED
        // Nel tuo PostController
        $authors_post = Post::where('id', $post->id)
            ->with(['authors', 'template_fk'])  // Includiamo entrambe le relazioni
            ->first();
        $auts = $authors_post->authors()->get();
        $coauthors = [];

        if ($request->state === '2') {
            // Raccogliamo gli indirizzi email per il CC
            foreach ($auts as $aut) {
                $coauthors[] = $aut->email;
            }

            // Prima inviamo agli amministratori
            foreach ($administrators as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\NewPaperEmail($post));
            }

            // Ora inviamo la notifica di submission
            Mail::to($post->user_fk->email)
                ->cc($coauthors)
                ->send(new \App\Mail\PaperSubmission(
                    $post->user_fk,     // autore principale
                    $post,              // il post
                    $auts              // collection completa dei coautori
                ));
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

        $comment = Comment::where('post_id', $item->id)->first();

        $user = Auth::user();

        $roles = $user->roles()->first();

        if ($roles->name === 'researcher') {
            if ($item->created === $user->id) {
                return view('posts.show', ['item' => $item, 'role' => $roles, 'title' => $this->title, 'source' => $source, 'comment' => $comment]);
            } else {
                return abort(403);
            }
        } else {
            return view('posts.show', ['item' => $item, 'role' => $roles, 'title' => $this->title, 'source' => $source, 'comment' => $comment]);
        }
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
        } else if ($item->created === Auth::id() && $item->state === '4') {

            return view('posts.edit', ['title' => $this->title, 'item' => $item]);
        } else if ($item->supervisor === Auth::id()) {
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


        $authors = $request->input('coauthors');
        $authors_array = [];

        if ($request->input('coauthors') !== null) {
            $authors_array = explode(',', $request->input('coauthors'));
        }

        /*  if ($authors != null) {
              $authors = implode(',', $authors);
          }*/


        $data = $request->all();


        //Check consistency of input hidden STATE
        if ($data['state'] != '1' && $data['state'] != '2' && $data['state'] != '6') {
            abort(403);
        }

        $data['created'] = Auth::id();
        $data['edit'] = Auth::id();


        $data['authors'] = $authors;
        $data['latest_modify'] = Carbon::now();

        if ($data['state'] == 4 && $data['definitve_pdf'] != null) {
            $data['state'] = 6;
        }

        $res = Post::find($post->id)->update($data);


        if (empty($authors_array)) {
            $post->authors()->detach();
        } else {
            if ($res) {
                $post->authors()->sync($authors_array);
            }
        }


        // SEND MAIL FOR PAPER SUBMITTED
        // Nel tuo PostController
        $authors_post = Post::where('id', $post->id)
            ->with(['authors', 'template_fk'])  // Includiamo entrambe le relazioni
            ->first();
        $auts = $authors_post->authors()->get();
        $coauthors = [];

        if ($request->state === '2') {
            // Raccogliamo gli indirizzi email per il CC
            foreach ($auts as $aut) {
                $coauthors[] = $aut->email;
            }

            // Prima inviamo agli amministratori
            foreach ($administrators as $admin) {
                Mail::to($admin->email)->send(new \App\Mail\NewPaperEmail($post));
            }

            // Ora inviamo la notifica di submission
            Mail::to($post->user_fk->email)
                ->cc($coauthors)
                ->send(new \App\Mail\PaperSubmission(
                    $post->user_fk,     // autore principale
                    $post,              // il post
                    $auts              // collection completa dei coautori
                ));
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
            foreach ($supervisors_post->users as $supervisor) {
                Mail::to($supervisor->email)->send(new \App\Mail\ReviewerAssignment($supervisor, $supervisors_post));
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

        $postfind = Post::find($post->id)->with('authors')->first();

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
            $authors_post = Post::where('id', $post->id)
                ->with(['authors', 'template_fk'])
                ->first();
            $auts = $authors_post->authors()->get();
            $coauthors = [];

            // Collect co-authors' email addresses for CC
            foreach ($auts as $aut) {
                $coauthors[] = $aut->email;
            }

            Mail::to($post->user_fk->email)
                ->cc($coauthors)
                ->send(new \App\Mail\AcceptedPaper($post));
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

        $presents = [];

        foreach ($items as $item) {
            $presents[] = $item->category_fk->name;
        }

        $presents = array_unique($presents);
        $presents = array_values($presents);

        return view('posts.vuetable', ['items' => $items, 'title' => $this->title, 'reviews' => $reviews, 'statuses' => $statuses, 'source' => $source, 'categories' => $presents]);
        // return view('posts.index', ['items' => $items, 'title' => $this->title, 'reviews' => $reviews, 'statuses' => $statuses, 'source' => $source]);

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

    function definitivepost(Request $request)
    {

    }

    function singleemail($post, Request $request)
    {
        $item = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'user_fk')
            ->where('id', $post)
            ->first();



        if ($request->has('user')) {
            // Se c'è un user_id nella query, questo è un reviewer
            $reviewer = User::find($request->user);
            if ($reviewer) {
                $item->is_reviewer = true;
                $item->reviewer_data = $reviewer;
                unset($item->users);
            }
        } else {
            $item->load('users');
        }

        return view('posts.email', [
            'item' => $item,
            'title' => $this->title
        ]);
    }

    public function sendsingleemail(Request $request, Post $post)
    {
        $subject = $request->input('subject');
        $body = $request->input('body');
        $isReviewer = $request->input('is_reviewer');

        try {
            if ($isReviewer) {
                // Gestione email per reviewer
                $reviewerData = $request->input('reviewer');

                // Invia l'email al reviewer
                \Mail::to($reviewerData['email'])->send(new SingleEmail(
                    $reviewerData,
                    [
                        'id' => $post->id,
                        'title' => $post->title
                    ],
                    [], // no coauthors per reviewer
                    $subject,
                    $body,
                    'reviewer' // nuovo tipo per reviewer
                ));
            } else {
                // Gestione email per autori (codice esistente)
                $recipientType = $request->input('recipient');
                $authorData = $request->input('author');
                $coauthorsData = $request->input('coauthors');

                $postData = [
                    'id' => $post->id,
                    'title' => $post->title
                ];

                // Determina i destinatari in base al tipo selezionato
                $recipients = [];
                switch ($recipientType) {
                    case 'author':
                        $recipients[] = $authorData['email'];
                        break;
                    case 'author-coauthors':
                        $recipients[] = $authorData['email'];
                        $recipients = array_merge($recipients,
                            collect($coauthorsData)->pluck('email')->toArray());
                        break;
                    case 'coauthors':
                        $recipients = collect($coauthorsData)->pluck('email')->toArray();
                        break;
                }

                // Rimuovi duplicati e valori vuoti
                $recipients = array_unique(array_filter($recipients));

                // Invia l'email a tutti i destinatari
                \Mail::to($recipients)->send(new SingleEmail(
                    $authorData,
                    $postData,
                    $coauthorsData,
                    $subject,
                    $body,
                    $recipientType
                ));
            }

            session()->flash('message', 'Email for paper "' . $post->title . '" sent successfully');
            session()->flash('alert-class', 'alert-success');

        } catch (\Exception $e) {
            session()->flash('message', 'An error occurred while sending the email');
            session()->flash('alert-class', 'alert-danger');
            \Log::error('Email sending error: ' . $e->getMessage());
        }
    }

    function generate(Request $request)
    {
        $papers = $request->get('papers');
        $file = new Spreadsheet;
        $active_sheet = $file->getActiveSheet();

        /*$array = (array)$papers[0];
        $keys = array_keys($array);*/


        $allpapers = [];


        foreach ($papers as $p) {


            $paper = new PaperExport();
            $paper->id = $p['id'];
            $paper->title = $p['title'];
            $paper->authors = $p['authors_export'];
            $paper->template = $p['template'];
            $paper->topic = $p['topic'];
            $paper->state = $p['state_export'];
            $paper->tags = $p['tags'];
            $paper->pdf = $p['pdf'];
            $paper->definitve_pdf = $p['definitive_pdf'];
            $paper->date = $p['date'];


            $ar = get_object_vars($paper);
            array_push($allpapers, $ar);
        }

        $array = (array)$allpapers[0];
        $keys = array_keys($array);

        $col = 1;
        foreach ($keys as $key) {
            $active_sheet->setCellValueByColumnAndRow($col, 1, $key);
            $col++;
        }

        $col = 1;
        $row = 2;


        foreach ($allpapers as $papers) {

            foreach ($papers as $p) {
                $active_sheet->setCellValueExplicitByColumnAndRow($col, $row, $p, DataType::TYPE_STRING);
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



// Add this method to your PostController class
    public function generatePDF(Request $request)
    {
        $papers = $request->get('papers');

        // Get full post data for all selected posts
        $postIds = collect($papers)->pluck('id');
        $posts = Post::whereIn('id', $postIds)
            ->with(['state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk'])
            ->get();

        // Create PDF file
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // To load images from remote URLs if needed

        $dompdf = new Dompdf($options);

        // Create HTML content
        $html = view('pdf.posts', ['posts' => $posts])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'papers-' . date('YmdHis') . '.pdf';

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

}
