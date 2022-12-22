<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $this->title = __('titles.users');
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
                $items = User::whereRoleIs('superadministrator')->orwhereRoleIs('administrator')->with('roles')->orderBy('id', 'ASC')->get();
            } else if ($type === 'supervisor') {
                $items = User::whereRoleIs('supervisor')->with('roles')->orderBy('id', 'ASC')->get();
            } else if ($type === 'researcher') {
                $items = User::whereRoleIs('researcher')->with('roles')->orderBy('id', 'ASC')->get();
            } else if ($type === 'user') {
                $items = User::whereRoleIs('user')->with('roles')->orderBy('id', 'ASC')->get();
            } else {
                $items = User::with('roles')->orderBy('id', 'ASC')->get();
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
     * Display the specified resource.
     *
     * @param \App\Models\Author $author
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $item = $user;
        $roles = Role::all()->sortBy('name');
        $role = $user->getRoles();
        $role_id = Role::where('name', $role)->first();

        return view('admin.users.show', ['item' => $item, 'role' => $role[0], 'title' => $this->title]);
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


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */

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
        } else {
            unset($data['password']);
        }

        unset($data['new_password']);


        /**
         *
         * SEND EMAIL WHEN CHANGE USER ROLE
         *
         */

        if (isset($user->id)) {

            $roles = Role::all()->sortBy('name');
            $role = $user->getRoles();
            $role_id = Role::where('name', $role)->first();

            if (!isset($data['role'])) {
                $res = User::find($user->id)->update($data);
            } else {


                $new_role = Role::where('id', $data['role'])->first();


                if ($role_id['id'] !== $data['role']) {

                    if ($role_id['id'] == '5' && $data['role'] == '4') {
                        Mail::to($user->email)->send(new \App\Mail\ApprovedAccount($user));
                    } else {
                        Mail::to($user->email)->send(new \App\Mail\ChangeUserRole($user, $new_role['name']));
                    }
                }


                DB::table('role_user')
                    ->where('user_id', $user->id)
                    ->update([
                        'role_id' => $data['role'],
                    ]);

                $res = User::find($user->id)->update($data);
            }
        } else {
            $res = User::find($request->id)->update($data);
        }


        $message = $res ? 'User ' . $user->name . ' ' . $user->surname . ' has been saved' : 'User ' . $user->name . ' ' . $user->surname . ' was not saved';
        session()->flash('message', $message);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {

        if (($this->user->hasRole('superadministrator|administrator'))) {
            $res = $user->delete();
            $message = $res ? 'User ' . $user->name . ' ' . $user->surname . ' has been deleted' : 'User ' . $user->name . ' ' . $user->surname . ' was not deleted';
            session()->flash('message', $message);
        } else {
            return abort(403);
        }
    }


    public function vueTable() {

        $items = User::with('roles')->orderBy('id', 'ASC')->get();

        return view('admin.users.vuetable', ['items' => $items, 'title' => $this->title]);

    }

}
