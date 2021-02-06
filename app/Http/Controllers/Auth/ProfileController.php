<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Monarobase\CountryList\CountryListFacade;

class ProfileController extends Controller
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

        $this->title = __('titles.profile');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $item = $this->user;

        $countries = json_decode(CountryListFacade::getList('en','json'), true);

        $roles = Role::all()->sortBy('name');

        $role =  $this->user->getRoles();

        $role_id = Role::where('name', $role)->first();

        return view('profile.edit', ['item' => $item, 'role'=>$role_id, 'title' => $this->title, 'roles' => $roles,'countries'=>$countries, 'password'=>$item->getAuthPassword()]);
    }


}
