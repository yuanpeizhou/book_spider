<?php

namespace App\Http\Controllers\Api;

use App\Models\BookModel;
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
        $collect = request()->collect;

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
        if ($collect) {
            $query = $query->where('collect', '=', $collect);
        }
  
        $res = $query->orderBy("updated_at", "desc")->paginate($limit);

        // foreach ($res as $key => $value) {
        //     $res[$key]->url = $this->originUrl . $value->url;
        // }
        return $this->returnApi(200,'ok',$res);
    }

    public function bookInfo(ChapterModel $chapterModel, BookModel $bookModel){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $id = request()->id;
        $keyword = request()->keyword;

        $book = $bookModel->where('id', '=', $id)->first();
        if (!$book) {
            return $this->returnApi(500, '无效书籍id');
        }

        $query = $chapterModel->select("id","chapter_name")->where('book_id',  '=',$id);
        if($keyword){
            $query = $query->where('source_content','like',"%$keyword%");
        }
        $chapter_list = $query->orderBy('id')->paginate($limit);
        return $this->returnApi(200,'ok',['book' => $book, 'chapter_list' => $chapter_list]);
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
        $res->content = str_replace('ｗｗｗ．ｄｉｙｉｂａｎｚｈｕ．ｎｅｔ', "", $res->content);
        $res->content = str_replace('www.diyibanzhu.net', "", $res->content);

        return $this->returnApi(200,'ok',$res);
    }


    /**
     * 书籍导出
     */
    public function bookExport(){
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
}