<?php

namespace App\Http\Controllers\Api;

class CommonController{

    public function __construct()
    {
        
    }

    /**
     * 获取页面信息
     * @param url string 网页地址
     */
    public function getPageData($url){
        $lib = New \App\Lib\Common();
        $res = $lib->getData($url,false);
        $res = mb_convert_encoding($res, 'utf-8', 'gb2312');
        return $res;
    }

    /**
     * 页面章节信息
     * @param str string 目录页面
     */
    public function getChapterList($str){
        $regex ="/<ul .*?>.*?<\/ul>/ism"; 
        if(preg_match_all($regex, $str, $matches)){
            // return isset($matches[0]) ? $matches[0][1] : '';
            $regex2 ="/<a href=\"(.*?)\".*?>.*?<\/a>/i";
            preg_match_all($regex2, $matches[0][1], $matches2);
            return $matches2[1];
        }else{
            return '';
        }
    }

    /**
     * 获取目录最后的分页
     * @param str string 首页信息
     */
    public function getDirLastPage($str){
        $regex ="/<a class=\"endPage\" href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){
            $pageArray = explode('/',$matches[1][0]);
            $pageArray2 = explode('_',$pageArray[2]);

            return $pageArray2[1];
        }else{
            return '';
        }
    }

    /*获取页面内容*/
    public function getContent($str){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/center>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){
            return $matches[0][0];
        }else{
            return '';
        }
    }

    /*获取文本*/
    public function getTxt($str){
        $regex ="/<p>.*?<\/p>/ism"; 
        if(preg_match_all($regex, $str, $matches)){
            return $matches[0][0];
        }else{
            return '';
        }
    }

    /*获取文本中的图片列表*/
    public function getImgList($str){
        $regexImg = "/<img src.*?>/i";
        if(preg_match_all($regexImg,$str,$matches)){
            return $matches[0];
        }else{
            return [];
        }
    }

    /*获取章节分页信息*/
    public function getChapterLastPage($str){
        $regex ="/<a href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){
            $last = array_pop($matches[1]);
            $pageArray = explode('_',$last);
            $pageArray2 = explode('.',$pageArray[1]);
            return $pageArray2[0];
        }else{
            return '';
        }
    }
}