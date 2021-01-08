<?php

use Illuminate\Http\Request;

Route::namespace('Admin')->group(function () {

    Route::get('/website/list', 'WebsiteController@websiteList');
});