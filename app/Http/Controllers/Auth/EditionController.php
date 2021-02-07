<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditionController extends Controller
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

        $this->title = __('titles.editions');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Edition::orderBy('id', 'ASC')->get();
        return view('admin.editions.index', ['items' => $items, 'title' => $this->title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.editions.create', ['title' => $this->title]);
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
            'name' => 'required',
        ]);

        $edition = new Edition($request->all());

        if ($edition->active) {
            Edition::where('active', '=', 1)->update(['active' => 0]);
        }

        $res = $edition->save();
        $message = $res ? 'Edition ' . $edition->name . ' has been saved' : 'Edition ' . $edition->name . ' was note saved';
        session()->flash('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Edition $edition
     * @return \Illuminate\Http\Response
     */
    public function show(Edition $edition)
    {
        $item = $edition;
        return view('admin.editions.show', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Edition $edition
     * @return \Illuminate\Http\Response
     */
    public function edit(Edition $edition)
    {
        $item = $edition;
        return view('admin.editions.edit', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Edition $edition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Edition $edition)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $data = $request->all();

        if ($request->active) {
            Edition::where('active', '=', 1)->update(['active' => 0]);
        }

        $res = Edition::find($edition->id)->update($data);
        $message = $res ? 'Edition ' . $data['name'] . ' has been saved' : 'Edition ' .  $data['name']. ' was note saved';
        session()->flash('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Edition $edition
     * @return \Illuminate\Http\Response
     */
    public function destroy(Edition $edition)
    {

        try {
            $res = $edition->delete();
            $message = $res ? 'The Edition ' . $edition->name . ' has been deleted' : 'The Edition ' . $edition->name . ' has been deleted';
            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-success');
        } catch (\Exception $e) {
            if ($e->getCode() == '23000') {
                $message = 'The edition cannot be deleted because it is present in a paper';
                session()->flash('message', $message);
                session()->flash('alert-class', 'alert-danger');
            }
        }

    }
}
