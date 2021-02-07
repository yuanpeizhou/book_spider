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
    
    $res = $ocr->image('D:\Wamp\www\book_spider\public\word\test.png')
    ->lang('chi_sim', 'chi_tra')
    ->run();

    dd($res);
});