<?php

use Illuminate\Http\Request;

Route::namespace('Admin')->group(function () {

    Route::get('/website/list', 'WebsiteController@websiteList');

    Route::get('/book/list', 'BookController@bookList');//书籍列表

    Route::get('/book/info', 'BookController@bookInfo');//书籍详情

    Route::post('/book/handleBook', 'BookController@handleBook');//处理书籍图片

    Route::post('/book/handleChapter', 'BookController@handleChapter');//爬取单章节

    Route::get('/book/export', 'BookController@bookExport');//导出书籍
    
    Route::get('book/getSpiderCommond','BookController@bookSpider');//获取书籍爬取命令

    /**
     * 颜控网
     */
    Route::get('/yankongImgs/list', 'YankongImgsController@imgsList');//导出书籍
    
    Route::get('/yankongImgs/info','YankongImgsController@imgsInfo');//获取书籍爬取命令
});