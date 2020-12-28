<?php

namespace App\Http\Controllers\Api;

class ImgsController extends CommonController{

    public $bookModel;

    public $originUrl;

    public $wordList;

    public function __construct()
    {
        $this->model = New \App\Models\FaImgModel(); 
        
        $webModel = New \App\Models\WebsiteModel();
        $this->originUrl =  $webModel->find(1)->url;
    }

    /**
     * 获取图片列表
     */
    public function imgsList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;


        $condition[] = ['is_spider','=',1];


        $res = $this->model->where($condition)->paginate($limit);

        return $this->returnApi(200,'ok',$res);
    }

    public function imgsInfo(){
        $imgs_model = New \App\Models\FaImgsModel();

        $id = request()->id;


        $res = $imgs_model->where('img_id',$id)->orderBy('order')->get();

        return $this->returnApi(200,'ok',$res);
    }
}