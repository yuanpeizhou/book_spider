<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookController extends CommonController{

    public $bookModel;

    public $originUrl;

    public $wordList;

    public function __construct()
    {
        $this->bookModel = New \App\Models\BookModel(); 
        
        $webModel = New \App\Models\WebsiteModel();
        $this->originUrl =  $webModel->find(1)->url;
    }

    /**
     * 获取书记列表
     */
    public function bookList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $book_name = request()->book_name;
        $author_name = request()->author_name;

        $condition[] = ['id','>',0];

        if($book_name){
            $condition[] = ['book_name','like',"%$book_name%"];
        }

        if($author_name){
            $condition[] = ['author_name','like',"%$author_name%"];
        }

        $res = $this->bookModel->where($condition)->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->url = $this->originUrl . $value->url;
        }
        return $this->returnApi(200,'ok',$res);
    }

    public function bookInfo(){
        $chapterModel = New \App\Models\ChapterModel();

        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $id = request()->id;
        $keyword = request()->keyword;

        $condition[] = ['book_id','=',$id];

        if($keyword){
            $condition[] = ['source_content','like',"%$keyword%"];
        }

        $res = $chapterModel->where($condition)->orderBy('chapter_order')->paginate($limit);

        return $this->returnApi(200,'ok',$res);
    }

    public function bookExport(){

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
        $file = fopen($path,'a');
        fwrite($file,$data);
        fclose($file);
    }

    /**
     * 处理书籍
     */
    public function handleBook(){

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