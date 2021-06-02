<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->title = __('titles.calendar');
    }

    public function index() {
        $items = Event::where('active','1')->with('room_fk')->get();
        $rooms = Room::where('visible',1)->get();
        return view('calendar.index', ['items' => $items, 'title' => $this->title, 'rooms'=>$rooms]);
    }
}
