<?php

use App\Http\Controllers\Auth\PostController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use UniSharp\LaravelFilemanager\Lfm;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Route::get('templates/{id}', [\App\Http\Controllers\Auth\TemplateController::class, 'jsonTemplate']);

Route::get('send-mail', function () {

    $details = [
        'title' => 'Mail from ItSolutionStuff.com',
        'body' => 'This is for testing email using smtp'
    ];


    dd("Email is Sent.");
});

Route::get('/home', function () {
    return view('home');
})->middleware('verified');



Route::group(['namespace' => 'Auth'], function () {

    Route::get('/posts/{id}', [PostController::class, 'single'])->middleware('verified')->name('posts.single');
    Route::get('/posts', [PostController::class, 'index'])->middleware('verified');

});

/**
 *
 * USER GUEST
 *
 */

Route::group(['namespace' => 'App\Http\Controllers\Auth', 'prefix' => '', 'middleware' => ['role:user']], function () {
    Route::get('/guest', function () {
        return view('guest.index');
    })->middleware('verified');

    //Route::resource('posts', 'PostController');
});

Route::group(['namespace' => 'App\Http\Controllers\Auth', 'prefix' => 'admin', 'middleware' => ['role:superadministrator']], function () {

    Route::get('/', function () {
        return view('admin.home.index');
    })->middleware('verified');

    Route::resource('templates', 'TemplateController');
    Route::resource('categories', 'CategoryController');
    Route::resource('authors', 'AuthorController');
    Route::resource('users', 'UserController');
    Route::resource('posts', 'PostController');
    Route::resource('statuses', 'StatusController');
    Route::resource('reviews', 'ReviewController');

    Route::get('/posts/{post}/link', [PostController::class, 'link']);
    Route::patch('/posts/{post}/link',[PostController::class, 'updatelink'])->name('posts.link');

    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile/{id}', [UserController::class, 'update'])->middleware('verified');

});

Route::group(['namespace' => 'Auth', 'middleware' => ['role:user|supervisor|researcher']], function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('verified');
    Route::patch('/profile/{id}', [UserController::class, 'update'])->middleware('verified');

    /*    Route::get('/profile', function () {
        return view('profile.index');
    })->middleware('verified');*/
});




/**
 *
 * RESEARCHER
 *
 */

Route::group(['namespace' => 'App\Http\Controllers\Auth','prefix' => 'admin', 'middleware' => ['role:researcher|superadministrator|administrator']], function () {


    Route::resource('authors', 'AuthorController');

    /*    Route::get('/profile', function () {
        return view('profile.index');
    })->middleware('verified');*/
});


Route::group(['namespace' => 'App\Http\Controllers\Auth','prefix' => 'admin', 'middleware' => ['role:researcher|superadministrator|administrator|supervisor']], function () {

    Route::resource('posts', 'PostController');

});



Auth::routes(['verify' => true]);

/*
Route::group(['namespace' => 'Auth','prefix'=>'admin', 'middleware' => ['role:superadministrator']], function () {
    Route::get('/home', function() {
        return view('home')->middleware('verified');
    });
});

Route::group(['namespace' => 'Auth', 'prefix'=>'admin', 'middleware' => ['role:administrator']], function () {
    Route::get('/profile', function() {
        return view('profile.index');
    })->name('profile')->middleware('verified');

});
*/
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


/*Route::get('/home', function() {
    return view('home');
})->name('home')->middleware('verified');*/


Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});

Route::get('/{catchall?}', function () {
    return response()->view('authors.create');
})->where('catchall', '(.*)');
