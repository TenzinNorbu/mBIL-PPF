<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

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
    try {
        return view('welcome');
    } catch (Exception $e) {
        return response()->view('errors.404', [], 404);
    }
});

Route::get('/{path}', function ($path) {
    try {
    	if (!Route::has($path)) {
    		abort(404);
    	}
    	return redirect()->route($path);
    }catch (Exception $e) {
        return response()->view('errors.404', [], 404);
    }
});

Route::get('/api/login', function () {

	// return 'you are in web.php api/login route';
    try {
    	if (Route::has('/api/login')) {
    		return redirect()->route($path);
    	}else {
    		abort(404);
    	}
    } catch (Exception $e) {
        return response()->view('errors.404', [], 404);
    }
});

Route::get('/{any}', function () {
    abort(404);
})->where('any', '.*aux|com1|com2.*');


