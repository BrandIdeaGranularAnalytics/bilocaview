<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

use Illuminate\Support\Facades\Auth;

// Route::get('/', function () {
//     return view('dashboard');
// });

// Route::get('/map', function () {
//     return view('map');
// })->name('map');

Route::get('/map', 'DashboardController@generatemenu');
Route::get('/map', 'DashboardController@changejson');


Route::get('chart', 'ChartController@index');
Route::post('/chart/{id}/viewchart', 'ChartController@viewchart');

Route::get('grid', 'GridController@index');
Route::get('map', 'MapController@index');

Route::get('/', function () {
    return view('pages.auth.login');
});


Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');


Route::post('logincheck', function() {

    $rules = array (
        'email' => 'required|max:255',
        'password' => 'required|max:25',
    );

    $v = Validator::make(Request::all(), $rules);


    if ($v->fails()) {
        Request::flash ("Unauthorized Acesss !!!!");
        return Redirect::to('login')->withErrors($v->messages());
    } else {
        $userdata = array (
            'email' => Request::get('email'),
            'password' => Request::get('password')
        );

        If (Auth::attempt($userdata)) {
            return Redirect::to('/dashboard');
        } else {
            return Redirect::to('login')->withErrors('Incorrect login details');
        }
    }

});


Route::get ('logout',function(){
    Auth::logout();
    return Redirect::to('/auth/login');
});

Route::resource('dashboard', 'DashboardController');

Route::resource('common', 'CommonController');

Route::resource('profile', 'ProfileController');

Route::resource('common', 'ContentController');

Route::post('dashboard/addoutlet','DashboardController@addoutlet');
Route::post('combine/rpi_action','CombineController@rpi_action');

Route::post('dashboard/addoutlet_image','DashboardController@addoutlet_image');
Route::post('dashboard/show_image','DashboardController@show_image');
Route::post('dashboard/delete_image','DashboardController@delete_image');


Route::post('dashboard/updateoutlet_premium','DashboardController@updateoutlet_premium');
Route::post('dashboard/updateoutlet','DashboardController@updateoutlet');
Route::post('dashboard/updateoutlet_potential','DashboardController@updateoutlet_potential');
Route::post('dashboard/updateoutlet_byid','DashboardController@updateoutlet_byid');
Route::post('dashboard/userhistory','DashboardController@userhistory');
Route::post('dashboard/notrelavantoutlet','DashboardController@notrelavantoutlet');
Route::post('dashboard/relavantoutlet','DashboardController@relavantoutlet');
Route::post('dashboard/notfoundoutlet','DashboardController@notfoundoutlet');
Route::post('dashboard/existingoutlet','DashboardController@existingoutlet');


Route::resource('user', 'User\UserController');
Route::post('/user/setpasswd', 'User\UserController@setpasswd');
Route::post('/user/register', 'User\UserController@register');
Route::get('/user/{id}/changepwd', 'User\UserController@changepwd');
Route::post('/user/resetpwd', 'User\UserController@resetpwd');
Route::post('/user/updatepwd', 'User\UserController@updatepwd');
Route::get('/user/{id}/menu', 'User\UserController@menu');

Route::resource('menumaster', 'MenuMasterController');

Route::group(['prefix' => 'auth'], function(){
    Route::get('login', function () { return view('pages.auth.login'); });
    Route::get('register', function () { return view('pages.auth.register'); });
    Route::get('setpasswd', function () { return view('pages.auth.setpasswd'); });

});

Route::group(['prefix' => 'error'], function () {
    Route::get('404', function () {
        return view('pages.error.404');
    });
    Route::get('500', function () {
        return view('pages.error.500');
    });
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

// 404 for undefined routes
Route::any('/{page?}', function () {
    return view('pages.error.404');
})->where('page', '.*');


/// Rega //
Route::post('dashboard', 'DashboardController@loadmapPost')->name('loadmap.post');
Route::post('common', 'CommonController@commonactivity')->name('commonactivity.post');
Route::post('dashboard/getsubchannel/{id}','DashboardController@getsubchannel');

/// REga //
