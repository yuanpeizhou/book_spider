<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookHandleController extends CommonController{

    protected $chapterModel ;

    public function __construct()
    {
        $this->chapterModel = New \App\Models\ChapterModel();
    }

    /**处理网页原数据 */
    public function handle(){
        $test = $this->chapterModel->find(1)->source_content;

        // dd($test);

        $content = $this->getChapterContent($test);

        dd($content);
    }

    /*获取页面内容*/
    public function getChapterContent($str){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/center>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){
            dd($matches);
        }else{
            return '';
        }
    }

}

