<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::apiResource('posts', 'API\PostController');


Route::post('login', 'API\ConnectionController@login');
Route::get('check_blance', 'API\PostController@check_blance');
Route::post('create_user', 'API\AuthController@api_signup');

Route::group(['middleware' => ['connection']], function () {
    
    Route::get('logins', 'API\AuthController@logins');
    Route::middleware('auth:api')->group(function () {
    Route::POST('get_invoice', 'API\PostController@showinvoice');
    Route::POST('get_invoice_details', 'API\PostController@show_invoice_details');
    Route::POST('pay_invoice', 'API\PostController@store');
    Route::POST('get_paid_invoices', 'API\PostController@show_index');
    Route::POST('cancel_invoice', 'API\PostController@cancel');
    Route::POST('get_invoices', 'API\PostController@showinvoices');

    //Route::POST('get_cancel_invoices', 'API\PostController@show_cancel_invoices');
    //Route::POST('get_paid_invoices', 'API\PostController@index');

});

});
