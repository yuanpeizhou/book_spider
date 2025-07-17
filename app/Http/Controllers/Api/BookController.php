<?php

namespace App\Http\Controllers\Api;

use App\Models\ChapterModel;
use Illuminate\Support\Facades\DB;

class BookController extends CommonController{

    public $bookModel;

    public $originUrl;

    public $wordList;

    public function __construct()
    {
        $this->bookModel = New \App\Models\BookModel(); 
        
        // $webModel = New \App\Models\WebsiteModel();
        // $this->originUrl =  $webModel->find(1)->url;
    }

    /**
     * 获取书籍列表
     */
    public function bookList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $website_id = request()->website_id ? intval(request()->website_id) : 1;
        $keyword = request()->keyword;

        $query = $this->bookModel;

        if($keyword){
            $query = $query->where(function($query) use ($keyword) {
                $query->where('book_name','like',"%$keyword%")
                    ->orWhere('author_name','like',"%$keyword%");
            });
        }
        if ($website_id) {
            $query = $query->where('website_id', '=', $website_id);
        }
        // ->orderBy("last_update_date", "desc")
        $res = $query->orderBy("id")->paginate($limit);

        // foreach ($res as $key => $value) {
        //     $res[$key]->url = $this->originUrl . $value->url;
        // }
        return $this->returnApi(200,'ok',$res);
    }

    public function bookInfo(ChapterModel $chapterModel){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $id = request()->id;
        $keyword = request()->keyword;

        $query = $chapterModel->select("id","chapter_name")->where('book_id',  '=',$id);
        if($keyword){
            $query = $query->where('source_content','like',"%$keyword%");
        }
        $res = $query->orderBy('id')->paginate($limit);
        return $this->returnApi(200,'ok',$res);
    }

    public function chapterInfo(ChapterModel $chapterModel) {
        $id = request()->id;
        $res = $chapterModel->where('id',  '=',$id)->first();

        // 检索上一章
        $last = $chapterModel->where("book_id", '=', $res->book_id)
            ->where('chapter_order',  '=',$res->chapter_order - 1)->first();

        // 检索下一章
        $next = $chapterModel->where("book_id", '=', $res->book_id)
            ->where('chapter_order',  '=',$res->chapter_order + 1)->first();

        $res->next_id = 0;
        if ($next) {
            $res->next_id = $next->id;
        }

        $res->last_id = 0;
        if ($last) {
            $res->last_id = $last->id;
        }

        
        $res->content = str_replace("\t\t\t\t", "", $res->content);
        $res->content = str_replace("    　　", "　　", $res->content);
        $res->content = str_replace("\r\n\r\n", "\r\n", $res->content);
        $res->content = str_replace("&nsp;", "", $res->content);
        $res->content = str_replace("&nbsp;&nbsp;&nbsp;&nbsp;", "\r\n", $res->content);

        return $this->returnApi(200,'ok',$res);
    }

    /**
     * 单章节爬取
     */
    public function chapterSipder(){
        set_time_limit(0);

        $id = request()->id;

        if(!$id){
            return $this->returnApi(201,'参数传递错误','');
        }
        $chapterModel = New \App\Models\ChapterModel();
        $chapter = $chapterModel->find($id);

        if(!$chapter){
            return $this->returnApi(201,'参数传递错误','');
        }

        $source_content = $this->getChapterPageData($chapter->url);

        if(!$source_content){
            return $this->returnApi(202,'未爬取到数据','');
        }

        $chapter->source_content = $source_content;
        $chapter->is_spider = 1;

        $res = $chapter->save();

        if(!$res){
            return $this->returnApi(202,'保存失败','');
        }


        return $this->returnApi(200,'ok',$res);
    }

    public function getChapterPageData($chapterHomeUrl){
        $lastPage = $this->handleChapterPageHome($chapterHomeUrl);
        return $this->handleChapterPage($chapterHomeUrl,$lastPage);
    }

    /**处理章节第一页数据 */
    public function handleChapterPageHome($url){
        /*获取章节第一页信息*/
        $url = $this->originUrl . $url;

        $pageHomeData = $this->getPageData($url);

        $content = $this->getContent($pageHomeData);

        /*获取本章最后分页*/
        $lastPage = $this->getChapterLastPage($content);

        // echo "扫描章节分页完成,共"."$lastPage"."页：\r\n";

        return $lastPage;
    }

    /**循环处理章节页面 */
    public function handleChapterPage($chapterHomeUrl,$lastPage){

        $chapterArray = explode('.',$chapterHomeUrl);
        $res = '';
        for ($i=1; $i <= $lastPage; $i++) { 
            $chapterUrl = $this->originUrl . $chapterArray[0] . '_' .$i . '.html';
            $pageData = $this->getPageData($chapterUrl);
            $res = $res.$pageData;
            // echo "第".$i."页数据爬取完成\r\n";
        }
        // echo "章节数据爬取完成\r\n\r\n";
        return $res;
    }

    /**
     * 书籍导出
     */
    public function bookExport(){
        // var_dump('123');
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $id = request()->id;

        if(!$id){
            return $this->returnApi(201,'参数传递错误','');
        }

        $book = $this->bookModel->find($id);

        if(!$book){
            return $this->returnApi(201,'参数传递错误','');
        }

        $book_name = $book->book_name;

        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'book' . DIRECTORY_SEPARATOR . $book_name .'.txt';
        if(!file_exists($path)){
            $chapterModel = New \App\Models\ChapterModel();
            $data = $chapterModel->where('book_id',$id)->orderBy('chapter_order')->get();
            foreach ($data as $key => $value) {
                $this->writeTxt($value->content,$path);
            }
        }
        return response()->download($path);
    }

    /*写入文档*/
    public function writeTxt($data,$path){
        $path = iconv("UTF-8","GBK//IGNORE",$path);
        $file = fopen($path,'a');
        fwrite($file,$data);
        fclose($file);
    }

    /**
     * 处理书籍
     */
    public function handleBook(){

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $id = request()->id;

        if(!$id){
            return $this->returnApi(201,'参数传递错误','');
        }

        $book = $this->bookModel->find($id);

        if(!$book){
            return $this->returnApi(201,'参数传递错误','');
        }

        /**字典 */
        $wordModel = New \App\Models\WordModel();
        $this->wordList = $wordModel->select('origin_url','word')->get()->toArray(); 

        $chapterModel = New \App\Models\ChapterModel();
        $chapterList = $chapterModel->where('book_id',$id)->get();
        
        foreach ($chapterList as $key => $value) {
            // if(!$value->content){
            //     $source_content = $this->getChapterContent($value->source_content);
            
            //     $content = '';
            //     foreach ($source_content[0] as $source_key => $source_value) {
            //         $content = $content . $this->handleSourceContent($source_value);
            //     }
    
            //     $value->content = $content;
            //     $value->save();
            // }

            $source_content = $this->getChapterContent($value->source_content);
            
            $content = '';
            foreach ($source_content[0] as $source_key => $source_value) {
                $content = $content . $this->handleSourceContent($source_value);
            }

            $value->content = $content;
            $value->save();
        }

        return $this->returnApi(200,'ok','');
    }

    public function handleChapter(){
        $chapterModel = New \App\Models\ChapterModel();
        
        $id = request()->id;

        $chapter = $chapterModel->find($id);

        if(!$chapter){
            return $this->returnApi(201,'参数传递错误','');
        }

        $source_content = $this->getChapterContent($chapter->source_content);

        if(!$source_content || !isset($source_content)){
            return $this->returnApi(202,'数据解析失败，检查源数据','');
        }

        $wordModel = New \App\Models\WordModel();
        $this->wordList = $wordModel->select('origin_url','word')->get()->toArray(); 

        $res = '';
        foreach ($source_content[0] as $key => $value) {
            $res = $res . $this->handleSourceContent($value);
        }
        
        $chapter->content = $res;

        // dd($res);
        $res = $chapter->save();

        if($res){
            return $this->returnApi(200,'ok','');
        }else{
            return $this->returnApi(202,'章节内容解析失败','');
        }
    }

    public function handleSourceContent($str){
        $txt = $this->getTxt($str);
        $txt = str_replace('&nbsp;',"",$txt);
        $txt = str_replace('<noscript>',"",$txt);
        $txt = str_replace('</noscript>',"",$txt);
        $txt = str_replace('<br />',"\r\n",$txt);
        $txt = str_replace('<p>',"",$txt);
        $txt = str_replace('</p>',"",$txt);
        $txt = str_replace("本站地址随时可能失效，记住发布邮箱：ｄｉｙｉｂａｎｚｈｕ＠ｇｍａｉｌ．Ｃ０Ｍ\r\n\r\n","",$txt);
        $txt = str_replace("本站地址随时可能失效，记住发布页wｗw．０１Ｂz．ｎＥt\r\n\r\n","",$txt);
        $txt = str_replace("`久`地`址`２ｕ２ｕ２ｕ．Ｃ〇Ｍ\r\n\r\n","",$txt);
        $txt = str_replace("&#x2193;&#x8BB0;&#x4F4F;&#x53D1;&#x5E03;&#x9875;&#x2193;\r\n\r\n","",$txt);
        $txt = str_replace("&#xFF12;&#xFF48;&#xFF12;&#xFF48;&#xFF12;&#xFF48;&#xFF0E;&#xFF43;&#xFF4F;&#xFF4D;\r\n\r\n","",$txt);
        $txt = str_replace("本文dybz8.pw免费首发\r\n","",$txt);
        
        $imgArray = $this->getImgList($txt);

        foreach ($imgArray as $key => $value) {

            $word = $this->handleWord($value);

            if($word){
                $txt = str_replace($value,$word,$txt);
            }else{
                $txt = str_replace($value,"(未查询到字典)",$txt);
            }
        }

        return $txt;
    }

    /*图片转化文字*/
    public function handleWord($img){
        
        $word = '';

        preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$img,$match);

        // $srcArray = explode("/",$match[1]);
        // $fileName = array_pop($srcArray);

        foreach ($this->wordList as $key => $value) {
            if($match[1] == $value['origin_url']){
                $word = $value['word'];
            }
        }

        if($word){
            return $word;
        }else{
            return false;
        }
    }

    /*获取页面内容*/
    public function getChapterContent($str){
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        if(preg_match_all($regexContent, $str, $matches)){

            return $matches;

        }else{
            return '';
        }
    }
}