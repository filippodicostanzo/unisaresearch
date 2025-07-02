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


    public function generate(Request $request)

    {

        $papers = $request->get('papers');


        // Get full post data for all selected posts (come in generateWord)

        $postIds = collect($papers)->pluck('id');


        $posts = Post::whereIn('id', $postIds)
            ->with(['state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk'])
            ->orderBy('title', 'ASC')
            ->get();


        $file = new Spreadsheet;

        $active_sheet = $file->getActiveSheet();


        $allpapers = [];


        foreach ($posts as $post) {

            // Costruisci l'array direttamente nell'ordine corretto

            $paper = [

                'id' => $post->id,

                'title' => $post->title,

                'authors' => $this->getOrderedAuthorsString($post),

                'template' => $post->template_fk ? $post->template_fk->name : '',

                'topic' => $post->category_fk ? $post->category_fk->name : '',

                'related_nfs_pillars' => $this->cleanFieldContent($post->field_7 ?? ''),

                'state' => $post->state_fk ? $post->state_fk->name : '',

                'tags' => $post->tags,

                'pdf' => $post->pdf,

                'definitve_pdf' => $post->definitive_pdf,

                'date' => $post->date

            ];


            array_push($allpapers, $paper);

        }


        // Se non ci sono papers, gestisci il caso

        if (empty($allpapers)) {

            return response()->json(['error' => 'Nessun paper trovato'], 404);

        }


        // Definisci l'ordine esatto delle colonne

        $orderedKeys = [

            'id',

            'title',

            'authors',

            'template',

            'topic',

            'related_nfs_pillars',  // Posizionata dopo topic

            'state',

            'tags',

            'pdf',

            'definitve_pdf',

            'date'

        ];


        // Mappa i nomi delle colonne per l'header

        $columnHeaders = [

            'id' => 'id',

            'title' => 'title',

            'authors' => 'authors',

            'template' => 'template',

            'topic' => 'topic',

            'related_nfs_pillars' => 'related_nfs_pillars',

            'state' => 'state',

            'tags' => 'tags',

            'pdf' => 'pdf',

            'definitve_pdf' => 'definitve_pdf',

            'date' => 'date'

        ];


        // Header del CSV nell'ordine corretto

        $col = 1;

        foreach ($orderedKeys as $key) {

            $headerName = $columnHeaders[$key] ?? $key;

            $active_sheet->setCellValueByColumnAndRow($col, 1, $headerName);

            $col++;

        }


        // Dati del CSV nell'ordine corretto

        $row = 2;


        foreach ($allpapers as $paper) {

            $col = 1;

            foreach ($orderedKeys as $key) {

                $value = $paper[$key] ?? '';

                $active_sheet->setCellValueExplicitByColumnAndRow($col, $row, $value, DataType::TYPE_STRING);

                $col++;

            }

            $row++;

        }


        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($file);

        $writer->setDelimiter(';');

        $writer->setEnclosure('"');

        $writer->setLineEnding("\r\n");

        $writer->setSheetIndex(0);

        $writer->setUseBOM(true);


        $file_name = 'papers-' . date('YmdHis') . '.csv';


        try {

            $writer->save($file_name);


            if (!file_exists($file_name) || filesize($file_name) === 0) {

                throw new \Exception('Errore nella creazione del file CSV');

            }


            header('Content-Type: text/csv; charset=utf-8');

            header('Content-Transfer-Encoding: Binary');

            header("Content-disposition: attachment; filename=\"" . $file_name . "\"");


            readfile($file_name);

            unlink($file_name);


        } catch (\Exception $e) {

            return response()->json(['error' => 'Errore durante la generazione del CSV: ' . $e->getMessage()], 500);

        }

    }


    /**
     * Estrae la logica di ordinamento autori dalla funzione generateWord
     * con formato: Nome Cognome [email];
     */

    private function getOrderedAuthorsString($post)

    {

        $submitterPosition = (int)$post->submitter_position;


        // Ottieni il nome e email del submitter da user_fk

        $submitterName = ($post->user_fk->name ?? 'Sconosciuto') . ' ' . ($post->user_fk->surname ?? '');

        $submitterEmail = $post->user_fk->email ?? '';


        // Usa $post->authors()->get() per accedere alla relazione

        $authors = $post->authors()->get();


        // Inizializza la lista degli autori

        $allAuthors = [];


        // Aggiungi gli autori dalla relazione authors

        if ($authors && $authors->count() > 0) {

            foreach ($authors as $author) {

                $firstname = $author->firstname ?? 'Sconosciuto';

                $lastname = $author->lastname ?? '';

                $authorName = $firstname . ' ' . $lastname;

                $authorEmail = $author->email ?? '';


                $allAuthors[] = [

                    'name' => $authorName,

                    'email' => $authorEmail,

                    'formatted' => $this->formatAuthorWithEmail($authorName, $authorEmail),

                    'is_submitter' => false

                ];

            }

        }


        // Aggiungi il submitter manualmente

        $submitterFormatted = $this->formatAuthorWithEmail($submitterName, $submitterEmail);

        $submitterExistsInAuthors = false;


        foreach ($allAuthors as &$author) {

            if ($author['name'] === $submitterName) {

                $author['is_submitter'] = true;

                // Aggiorna con i dati del submitter se necessario

                $author['email'] = $submitterEmail;

                $author['formatted'] = $submitterFormatted;

                $submitterExistsInAuthors = true;

                break;

            }

        }

        unset($author);


        if (!$submitterExistsInAuthors) {

            $allAuthors[] = [

                'name' => $submitterName,

                'email' => $submitterEmail,

                'formatted' => $submitterFormatted,

                'is_submitter' => true

            ];

        }


        // Costruisci la lista finale in base a submitter_position

        $totalAuthorsCount = count($allAuthors);

        if ($submitterPosition < 1 || $submitterPosition > $totalAuthorsCount) {

            $submitterPosition = 1;

        }


        // Separa il submitter dagli altri autori

        $submitterData = null;

        $otherAuthors = [];

        foreach ($allAuthors as $author) {

            if ($author['is_submitter']) {

                $submitterData = $author;

            } else {

                $otherAuthors[] = $author;

            }

        }


        // Costruisci la lista finale

        $finalAuthors = [];

        $otherAuthorsIndex = 0;

        for ($i = 1; $i <= $totalAuthorsCount; $i++) {

            if ($i == $submitterPosition) {

                $finalAuthors[] = $submitterData['formatted'];

            }

            if ($otherAuthorsIndex < count($otherAuthors)) {

                if ($i != $submitterPosition) {

                    $finalAuthors[] = $otherAuthors[$otherAuthorsIndex]['formatted'];

                    $otherAuthorsIndex++;

                } else {

                    $finalAuthors[] = $otherAuthors[$otherAuthorsIndex]['formatted'];

                    $otherAuthorsIndex++;

                }

            }

        }


        // Unisci tutti gli autori con il formato richiesto

        return implode(' ', $finalAuthors);

    }


    /**
     * Formatta un autore con il formato: Nome Cognome [email];
     */

    private function formatAuthorWithEmail($name, $email)

    {

        $formattedName = trim($name);

        $formattedEmail = trim($email);


        if (empty($formattedEmail)) {

            return $formattedName . ';';

        }


        return $formattedName . ' [' . $formattedEmail . '];';

    }


    /**
     * Pulisce il contenuto dei campi rimuovendo HTML e normalizzando il testo per CSV
     */

    private function cleanFieldContent($content)

    {

        if (empty($content)) {

            return '';

        }


        // Rimuovi tag HTML

        $cleaned = strip_tags($content);


        // Sostituisci entità HTML

        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');


        // Rimuovi spazi multipli e normalizza

        $cleaned = preg_replace('/\s+/', ' ', $cleaned);


        // Rimuovi caratteri speciali che potrebbero dare problemi nel CSV

        $cleaned = str_replace(['"', "\r", "\n"], ['""', ' ', ' '], $cleaned);


        return trim($cleaned);

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







    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Generate a Word document with selected posts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function generateWord(Request $request)

    {

        $papers = $request->get('papers');


        // Get full post data for all selected posts

        $postIds = collect($papers)->pluck('id');


        $posts = Post::whereIn('id', $postIds)
            ->with(['state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk'])
            ->orderBy('title', 'ASC')
            ->get();


        // Create new Word document

        $phpWord = new \PhpOffice\PhpWord\PhpWord();


        // Set document properties

        $properties = $phpWord->getDocumentProperties();

        $properties->setCreator('Naples Forum');

        $properties->setCompany('Naples Forum on Service');

        $properties->setTitle('Selected Papers');


        // Configure styles

        $phpWord->setDefaultFontName('Times New Roman');

        $phpWord->setDefaultFontSize(12);


        // Define heading styles for TOC

        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16], ['alignment' => 'center', 'spaceAfter' => 240]);

        $phpWord->addTitleStyle(2, ['bold' => false, 'size' => 12], ['alignment' => 'center', 'spaceAfter' => 200]);


        // Define other styles

        $titleStyle = ['bold' => true, 'size' => 16, 'spaceAfter' => 0];

        $topicStyle = ['italic' => true, 'size' => 12];

        $headingStyle = ['bold' => true, 'size' => 14, 'spaceAfter' => 120];

        $normalStyle = ['size' => 12];

        $referencesHeadingStyle = ['bold' => true, 'size' => 12, 'spaceBefore' => 240];

        $referencesStyle = ['size' => 11, 'spaceAfter' => 0];


        // Create section style

        $sectionStyle = [

            'orientation' => 'portrait',

            'marginTop' => 1000,

            'marginLeft' => 1000,

            'marginRight' => 1000,

            'marginBottom' => 1000,

        ];


        // Add title page

        $section = $phpWord->addSection($sectionStyle);

        $section->addText('Naples Forum on Service', ['bold' => true, 'size' => 20], ['alignment' => 'center', 'spaceAfter' => 200]);

        $section->addText('Selected Papers', ['bold' => true, 'size' => 18], ['alignment' => 'center', 'spaceAfter' => 200]);

        $section->addText('Generated on: ' . date('d/m/Y'), ['italic' => true], ['alignment' => 'center']);


        // Add page break

        $section->addPageBreak();


        // Add TOC section

        $section->addText('Table of Contents', ['bold' => true, 'size' => 16], ['alignment' => 'center', 'spaceAfter' => 240]);

        $section->addTOC(['bold' => true, 'size' => 12], ['tabLeader' => 'dot', 'minDepth' => 1, 'maxDepth' => 2]);

        $section->addPageBreak();


        // Add each paper

        foreach ($posts as $post) {

            try {

                // Start new page for each paper (except the first one)

                if ($post->id != $posts->first()->id) {

                    $section->addPageBreak();

                }


                // Create a title that will be picked up by TOC

                $title = $this->cleanTextForWord(strip_tags($post->title));

                $section->addTitle($title, 1);


                // Add author information with submitter position as Heading 2

                $submitterPosition = (int)$post->submitter_position;


                // Ottieni il nome del submitter da user_fk

                $submitterName = ($post->user_fk->name ?? 'Sconosciuto') . ' ' . ($post->user_fk->surname ?? '');


                // Usa $post->authors()->get() per accedere alla relazione

                $authors = $post->authors()->get();


                // Inizializza la lista degli autori

                $allAuthors = [];


                // Aggiungi gli autori dalla relazione authors

                if ($authors && $authors->count() > 0) {

                    foreach ($authors as $author) {

                        // Verifica che firstname e lastname non siano nulli

                        $firstname = $author->firstname ?? 'Sconosciuto';

                        $lastname = $author->lastname ?? '';

                        $authorName = $firstname . ' ' . $lastname;


                        $allAuthors[] = [

                            'name' => $authorName,

                            'is_submitter' => false

                        ];

                    }

                }


                // Aggiungi il submitter manualmente

                $submitterExistsInAuthors = false;

                foreach ($allAuthors as &$author) {

                    if ($author['name'] === $submitterName) {

                        $author['is_submitter'] = true;

                        $submitterExistsInAuthors = true;

                        break;

                    }

                }

                unset($author);


                if (!$submitterExistsInAuthors) {

                    $allAuthors[] = [

                        'name' => $submitterName,

                        'is_submitter' => true

                    ];

                }


                // Costruisci la lista finale in base a submitter_position

                $totalAuthorsCount = count($allAuthors);

                if ($submitterPosition < 1 || $submitterPosition > $totalAuthorsCount) {

                    $submitterPosition = 1;

                }


                // Separa il submitter dagli altri autori

                $submitterData = null;

                $otherAuthors = [];

                foreach ($allAuthors as $author) {

                    if ($author['is_submitter']) {

                        $submitterData = $author;

                    } else {

                        $otherAuthors[] = $author;

                    }

                }


                // Costruisci la lista finale

                $finalAuthors = [];

                $otherAuthorsIndex = 0;

                for ($i = 1; $i <= $totalAuthorsCount; $i++) {

                    if ($i == $submitterPosition) {

                        $finalAuthors[] = $submitterData['name'];

                    }

                    if ($otherAuthorsIndex < count($otherAuthors)) {

                        if ($i != $submitterPosition) {

                            $finalAuthors[] = $otherAuthors[$otherAuthorsIndex]['name'];

                            $otherAuthorsIndex++;

                        } else {

                            $finalAuthors[] = $otherAuthors[$otherAuthorsIndex]['name'];

                            $otherAuthorsIndex++;

                        }

                    }

                }


                // Unisci tutti gli autori in una stringa con separatore " - "

                $authorText = implode(' - ', $finalAuthors);

                $authorText = $this->cleanTextForWord($authorText);

                $section->addTitle($authorText, 2);


                // Add topic (centered)

                if ($post->category_fk) {

                    $topicText = $this->cleanTextForWord('Topic: ' . $post->category_fk->name);

                    $section->addText($topicText, $topicStyle, ['alignment' => 'center']);

                }


                $section->addTextBreak(1);


                // Otteniamo i campi del template

                $templateFields = [];

                if ($post->template_fk && !empty($post->template_fk->fields)) {

                    try {

                        $templateFields = json_decode($post->template_fk->fields, true);

                        if (!is_array($templateFields)) {

                            $templateFields = [];

                        }

                    } catch (\Exception $e) {

                        $templateFields = [];

                    }

                }


                // Ottieni gli attributi del post

                $attributes = $post->getAttributes();


                // Processa i campi

                if (!empty($templateFields) && !empty($attributes)) {

                    foreach ($templateFields as $index => $fieldData) {

                        if (!isset($fieldData['name']) || empty($fieldData['name'])) {

                            continue;

                        }


                        $fieldName = $fieldData['name'];

                        $fieldKey = 'field_' . ($index + 1);


                        if (!isset($attributes[$fieldKey]) || empty($attributes[$fieldKey])) {

                            continue;

                        }


                        $fieldContent = $attributes[$fieldKey];


                        try {

                            $isReferencesField = (strtolower($fieldName) === 'references');


                            if ($isReferencesField) {

                                // Formattazione per i riferimenti

                                $section->addText($this->cleanTextForWord($fieldName . ':'), $referencesHeadingStyle);

                                $references = $this->splitHtmlIntoParagraphs($fieldContent);


                                foreach ($references as $reference) {

                                    if (!empty($reference)) {

                                        $section->addText($reference, $referencesStyle, ['alignment' => 'both']);

                                    }

                                }

                            } else {

                                // Formattazione per altri campi

                                $section->addText($this->cleanTextForWord($fieldName . ':'), $headingStyle);

                                $paragraphs = $this->splitHtmlIntoParagraphs($fieldContent);


                                foreach ($paragraphs as $paragraph) {

                                    if (!empty($paragraph)) {

                                        $section->addText($this->cleanTextForWord($paragraph), $normalStyle, ['alignment' => 'both']);

                                        //$section->addTextBreak(1);

                                    }

                                }

                            }

                        } catch (\Exception $e) {

                            continue;

                        }

                    }

                }

            } catch (\Exception $e) {

                continue;

            }

        }


        // Add headers and footers

        $header = $section->addHeader();

        $header->addText('Naples Forum on Service - Selected Papers', ['size' => 8], ['alignment' => 'center']);


        $footer = $section->addFooter();

        $footer->addPreserveText('Page {PAGE} of {NUMPAGES}', ['size' => 8], ['alignment' => 'center']);


        try {

            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

            $objWriter->setUseDiskCaching(true, sys_get_temp_dir());

            $tempFile = tempnam(sys_get_temp_dir(), 'papers') . '.docx';

            $objWriter->save($tempFile);


            if (!file_exists($tempFile) || filesize($tempFile) === 0) {

                throw new \Exception('Errore nella creazione del file temporaneo');

            }


            $content = '';

            $handle = fopen($tempFile, 'rb');

            while (!feof($handle)) {

                $content .= fread($handle, 8192);

            }

            fclose($handle);


            @unlink($tempFile);


            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->header('Content-Disposition', 'attachment; filename="papers-' . date('YmdHis') . '.docx"')
                ->header('Content-Length', strlen($content))
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache');

        } catch (\Exception $e) {

            return response()->json(['error' => 'Errore durante la generazione del documento: ' . $e->getMessage()], 500);

        }

    }



    /**
     * Split HTML content into paragraphs, preserving line breaks and formatting
     *
     * @param string $html
     * @return array
     */

    /**
     * Split HTML content into paragraphs, preserving line breaks and formatting
     *
     * @param string $html
     * @return array
     */

    private function splitHtmlIntoParagraphs($html)

    {

        if (empty($html)) {

            return [];

        }


        // Normalizza i caratteri di nuova linea

        $html = preg_replace('/\r\n|\r/', "\n", $html);


        // Sostituisci i tag HTML che indicano nuove righe o paragrafi

        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $html = preg_replace('/<\/p>\s*<p>/i', "\n", $html);

        $html = preg_replace('/<\/p>/i', "\n", $html);

        $html = preg_replace('/<p>/i', "", $html);

        $html = preg_replace('/<\/div>\s*<div>/i', "\n", $html);

        $html = preg_replace('/<\/div>/i', "\n", $html);

        $html = preg_replace('/<div>/i', "", $html);


        // Decodifica le entità HTML

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');


        // Rimuovi tutti gli altri tag HTML

        $text = strip_tags($html);


        // Dividi il testo in paragrafi basati su singole nuove righe

        $paragraphs = preg_split('/\n+/', $text, -1, PREG_SPLIT_NO_EMPTY);


        // Pulisci ogni paragrafo

        $cleanedParagraphs = [];

        foreach ($paragraphs as $paragraph) {

            $paragraph = trim($paragraph);

            if (!empty($paragraph)) {

                $cleanedParagraphs[] = $this->cleanTextForWord($paragraph);

            }

        }


        return array_filter($cleanedParagraphs);

    }



    /**
     * Pulisce il testo da caratteri problematici per Word
     *
     * @param string $text Testo da pulire
     * @return string Testo pulito
     */


    /**
     * Pulisce il testo da caratteri problematici per Word
     *
     * @param string $text Testo da pulire
     * @return string Testo pulito
     */

    private function cleanTextForWord($text)

    {

        if (empty($text)) {

            return '';

        }


        // Converti il testo in UTF-8, rimuovendo caratteri non validi

        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');


        // Rimuovi caratteri di controllo e non stampabili

        $text = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);


        // Sostituisci caratteri problematici con alternative sicure

        $replacements = [

            "\xE2\x80\x93" => '-', // en dash (U+2013)

            "\xE2\x80\x94" => '-', // em dash (U+2014)

            "\xE2\x80\x98" => "'", // apostrofo curvo sinistro (U+2018)

            "\xE2\x80\x99" => "'", // apostrofo curvo destro (U+2019)

            "\xE2\x80\x9C" => '"', // virgolette curve sinistre (U+201C)

            "\xE2\x80\x9D" => '"', // virgolette curve destre (U+201D)

            "\xE2\x80\xA6" => '...', // ellissi (U+2026)

            "\xE2\x80\xA2" => '*', // bullet point (U+2022)

            "\xC2\xA9" => '(c)', // copyright (U+00A9)

            "\xC2\xAE" => '(R)', // registered (U+00AE)

            "\xE2\x84\xA2" => '(TM)', // trademark (U+2122)

            "\xE2\x82\xAC" => 'EUR', // euro (U+20AC)

            "\xC2\xA3" => 'GBP', // sterlina (U+00A3)

            "\xC2\xB0" => 'deg', // gradi (U+00B0)

            "\xC2\xB1" => '+/-', // più o meno (U+00B1)

            "\xC2\xA0" => ' ', // spazio non separabile (U+00A0)

            "&" => "and", // HTML entity per &

            "<" => "", // HTML entity per <

            ">" => "", // HTML entity per >

            "\xE2\x87\x92" => '=>', // Freccia (U+21D2)

            "\xE2\x86\x92" => '->', // Freccia destra (U+2192)

            "\xE2\x86\x90" => '<-', // Freccia sinistra (U+2190)

            "\xCE\xB1" => 'alpha', // Simbolo greco alpha (U+03B1)

            "\xCE\xB2" => 'beta', // Simbolo greco beta (U+03B2)

            "\xCE\xB3" => 'gamma', // Simbolo greco gamma (U+03B3)

        ];


        $text = str_replace(array_keys($replacements), array_values($replacements), $text);


        // Rimuovi qualsiasi carattere non stampabile o problematico

        $text = preg_replace('/[^\x20-\x7E\x0A\x0D\t]/u', '', $text);


        // Normalizza gli spazi e le linee

        $text = preg_replace('/\s+/', ' ', $text);

        $text = trim($text);


        return $text;

    }


    /**
     * Clean HTML content while preserving basic structure
     *
     * @param string $html
     * @return string
     */

    private function cleanHtmlContent($html)

    {

        if (empty($html)) {

            return '';

        }


        // Normalizza i caratteri di nuova linea

        $html = preg_replace('/\r\n|\r/', "\n", $html);


        // Sostituisci entità HTML problematiche

        $html = str_replace(

            ["\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x98", "\xE2\x80\x99", "&", "\xE2\x80\x93", "\xE2\x80\x94", "\xC2\xA0"],

            ['"', '"', "'", "'", "and", "-", "-", " "],

            $html

        );


        // Decodifica le entità HTML

        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');


        // Rimuovi tag HTML, preservando le nuove righe

        $text = strip_tags($html);


        // Pulisci il testo

        $text = $this->cleanTextForWord($text);


        // Normalizza le nuove righe

        $text = preg_replace('/\n{3,}/', "\n\n", $text);


        return trim($text);

    }


    /**
     * Convert HTML to plain text while preserving basic formatting
     *
     * @param string $html
     * @return string
     */

    private function htmlToPlainText($html)

    {

        // Replace <br>, <p>, <div> tags with newlines

        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $text = preg_replace('/<\/p>/i', "\n", $text);

        $text = preg_replace('/<\/div>/i', "\n", $text);


        // Remove all HTML tags

        $text = strip_tags($text);


        // Decode HTML entities

        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');


        // Normalize whitespace

        $text = preg_replace('/\s+/', ' ', $text);


        // Fix multiple newlines

        $text = preg_replace('/\n\s*\n/', "\n\n", $text);


        return trim($text);

    }


}

