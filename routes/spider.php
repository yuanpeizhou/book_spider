<?php

use Illuminate\Http\Request;

Route::namespace('Spider')->group(function () {

    Route::get('/test/test', 'TestController@test');
});