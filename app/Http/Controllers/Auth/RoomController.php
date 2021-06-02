<?php

namespace App\Http\Controllers\Auth;

use App\Models\Edition;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RoomController extends Controller
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

        $this->title = __('titles.rooms');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $edition = Edition::where('active',1)->first();
        $items = Room::where('edition',$edition['id'])->orderBy('id', 'ASC')->with('edition_fk')->get();
        return view('admin.rooms.index', ['items' => $items, 'title' => $this->title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.rooms.create', ['title' => $this->title]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $edition = Edition::where('active',1)->first();


        $room = new Room($request->all());
        $room['edition']= $edition['id'];

        $res = $room->save();
        $message = $res ? 'The Room ' . $room->name . ' has been saved' : 'The Room ' . $room->name . ' was not saved';
        session()->flash('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room)
    {
        $item = $room;
        return view('admin.rooms.show', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(Room $room)
    {
        $item = $room;
        return view('admin.rooms.edit', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Room $room)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $data = $request->all();
        $res = Room::find($room->id)->update($data);
        $message = $res ? 'The Room ' . $room->name . ' has been saved' : 'The Room ' . $room->name . ' was not saved';
        session()->flash('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy(Room $room)
    {
        try {
            $res = $room->delete();
            $message = $res ? 'The Room ' . $room->name . ' has been deleted' : 'The Author ' . $room->name . ' was not deleted';
            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-success');
        } catch (\Exception $e) {
            if ($e->getCode() =='23000') {
                $message = 'The room cannot be deleted because it is present in a event';
                session()->flash('message', $message);
                session()->flash('alert-class', 'alert-danger');
            }
        }
    }
}
