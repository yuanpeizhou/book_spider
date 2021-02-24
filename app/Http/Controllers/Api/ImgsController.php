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
        $keyword = request()->keyword;

        $imgs_model = New \App\Models\FaImgsModel();


        $condition[] = ['is_spider','=',1];

        if($keyword){
            $condition[] = ['name','like',"%$keyword%"];
        }


        $res = $this->model->select('id','name','index','number')->where($condition)->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->img_list = $imgs_model->select('local_url')->where('img_id',$value['id'])->orderBy('order')->limit(3)->get();
        }

        return $this->returnApi(200,'ok',$res);
    }

    public function imgsInfo(){
        $imgs_model = New \App\Models\FaImgsModel();

        $id = request()->id;


        $res = $imgs_model->where('img_id',$id)->orderBy('order','desc')->get();

        return $this->returnApi(200,'ok',$res);
    }
}