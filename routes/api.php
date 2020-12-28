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

    Route::get('/word/md5', 'WordController@md5');

    Route::get('/book/list', 'BookController@bookList');

    Route::get('/book/info', 'BookController@bookInfo');

    Route::post('/book/handleBook', 'BookController@handleBook');

    Route::post('/book/handleChapter', 'BookController@handleChapter');

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

    Route::get('/chapter/spider', 'BookController@chapterSipder');//爬取章节内容

    Route::get('/video/list', 'VideoController@videoList');

    Route::get('/book/check', 'BookCheckController@checkBookComplex');

    Route::any('/camera/received', 'CameraController@received');
    Route::get('/camera/count', 'CameraController@getCountByDate');
    Route::get('/camera/log', 'CameraController@getLogList');

    Route::get('/img/setimg', 'SetImgController@scan');

    Route::get('/img/spider', 'SetImgController@spider');

    Route::get('/img/img', 'SetImgController@img');

    Route::get('/znns/scan', 'ZnnsController@scan');

    Route::get('/district/scan', 'DistrictController@scan');

    Route::get('/autotuba/scan', 'AotubaController@scan');

    Route::get('/zhongtu/scan', 'ZhongtuController@scan');

    Route::get('/24fa/scan', 'FaController@scan');

    Route::get('/24fa/spider', 'FaController@spider');

    Route::get('/dpi/output', 'DpiController@dpi');

    Route::get('/imgs/list', 'ImgsController@imgsList');

    Route::get('/imgs/info', 'ImgsController@imgsInfo');
});

