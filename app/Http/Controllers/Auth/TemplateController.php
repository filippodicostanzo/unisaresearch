<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
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

        $this->title = 'template';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Template::orderBy('id', 'ASC')->get();
        return view('admin.templates.index', ['items' => $items, 'title' => $this->title]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.templates.create', ['title' => $this->title]);
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
            'fields' => 'required'
        ]);

        $template = new Template($request->all());

        $template->fields = json_encode($request->fields);

        if ($template->active) {
            Template::where('active', '=', 1)->update(['active' => 0]);
        }

        $res = $template->save();
        $message = $res ? 'Template ' . $template->name . ' has been saved' : 'Template ' . $template->name . ' was note saved';
        session()->flash('message', $message);
        //return redirect()->route('templates.index');

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Template $template
     * @return \Illuminate\Http\Response
     */
    public function show(Template $template)
    {
        $item = $template;
        return view('admin.templates.show', ['item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Template $template
     * @return \Illuminate\Http\Response
     */
    public function edit(Template $template)
    {
        $item = $template;
        return view('admin.templates.edit', ['item' => $item, 'title' => $this->title]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Template $template
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Template $template)
    {

        $data = $request->except(['fields']);

        if ($request->active) {
            Template::where('active', '=', 1)->update(['active' => 0]);
        }

        $res = Template::find($template->id)->update($data);
        $message = $res ? 'Template ' . $data['name'] . ' has been saved' : 'Template ' . $data['name'] . ' was note saved';
        session()->flash('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Template $template
     * @return \Illuminate\Http\Response
     */
    public function destroy(Template $template)
    {
        $res = $template->delete();
        $message = $res ? 'Il Video ' . $template->name . ' è stato cancellato' : 'La Gallery ' . $template->name . ' non è stato cancellato';
        session()->flash('message', $message);
    }
}
