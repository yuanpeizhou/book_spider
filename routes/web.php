<?php
use thiagoalessio\TesseractOCR\TesseractOCR;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*测试ocr*/
Route::get('/ocr',function() {
    $ocr = new TesseractOCR();
    https://www.diyibanzhu4.pro/toimg/data/a1.png
    $res = $ocr->image('D:/Wamp/www/spider/public/img/9.png')
    ->lang('chi_sim', 'chi_tra')
    ->run();

    dd($res);
});


