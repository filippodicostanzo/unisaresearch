<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Event;
use App\Models\Room;
use App\Models\Post;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->title = __('titles.calendar');
    }

    public function index() {
        $edition = Edition::where('active', 1)->first();
        $items = Event::where('active','1')->where('edition', $edition->id)->with('room_fk')->get();
        $rooms = Room::where('visible',1)->where('edition', $edition->id)->get();
        $posts = Post::with('state_fk', 'category_fk', 'template_fk', 'authors', 'users', 'user_fk')->get();
        return view('calendar.index', ['items' => $items, 'title' => $this->title, 'rooms'=>$rooms, 'posts'=>$posts]);
    }
}
