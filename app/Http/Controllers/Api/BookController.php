<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class BookController extends CommonController{

    public $bookModel;

    public function __construct()
    {
        $this->bookModel = New \App\Models\BookModel();    
    }

    /**
     * 获取书记列表
     */
    public function bookList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $keyword = request()->keyword;

        $condition[] = ['id','>',0];

        if($keyword){
            $condition[] = ['book_name','like',"%$keyword%"];
        }

        $res = $this->bookModel->where($condition)->paginate($limit);


        return $this->returnApi(200,'ok',$res);
    }
}