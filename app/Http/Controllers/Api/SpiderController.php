<?php
namespace App\Http\Controllers\Api;

class SpiderController
{   

    public function __construct($url,$file_name)
    {
        $this->model = New \App\Models\WordModel();
        $this->common = New \App\Lib\Common();
        $this->website = 'https://www.diyibanzhu4.pro';
        $this->url = $url; //小说目录页面，省略了/
        $this->chapterList = [];
        $this->chapterLastPage = 1;
        $this->pageLastPage = 1;
        $this->file_name = $file_name;
    }

    /**/
    public function handleBook(){
        echo "开始爬取书籍,爬取网址为：".$this->url."\r\n";
        /*获取书籍首页数据*/
        $bookUrl = $this->url.'/';
        $bookHome = $this->getPageData($bookUrl);

        /*获取首页章节信息*/
        $page = $this->getPageList($bookHome);
        $this->chapterList = array_merge($this->chapterList,$page);

        /*获取章节分页信息*/
        $this->getBookLastPage($bookHome);

        /*循环获取剩余页面*/
        if($this->chapterLastPage > 1){
            for ($i=2 ; $i <= $this->chapterLastPage ; $i++) { 
                $chapterUrl = $this->url . '_' . $i . '/';
                $bookInfo = $this->getPageData($chapterUrl);
                $page = $this->getPageList($bookInfo);
                $this->chapterList = array_merge($this->chapterList,$page);
            }
        }
        echo "书籍章节扫描完毕\r\n";

        $this->handleChapter();

        echo "书籍爬取完毕\r\n"; 
    }

    /**/
    public function handleChapter(){
        foreach ($this->chapterList as $key => $value) {
            "开始爬取章节数据,章节路由：$value\r\n";
            /*获取第一页信息*/
            $url = $this->website . $value;
            $chapterData = $this->getPageData($url);
            $content = $this->getContent($chapterData);

            /*获取章节名称*/
            $title = $this->getPageTitle($chapterData);

            /*获取本章最后分页*/
            $this->getLastPage($content);

            echo "扫描章节分页完成：$this->pageLastPage\r\n";

            /*循坏写入获取章节文本*/
            $data = $this->handlePage($value);

            $this->writeTxt($data);
            
            echo "成功写入".$value."章节数据\r\n";
        }
    }

    public function handlePage($chapter){
        $chapterArray = \explode('.',$chapter);
        $res = '';

        for ($i=1; $i <= $this->pageLastPage; $i++) { 
            $url = $this->website . $chapterArray[0] . '_' .$i . '.html';
            $pageData = $this->getPageData($url);
            $content = $this->getContent($pageData);
            $txt = $this->getTxt($content);
            $txt = str_replace('&nbsp;',"",$txt);
            $txt = str_replace('<noscript>',"",$txt);
            $txt = str_replace('</noscript>',"",$txt);
            $txt = str_replace('<br />',"\r\n",$txt);
            $txt = str_replace('<p>',"",$txt);
            $txt = str_replace('</p>',"",$txt);
            
            $imgArray = $this->getImgList($txt);

            foreach ($imgArray as $key => $value) {

                $word = $this->handleWord($value);

                if($word){
                    $txt = str_replace($value,$word,$txt);
                }else{
                    $txt = str_replace($value,"",$txt);
                }
            }
            $res = $res . $txt;
            echo "成功爬取第".$i."页数据\r\n";
        }
        echo $chapter."章节数据爬取完毕\r\n";
        return $res;
    }

    /*获取页面信息*/
    public function getPageData($url){
        $res = $this->common->getData($url,false);
        $res = mb_convert_encoding($res, 'utf-8', 'gb2312');
        return $res;
    }

    public function test(){
        $this->handleBook();
        // $this->getTestData();
        // $url = $this->url.'/';

        // $res = $this->common->getData($url,false);

        // $str = mb_convert_encoding($res, 'utf-8', 'gb2312');
        // dd($str);
        // $str = $this->getPageList($str);
        // dd($str);

        // $title = $this->getPageTitle($str);

        // $str = $this->getContent($str);
        // $txt = $this->getTxt($str);
        // $page = $this->getPage($str);
        
        // if(preg_match_all($regexContent, $res, $matches)){ 
        //     $res = str_replace('&nbsp;',"",$matches[0][0]);
        //     $res = str_replace('<noscript>',"",$res);
        //     $res = str_replace('</noscript>',"",$res);
        //     $res = str_replace('<br />',"\r\n",$res);
        //     $res = str_replace('<p>',"",$res);
        //     $res = str_replace('</p>',"",$res);
            
        //     foreach ($resImg[0] as $key => $value) {

        //         $word = $this->handleWord($value);

        //         if($word){
        //             $res = str_replace($value,$word,$res);
        //         }else{
        //             $res = str_replace($value,"",$res);
        //         }
                
        //     }
        //     $this->writeTxt($res);
        // }else{ 
        //     dd('没匹配到');
        // }
        

    }

    /*获取书籍最后的分页*/
    public function getBookLastPage($str){
        $regex ="/<a class=\"endPage\" href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){
            $pageArray = explode('/',$matches[1][0]);
            $pageArray2 = explode('_',$pageArray[2]);

            $this->chapterLastPage = $pageArray2[1];
        }else{
            return '';
        }
    }
    

    /*目录页获取章节列表*/
    public function getPageList($str){
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

    /*获取章节名称*/
    public function getPageTitle($str){
        $regexContent="/<h1 class=\"page-title\".*?>(.*?)<\/h1>/i"; 
        if(preg_match_all($regexContent, $str, $matches)){
            return isset($matches[1]) ? $matches[1][0] : '';
        }else{
            return '';
        }
    }

    /*获取页面内容*/
    public function getContent($str){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
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
    
    /*获取分页信息*/
    public function getLastPage($str){
        $regex ="/<a href=\"(.*?)\".*?>.*?<\/a>/i";
        if(preg_match_all($regex, $str, $matches)){
            $last = array_pop($matches[1]);
            $pageArray = explode('_',$last);
            $pageArray2 = explode('.',$pageArray[1]);
            $this->pageLastPage = $pageArray2[0];
        }else{
            return '';
        }
    }

    /*获取文本中的图片列表*/
    public function getImgList($str){
        $regexImg = "/<img.*?>/i";
        if(preg_match_all($regexImg,$str,$matches)){
            return $matches[0];
        }else{
            return '';
        }
    }

    public function getTestData(){
        $str = "<img src='/toimg/data/q22.png' />的古玲珑】月上三杆，彩虹城的迎归大会已经进入尾声，大多数人都已经离去，只剩下少许人在清理遗留下的现场，唯有陈林居士与古玲珑依旧留在这里<img src='/toimg/data/q22.png' />";
        $regexImg = "/<img.*?>/i";
        if(preg_match_all($regexImg,$str,$resImg)){
            dd($resImg);
        }else{
            dd("没匹配到");
        }
    }


    /*图片转化文字*/
    public function handleWord($img){

        if(strlen($img) > 40){
            return false;
        }
        $wordModel = New \App\Models\WordModel();
        $exg = preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$img,$match);

        $srcArray = explode("/",$match[1]);
        $fileName = array_pop($srcArray);
        $result = $wordModel->where('tag',$fileName)->first();

        if($result){
            return $result->word;
        }else{
            return false;
        }
    }

    /*写入文档*/
    public function writeTxt($data){
        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->file_name .'.txt';
        $file = fopen($path,'a');
        fwrite($file,$data);
        fclose($file);
    }
}