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


Artisan::command('imgHandle', function () {
    $word = New \App\Http\Controllers\Api\WordController();
    $word->handle();
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');

Artisan::command('book {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\SpiderController($file_name,$url);
    $book->handleBook();
})->describe('spider book');

Artisan::command('bookscan  {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\BookScanController($file_name,$url);
    $book->scan();
})->describe('scan book');

Artisan::command('libscan', function () {
    $lib = New \App\Http\Controllers\Api\LibScanController();
    $lib->scan();
})->describe('scan lib');

Artisan::command('bookChapterScan', function () {
    $book = New \App\Http\Controllers\Api\BookScanController();
    $book->chapterNumScan();
})->describe('bookChapterScan');

Artisan::command('bookChapterHandle {start} {end}', function ($start , $end) {
    $chapter = New \App\Http\Controllers\Api\ChapterScanController();
    $chapter->scan($start,$end);
})->describe('bookChapterScan');

Artisan::command('test', function () {
    $chapter = New \App\Http\Controllers\Api\JwtController();
    $chapter->index();
})->describe('123');

/**检查所有书籍爬取结果 */
Artisan::command('bookCheck', function () {
    $check = New \App\Http\Controllers\Api\BookCheckController();
    $check->checkBookComplex();
})->describe('检查所有书籍爬取结果');
