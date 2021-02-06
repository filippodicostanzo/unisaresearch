<?php

namespace App\Http\Controllers\Auth;

use App\Models\Category;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
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

        $this->title = __('titles.status');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Status::orderBy('id', 'ASC')->get();
        return view('admin.statuses.index', ['items' => $items, 'title' => $this->title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.statuses.create', ['title' => $this->title]);
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
            'color' => 'required'
        ]);

        $status = new Status($request->all());

        $res = $status->save();
        $message = $res ? 'The Status ' . $status->name . ' has been saved' : 'The Status ' . $status->name . ' was not saved';
        session()->flash('message', $message);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function show(Status $status)
    {
        $item = $status;
        return view('admin.statuses.show', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function edit(Status $status)
    {
        $item = $status;
        return view('admin.statuses.edit', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Status $status)
    {


        $this->validate($request, [
            'name' => 'required',
            'color' => 'required'
        ]);

        $data = $request->all();
        $res = Status::find($status->id)->update($data);
        $message = $res ? 'The Status ' . $status->name . ' has been saved' : 'The Status ' . $status->name . ' was not saved';
        session()->flash('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Status $status)
    {
        //
    }
}
