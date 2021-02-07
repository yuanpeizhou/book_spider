<?php

use Illuminate\Http\Request;

Route::namespace('Spider')->group(function () {

    Route::get('/test/test', 'TestController@test');

    Route::get('/test/ocr', 'TestController@ocr');

    Route::get('/yunv/test', 'YankongController@test');

    Route::get('/word/handle', 'WordController@handle');
});