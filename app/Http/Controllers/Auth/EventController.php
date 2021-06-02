<?php

namespace App\Http\Controllers\Auth;

use App\Models\Edition;
use App\Models\Event;
use App\Models\Post;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class EventController extends Controller
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

        $this->title = __('titles.events');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $edition = Edition::where('active', 1)->first();
        $items = Event::where('edition', $edition['id'])->orderBy('id', 'ASC')->with('room_fk')->get();
        $rooms = Room::where('visible', 1)->orderBy('id', 'ASC')->get();
        return view('admin.events.index', ['items' => $items, 'title' => $this->title, 'rooms' => $rooms]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $edition = Edition::where('active', 1)->first();
        $posts = $this->checkPaperForEvents();
        $rooms = Room::where('visible', 1)->orderBy('name', 'ASC')->get();
        $events = Event::where('active', 1)->where('type', 'poster')->get();
        return view('admin.events.create', ['title' => $this->title, 'rooms' => $rooms, 'posts' => $posts, 'events' => $events, 'edition' => $edition]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        // dd($first = Carbon::create($request['start']));

        //dd($request);


        $is_valid = $this->checkDate($request, 'new');

        if ($is_valid) {
            $event = new Event($request->all());
            $edition = Edition::where('active', 1)->first();
            $event['edition'] = $edition['id'];
            $res = $event->save();
            $message = $res ? 'The Event ' . $event->title . ' has been saved' : 'The Event ' . $event->title . ' was not saved';
            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-success');
        } else {
            return response()->json(['isValid' => false, 'errors' => 'The Date is busy']);
        }


    }

    public function checkDate($request, $source)
    {

        if ($source === 'new') {
            $events = Event::where('active', 1)->where('room', $request['room'])->where('type', 'poster')->get();
        } else if ($source === 'edit') {
            $events = Event::where('active', 1)->where('room', $request['room'])->where('type', 'poster')->where('id', '!=', $request['id'])->get();
        }

        foreach ($events as $event) {
            $start = Carbon::create($event->start);
            $end = Carbon::create($event->end)->subMinutes(1);
            $valid_start = Carbon::create($request['start'])->between($start, $end);
            $valid_end = Carbon::create($request['end'])->subMinutes(1)->between($start, $end);


            if ($valid_start || $valid_end) {
                return false;
            }

        }

        return true;

    }

    public function checkPaperForEvents()
    {
        $edition = Edition::where('active', 1)->first();
        $posts = Post::where('edition', $edition['id'])->where('state', '4')->with('state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk')->get();
        $events = Event::where('type', 'poster')->pluck('title')->toArray();

        $select = [];
        foreach ($posts as $post) {
            if (!in_array($post['title'], $events)) {
                array_push($select, $post);
            }
        }
        return $select;
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {

        $item = Event::where('id', $event->id)->with('room_fk')->first();
        return view('admin.events.show', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $item = $event;
        $edition = Edition::where('active', 1)->first();
        $posts = $this->checkPaperForEvents();
        if ($item->type==='poster') {
            array_push($posts, $item);
        }
        $rooms = Room::where('visible', 1)->orderBy('name', 'ASC')->get();
        $events = Event::where('active', 1)->where('type', 'poster')->get();
        return view('admin.events.edit', ['item' => $item, 'rooms' => $rooms, 'posts' => $posts, 'events' => $events, 'title' => $this->title, 'edition' => $edition]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $data = $request->all();


        $is_valid = $this->checkDate($request, 'edit');

        if ($is_valid) {
            $res = Event::find($event->id)->update($data);
            $message = $res ? 'The Event ' . $event->title . ' has been saved' : 'The Event ' . $event->title . ' was not saved';
            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-success');
        } else {
            return response()->json(['isValid' => false, 'errors' => 'The Date is busy']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $res = $event->delete();
        $message = $res ? 'The Event ' . $event->title . ' has been deleted' : 'The Event ' . $event->title . ' has been deleted';
        session()->flash('message', $message);
        session()->flash('alert-class', 'alert-success');
    }
}
