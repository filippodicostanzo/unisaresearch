<?php

use App\Http\Controllers\Auth\AuthorController;
use App\Http\Controllers\Auth\PostController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ReviewController;
use App\Http\Controllers\Auth\TemplateController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\CalendarController;
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

Route::get('/calendar', [CalendarController::class, 'index']);


Route::get('templates/{id}', [TemplateController::class, 'jsonTemplate']);



Route::get('/home', function () {
    if (Auth::user()->hasRole('superadministrator')) {
        return view('admin.home.index');
    } else {
        return view('home');
    }
})->middleware('verified');


Route::group(['namespace' => 'Auth'], function () {

    //Route::get('/posts/{id}', [PostController::class, 'single'])->middleware('verified')->name('posts.single');
    // Route::get('/posts', [PostController::class, 'index'])->middleware('verified');

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


    Route::get('authors/all', [AuthorController::class, 'authorsadmin'])->name('authors.admin');
    Route::post('authors/all/generate', [AuthorController::class, 'generate']);

    Route::resource('templates', 'TemplateController');
    Route::resource('categories', 'CategoryController');

    Route::get('/authors/search', [
        \App\Http\Controllers\Auth\AuthorController::class,
        'search'
    ])->name('authors.admin.search');


    Route::resource('authors', 'AuthorController');

    Route::resource('users', 'UserController');
    Route::post('users/generate', [UserController::class, 'generate']);

    //Route::resource('posts', 'PostController');
    Route::resource('statuses', 'StatusController');
    Route::resource('rooms', 'RoomController');
    Route::resource('events', 'EventController');
    Route::resource('reviews', 'ReviewController');
    Route::resource('editions', 'EditionController');

    Route::get('/posts/all', [PostController::class, 'postsadmin'])->name('posts.admin');

    Route::get('/posts/{post}/link', [PostController::class, 'link']);
    Route::patch('/posts/{post}/link', [PostController::class, 'updatelink'])->name('posts.link');

    Route::get('/posts/{post}/validate', [PostController::class, 'valid']);
    Route::patch('/posts/{post}/validate', [PostController::class, 'validupdate'])->name('posts.valid');



    Route::get('reviewers/count', [ReviewController::class, 'count']);

    Route::get('/calendar', [CalendarController::class, 'index']);

    Route::post('/posts/all/generate', [\App\Http\Controllers\Auth\PostController::class, 'generate']);

    Route::get('/clear-cache', function() {
        \Artisan::call('cache:clear');
        return 'Application cache cleared';
    });

});

Route::group(['namespace' => 'App\Http\Controllers\Auth', 'prefix' => 'admin', 'middleware' => ['role:superadministrator|supervisor']], function () {
    Route::resource('reviews', 'ReviewController');
    Route::get('/posts/review', [PostController::class, 'postsreviewer'])->name('posts.reviewer');
});


Route::group(['namespace' => 'Auth', 'middleware' => ['role:user|researcher|supervisor']], function () {

    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile/{id}', [UserController::class, 'update']);

    /*    Route::get('/profile', function () {
        return view('profile.index');
    })->middleware('verified');*/
});


/**
 *
 * RESEARCHER
 *
 */

Route::group(['namespace' => 'App\Http\Controllers\Auth', 'prefix' => 'admin', 'middleware' => ['role:researcher|superadministrator|administrator']], function () {


    //Route::resource('authors', 'AuthorController');


    /*    Route::get('/profile', function () {
        return view('profile.index');
    })->middleware('verified');*/
});


Route::group(['namespace' => 'App\Http\Controllers\Auth', 'prefix' => 'admin', 'middleware' => ['role:researcher|superadministrator|administrator|supervisor']], function () {


    Route::post('authors/check-exists', [AuthorController::class, 'checkExists'])->name('authors.check-exists');
    Route::resource('posts', 'PostController');
    Route::get('/posts', [PostController::class, 'index'])->name('posts.author');
    Route::resource('authors', 'AuthorController');
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile/{id}', [UserController::class, 'update'])->middleware('verified');


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
/*
Route::get('/{catchall?}', function () {
    return response()->view('authors.create');
})->where('catchall', '(.*)');
*/
