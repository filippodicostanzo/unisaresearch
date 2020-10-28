<?php

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

Route::group(['namespace' => 'Auth', 'prefix' => 'admin', 'middleware' => ['role:superadministrator']], function () {
    Route::get('/home', function () {
        return view('home');
    })->middleware('verified');
});

Route::group(['namespace' => 'Auth', 'prefix' => 'admin', 'middleware' => ['role:user']], function () {
    Route::get('/profile', function () {
        return view('profile.index');
    })->middleware('verified');
});

Auth::routes(['verify'=>true]);

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


/*Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});
*/
