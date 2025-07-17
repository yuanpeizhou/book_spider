<?php

namespace App\Http\Controllers\Api;

use App\Models\RoseModel;

class ImgsController extends CommonController{

    public $imgModel;

    protected $roseModel;

    public $originUrl;

    public $wordList;

    public function __construct()
    {
        $this->roseModel = New RoseModel();
        $this->imgModel = New \App\Models\ImgModel(); 
        $webModel = New \App\Models\WebsiteModel();
        $this->originUrl =  $webModel->find(3)->url;
    }

    /**
     * 获取图片列表
     */
    public function imgsList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $keyword = request()->keyword;

        $condition = [];
        if($keyword){
            $condition[] = ['rose_name','like',"%$keyword%"];
        }


        $res = $this->roseModel->select('id','rose_name','model_name','count')->where($condition)->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->img_list = $this->imgModel->select('img_local_url')->where('rose_id',$value['id'])->orderBy('img_order')->limit(3)->get();
        }

        return $this->returnApi(200,'ok',$res);
    }

    public function imgsInfo(){
        $id = request()->id;
        $res = $this->imgModel->where('rose_id',$id)->orderBy('img_order')->get();
        return $this->returnApi(200,'ok',$res);
    }
}