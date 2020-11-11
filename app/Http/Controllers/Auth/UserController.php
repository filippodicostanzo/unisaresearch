<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Monarobase\CountryList\CountryListFacade;

class UserController extends Controller
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

        $this->title = 'users';
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($this->user->hasRole('superadministrator|administrator')) {

            $type = $request->query('type');

            if ($type === 'administrator') {
                $items = User::whereRoleIs('superadministrator')->orwhereRoleIs('administrator')->orderBy('id', 'ASC')->get();
            } else if ($type === 'supervisor') {
                $items = User::whereRoleIs('supervisor')->orderBy('id', 'ASC')->get();
            } else if ($type === 'researcher') {
                $items = User::whereRoleIs('researcher')->orderBy('id', 'ASC')->get();
            } else if ($type === 'user') {
                $items = User::whereRoleIs('user')->orderBy('id', 'ASC')->get();
            } else {
                $items = User::orderBy('id', 'ASC')->get();
            }

            foreach ($items as $item) {
                foreach ($item->roles as $role)
                    $item->role = $role->display_name;
            }
        }

        return view('admin.users.index', ['items' => $items, 'title' => $this->title]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $countries = json_decode(CountryListFacade::getList('en', 'json'), true);


        $roles = Role::all()->sortBy('name');
        return view('admin.users.create', ['roles' => $roles, 'title' => $this->title, 'countries' => $countries]);
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
            'surname' => 'required',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);


        $user = new User($request->all());

        $res = $user->save();

        $user->attachRole($request->role);

        $message = $res ? 'The User ' . $user->name . ' has been saved' : 'The User ' . $user->name . ' was not saved';
        session()->flash('message', $message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $item = $user;

        $countries = json_decode(CountryListFacade::getList('en', 'json'), true);

        $roles = Role::all()->sortBy('name');
        $role = $user->getRoles();
        $role_id = Role::where('name', $role)->first();

        return view('admin.users.edit', ['item' => $item, 'role' => $role_id, 'title' => $this->title, 'roles' => $roles, 'countries' => $countries, 'password' => $item->getAuthPassword()]);
    }


    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data = $request->all();

        if ($data['new_password']) {

            $data['password'] = Hash::make($data['password']);
        }
        else {
            unset($data['password']);
        }

        unset($data['new_password']);

        $res = User::find($user->id)->update($data);
        $message = $res ? 'The Category ' . $user->name . ' è stato modificato' : 'Il Documento ' . $user->name . ' non è stata modificato';
        session()->flash('message', $message);
    }

}
