<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

class CommonController extends Controller{

    public function __construct()
    {
        
    }

    /**
     * api返回数据
     */
    public function returnApi($code = 200 , $msg = 'ok', $data = null){
        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取页面信息
     * @param url string 网页地址
     */
    public function getPageData($url,$encode = true){
        $lib = New \App\Lib\Common();
        $res = $lib->getData($url,false);

        $code = mb_detect_encoding($res, array('GB2312','UTF-8', 'GBK'));

        if($code == 'EUC-CN' || $code == 'CP936'){
            $res = mb_convert_encoding($res, 'utf-8', 'gbk');
        }
        
        /**废弃 */
        // if($encode && $this->utf8_gb2312($res) == 'gb2312'){
        //     $res = mb_convert_encoding($res, 'utf-8', 'gbk');
        // }
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
    public function getContent($str,$type = 'single'){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/center>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){

            if($type == 'single'){
                return $matches[0][0];
            }else{
                return $matches;
            }
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

    /**
     * 从书库页面中摘取书籍内容块
     */
    public function getLibContent($str){
        $regex ="/<ul>.*?<\/ul>/ism"; 
        if(preg_match_all($regex, $str, $matches)){

            $regex2 ='/<div class=\"right\">.*?<\/div>/ism';

            preg_match_all($regex2, $matches[0][0], $matches2);

            return $matches2[0];
        }else{
            return [];
        }
    }

    /**解析书籍内容 */
    public function getBookInfo($str){
        $regexBookName = "/<a class=\"name\" href=\"(.*?)\">(.*?)<\/a>/i";
        $regexAuthorName = "/<a href=\"(.*?)\"?\s.*?>(.*?)<\/a>/i";
        $regex2 = "/<font style=\"color:red\">(.*?)<\/font>/";
        $regex3 = "/<span class=\"words\">字数：(.*?)<\/span>/";
        $regex5 = "/<p class=\"info\">?.*\s更新：(.*?)<\/p>/ism";


        preg_match_all($regexBookName, $str, $matches);

        preg_match_all($regexAuthorName, $str, $matches2);

        preg_match_all($regex2, $str, $matches3);

        preg_match_all($regex3, $str, $matches4);


        if(!isset($matches[1][0])){
            echo "数据读取错误1";
            dd($matches);
        }

        if(!isset($matches[2][0])){
            echo "数据读取错误2";
            dd($matches);
        }

        if(!isset($matches2[2][0])){
            echo "数据读取错误3";
            var_dump($str);
            dd($matches2);
        }

        if(!isset($matches3[1][0])){
            preg_match_all($regex5, $str, $matches3);

            if(!isset($matches3[1][0])){
                echo "数据读取错误4";
                var_dump($str);
                dd($matches3);
            }
        }

        if(!isset($matches4[1][0])){
            echo "数据读取错误5";
            dd($matches4);
        }

        return [
            'book_name' => $matches[2][0] , 
            'book_url' => $matches[1][0]  , 
            'author_name' => $matches2[2][0] ,  
            'last_update_date' => $matches3[1][0] , 
            'boook_words' => $matches4[1][0]
        ];
    }

    /**
     * 获取书库最后的分页
     * @param str string 首页信息
     */
    public function getLibLastPage($str){
        $regex ="/<a class=\"endPage\" href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){

            $array = explode('.',$matches[1][0]);

            $array1 = explode('-',$array[0]);

            $lastPage = array_pop($array1);

            $shuKuUrl = implode('-',$array1);

            return ['shuKuUrl' =>  $shuKuUrl , 'lastPage' => $lastPage];

        }else{
            return '';
        }
    }

    /*获取页面内容*/
    public function getChapterContentFromDatabase($str){
        // if(strpos($str,'502') !== false){
        //     return false;
        // }
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 判断字符串是utf-8 还是gb2312
     * @param unknown $str
     * @param string $default
     * @return string
     */
    public function utf8_gb2312($str, $default = 'gb2312')
    {
        $str = preg_replace("/[\x01-\x7F]+/", "", $str);

        if (empty($str)) return $default;
        
        $preg = array(
            "gb2312" => "/^([\xA1-\xF7][\xA0-\xFE])+$/", //正则判断是否是gb2312
            "utf-8" => "/^[\x{4E00}-\x{9FA5}]+$/u",   //正则判断是否是汉字(utf8编码的条件了)，这个范围实际上已经包含了繁体中文字了
        );
        
        if ($default == 'gb2312') {
            $option = 'utf-8';
        } else {
            $option = 'gb2312';
        }
        
        if (!preg_match($preg[$default], $str)) {
            return $option;
        }
        $str = @iconv($default, $option, $str);
        
        //不能转成 $option, 说明原来的不是 $default
        if (empty($str)) {
            return $option;
        }

        return $default;
    }
}

