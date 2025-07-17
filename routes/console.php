<?php

use App\Http\Controllers\Spider\ImgController;
use App\Http\Controllers\Spider\RoseController;
use App\Models\BookModel;
use App\Models\ChapterModel;
use Illuminate\Foundation\Inspiring;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

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

Artisan::command('book {website_id}', function ($website_id) {
    $book = New \App\Http\Controllers\Spider\BookController();
    $book->handle($website_id);
})->describe('扫描书籍');

Artisan::command('chapter_list {website_id}', function ($website_id) {
    $chapterList = New \App\Http\Controllers\Spider\ChapterListController();
    $chapterList->handle($website_id);
})->describe('扫描章节目录');

Artisan::command('chapter_content {website_id}', function ($website_id) {
    $chapter = New \App\Http\Controllers\Spider\ChapterContentController();
    $chapter->handle($website_id);
})->describe('爬取章节内容');

/**4 */
Artisan::command('imgHandle', function () {
    $word = New \App\Http\Controllers\Api\WordController();
    $word->handle();
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');

Artisan::command('imgHandle2', function () {
    $word = New \App\Http\Controllers\Api\WordController();
    $word->handle2();
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');

Artisan::command('rosescan {website_id}', function ($website_id) {
    $rose = New RoseController();
    $rose->handle($website_id);
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');

Artisan::command('imgscan {website_id}', function ($website_id) {
    $rose = New ImgController();
    $rose->handle($website_id);
    // $this->info("Building {$tag}!");
})->describe('扫描出图片资源');

Artisan::command('roseImgHandle {website_id}', function ($website_id) {
    $rose = New ImgController();
    $rose->saveImg($website_id);
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

Artisan::command('zhou_test', function () {
    $bookModel = New BookModel();
    $books = $bookModel->pluck('website_id', 'id');

    $chapterModel = New ChapterModel();
    $handle = true;
    $i = 1;
    while ($handle) {
        $chapters = $chapterModel->whereNull('website_id')->orderBy('id')->limit(1000)->get();
        foreach ($chapters as $chapter) {
            $chapterModel->where('id', $chapter->id)->update(["website_id" => $books[$chapter->book_id]]);
        }
        echo $i . "\r\n";
        $i++;
    }
})->describe('handle img');