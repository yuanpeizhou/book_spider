<?php

namespace App\Http\Controllers\Admin;
// use Illuminate\Support\Facades\DB;

class BookController extends CommonController{

    /**网站model */
    public $web_url;

    /**书籍model */
    public $book_model;

    /**章节model */
    public $chapter_model;

    public function __construct()
    {
        $this->book_model = New \App\Models\BookModel(); 

        $this->chapter_model = New \App\Models\ChapterModel();
        
        $web_model = New \App\Models\WebsiteModel();

        $this->web_url =  $web_model->find(1)->url;
    }

    /**
     * 获取书籍列表
     * @param page int 页数
     * @param limit int 分页
     * @param book_name string 书籍名称
     * @param author_name string 作者名称
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

        $res = $this->book_model->where($condition)->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->url = $this->web_url . $value->url;
        }
        return $this->returnApi(200,'ok',$res);
    }

    /**
     * @param id int 书籍id
     * @param keyword string 章节搜索关键词
     */
    public function bookInfo(){

        $id = request()->id;
        $keyword = request()->keyword;

        $book_info = $this->book_model->find($id);

        if(!$book_info){
            return $this->returnApi(200,'参数传递作物');
        }

        $condition[] = ['book_id','=',$id];

        if($keyword){
            $condition[] = ['source_content','like',"%$keyword%"];
        }

        $book_info['chapter_list'] = $this->chapter_model->where($condition)->orderBy('chapter_order')->paginate(10);

        return $this->returnApi(200,'ok',$book_info);
    }

    /**
     * 获取书籍爬取链接
     * @param id int 书籍id
     */
    public function bookSpider(){
        $id = request()->id;

        return $this->returnApi(200,'ok',['command' => "php artisan bookChapterHandle $id $id"]);
    }

    /**
     * 单章节爬取
     * @param id int 章节id
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

        $id = request()->id;

        if(!$id){
            return $this->returnApi(201,'参数传递错误','');
        }

        $book = $this->book_model->find($id);

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

        $id = request()->id;

        if(!$id){
            return $this->returnApi(201,'参数传递错误','');
        }

        $book = $this->book_model->find($id);

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