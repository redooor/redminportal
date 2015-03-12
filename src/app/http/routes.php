<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Redooor\Redminportal\App\Http\Controllers'], function () {
	Route::get('page', 'PageController@index');
});

