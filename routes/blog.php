<?php

use Illuminate\Support\Facades\Route;

//Route::get('articles','ArticleController@index');
//Route::get('articles/{id}','ArticleController@show');
//Route::post('articles','ArticleController@store');
//Route::put('articles/{id}','ArticleController@update');
//Route::delete('articles/{id}','ArticleController@destroy');

 Route::apiResource('articles','ArticleController',['parameters' => ['articles' => 'id']]);