<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');


Artisan::command('img {tag}', function ($tag) {
    $word = New \App\Http\Controllers\Api\WordController();
    $word->handle($tag);
    // $this->info("Building {$tag}!");
})->describe('find end');

Artisan::command('book {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\SpiderController($file_name,$url);
    $book->handleBook();
})->describe('spider book');

Artisan::command('bookscan  {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\BookScanController($file_name,$url);
    $book->scan();
})->describe('scan book');
