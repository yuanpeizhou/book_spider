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

Artisan::command('book {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\SpiderController($file_name,$url);
    $book->handleBook();
})->describe('spider book');

Artisan::command('bookscan  {file_name} {url}', function ($file_name,$url) {
    $book = New \App\Http\Controllers\Api\BookScanController($file_name,$url);
    $book->scan();
})->describe('scan book');

/**1 */
Artisan::command('libscan', function () {
    $lib = New \App\Http\Controllers\Api\LibScanController();
    $lib->scan();
})->describe('扫描书籍目录');

/**2 */
Artisan::command('bookChapterScan', function () {
    $book = New \App\Http\Controllers\Api\BookScanController();
    $book->chapterNumScan();
})->describe('扫描章节目录');

/**3 */
Artisan::command('bookChapterHandle {start} {end}', function ($start , $end) {
    $chapter = New \App\Http\Controllers\Api\ChapterScanController();
    $chapter->scan($start,$end);
})->describe('爬取章节内容');

/**4 */
Artisan::command('imgHandle', function () {
    $word = New \App\Http\Controllers\Spider\WordController();
    $word->handle();
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');


/**6 */
Artisan::command('bookCheck', function () {
    $check = New \App\Http\Controllers\Api\BookCheckController();
    $check->checkBookComplex();
})->describe('检查所有书籍爬取结果');


/**爬取套图列表 */
Artisan::command('SetImg', function () {
    $setImg = New \App\Http\Controllers\Api\SetImgController();
    $setImg->scan();
})->describe('spider Set Img');


/**爬取套图图片信息 */
Artisan::command('Img', function () {
    $setImg = New \App\Http\Controllers\Api\SetImgController();
    $setImg->spider();
})->describe('spider Imgs');

/**爬取套图图片资源 */
Artisan::command('spider', function () {
    $setImg = New \App\Http\Controllers\Api\SetImgController();
    $setImg->img();
})->describe('spider source');


/**爬取宅男女神 */
Artisan::command('znns', function () {
    $obj = New \App\Http\Controllers\Api\ZnnsController();
    $obj->scan();
})->describe('spider znns source');

/**爬取国家地区划分 */
Artisan::command('address', function () {
    $obj = New \App\Http\Controllers\Api\DistrictController();
    $obj->scan();
})->describe('spider address str');

/**爬取凹图吧 */
Artisan::command('aotuba', function () {
    $obj = New \App\Http\Controllers\Api\AotubaController();
    $obj->scan();
})->describe('spider aotuba source');

/**24fa套图扫描 */
Artisan::command('fascan', function () {
    $obj = New \App\Http\Controllers\Api\FaController();
    $obj->scan();
})->describe('scan fa source');

/**24fa资源爬取 */
Artisan::command('faspider', function () {
    $obj = New \App\Http\Controllers\Api\FaController();
    $obj->spider();
})->describe('spider fa source');

/**颜控套图扫描 */
Artisan::command('yanscan', function () {
    $obj = New \App\Http\Controllers\Spider\YankongController();
    $obj->scan();
})->describe('scan yan source');

/**颜控资源爬取 */
Artisan::command('yanspider', function () {
    $obj = New \App\Http\Controllers\Spider\YankongController();
    $obj->spider();
})->describe('spider yan source');

/**图片压缩 */
Artisan::command('tinyimg', function () {
    $obj = New \App\Http\Controllers\Api\TinyController();
    $obj->img();
})->describe('handle img');