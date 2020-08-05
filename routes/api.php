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
Route::namespace('Api')->group(function () {

    Route::get('/test', function(){
        return '123';
    });

    Route::get('/book/list', 'BookController@bookList');

    Route::get('/book/info', 'BookController@bookInfo');

    Route::post('/book/handleBook', 'BookController@handleBook');

    Route::post('/book/handleBook', 'BookController@handleBook');

    Route::get('/book/export', 'BookController@bookExport');

    Route::get('/word/list', 'WordAdminController@wordList');

    Route::post('/word/update', 'WordAdminController@wordUpdate');

    Route::get('/book', 'SpiderController@test');

    Route::get('/book/sacn', 'BookScanController@scan');

    Route::get('/img/handle', 'WordController@handle');

    Route::get('/lib/sacn', 'LibScanController@scan');

    Route::get('/lib/new', 'LibScanController@new');

    Route::get('/book/chapterNumSacn', 'BookScanController@chapterNumScan');//章节链接扫描

    Route::get('/book/handle', 'BookHandleController@handle');//处理源数据
});

