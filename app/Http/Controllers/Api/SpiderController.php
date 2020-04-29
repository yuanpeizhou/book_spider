<?php
namespace App\Http\Controllers\Api;

class SpiderController
{   

    public function __construct($book_name = null , $book_url = null)
    {   
        $this->model = New \App\Models\WordModel();
        $this->bookModel = New \App\Models\BookModel();
        $this->chapterModel = New \App\Models\ChapterModel();
        $this->common = New \App\Lib\Common();
        $this->website = 'https://www.diyibanzhu4.pro';
        $this->book_url = $book_url; //小说目录页面，省略了/
        $this->book_name = $book_name;
        $this->chapterList = [];
        $this->chapterLastPage = 1;
        $this->pageLastPage = 1;
        $this->book = null;
        
    }

    /**/
    public function handleBook(){
        echo "开始爬取<<$this->book_name>>,扫描书籍信息"."\r\n";

        /*获取书籍首页数据*/
        $bookUrl = $this->book_url.'/';
        $bookHome = $this->getPageData($bookUrl);

        /*获取首页章节信息*/
        $page = $this->getPageList($bookHome);
        $this->chapterList = array_merge($this->chapterList,$page);

        /*获取章节分页信息*/
        $this->getBookLastPage($bookHome);

        /*循环获取剩余页面*/
        if($this->chapterLastPage > 1){
            for ($i=2 ; $i <= $this->chapterLastPage ; $i++) { 
                echo "扫描".$i."页章节信息\r\n";
                $chapterUrl = $this->book_url . '_' . $i . '/';
                $bookInfo = $this->getPageData($chapterUrl);
                $page = $this->getPageList($bookInfo);
                $this->chapterList = array_merge($this->chapterList,$page);
            }
        }

        /*检查该书籍是否已爬取过*/
        $book = $this->bookModel->where('url',$this->book_url)->first();
        $chapterNum = count($this->chapterList);

        if($book){
            if($chapterNum > $book->chapter_num){
                $book->chapter_num = $chapterNum;
                $book->save();
            }else if ($chapterNum == $book->chapter_num && $book->chapter_num == $book->current_page){
                echo "已经是最新章节，无须更新";die;
            }
            $this->book = $book;
            $updateNum = $chapterNum - $this->book->current_page;
            echo "书籍信息扫描完毕,为已入库书籍,需更新".$updateNum."章";
        }else{
            $bookData = [
                'website_id' => 1,
                'book_name' => $this->book_name,
                'url' => $this->book_url,
                'chapter_num' => count($this->chapterList)
            ];
    
            $this->bookModel->fill($bookData);
            $this->bookModel->save();
            $this->book = $this->bookModel;
            echo "书籍信息扫描完毕,已成功入库,共计".$this->book->chapter_num."章\r\n";
        }


        
        echo "    \r\n";
        echo "开始爬取书籍章节信息\r\n";

        $this->handleChapter();

        echo "书籍爬取完毕\r\n"; 
    }

    /**/
    public function handleChapter(){
        $totalNum = $this->book->chapter_num - $this->book->current_page;
        for ($i= ($this->book->current_page ? $this->book->current_page : 0); $i < $this->book->chapter_num; $i++) { 
            $nowNum = $i + 1;
            
            $value = isset($this->chapterList[$i]) ? $this->chapterList[$i] : dd($i);

            echo "开始爬取". $value ."章节数据,进度：" . $nowNum ."/". $totalNum ."\r\n";

            /*获取第一页信息*/
            $url = $this->website . $value;
            $chapterData = $this->getPageData($url);
            $content = $this->getContent($chapterData);

            // /*获取章节名称*/
            // $title = $this->getPageTitle($chapterData);

            /*获取本章最后分页*/
            $this->getLastPage($content);

            echo "扫描章节分页完成：$this->pageLastPage\r\n";

            /*循坏写入获取章节文本*/
            $data = $this->handlePage($value);

            $chapterData = [
                'book_id' => $this->book->id,
                'url' => $value,
                'chapter_order' => $nowNum,
                'content' => $data
            ];
            $chapterModel = $this->getChapterModel();
            $chapterModel->fill($chapterData);
            $res = $chapterModel->save();
            $this->book->current_page++;
            $this->book->save();

            if($res){
                echo $value."章节数据成功入库\r\n";
            }else{
                echo $value."章节数据入库失败\r\n";
            }

        }
    }

    public function getChapterModel(){
        return New \App\Models\ChapterModel();
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

        $id = request()->id;
        $chapterModel = $this->getChapterModel();
        $book = $this->bookModel->find($id);
        $this->book_name = $book->book_name;
        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->book_name .'.txt';
        if(!file_exists($path)){
            $data = $chapterModel->where('book_id',$id)->orderBy('chapter_order')->get();
            foreach ($data as $key => $value) {
                $this->writeTxt($value->content);
            }
        }
        return response()->download($path);
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
            return [];
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
        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->book_name .'.txt';
        $file = fopen($path,'a');
        fwrite($file,$data);
        fclose($file);
    }
}