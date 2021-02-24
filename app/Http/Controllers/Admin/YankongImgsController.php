<?php

namespace App\Http\Controllers\Admin;

class YankongImgsController extends CommonController{

    public function __construct()
    {
        $this->img_model = New \App\Models\YankongImgModel(); 
        $this->imgs_model = New \App\Models\YankongImgsModel(); 
    }

    /**
     * 获取图片列表
     */
    public function imgsList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $keyword = request()->keyword;


        $condition[] = ['is_spider','=',1];

        if($keyword){
            $condition[] = ['name','like',"%$keyword%"];
        }


        $res = $this->img_model->select('id','name','index','number')->where($condition)->paginate($limit);

        foreach ($res as $key => $value) {
            $res[$key]->img_list = $this->imgs_model->select('local_url')->where('img_id',$value['id'])->orderBy('order')->limit(3)->get();
        }

        return $this->returnApi(200,'ok',$res);
    }

    public function imgsInfo(){

        $id = request()->id;

        $res = $this->imgs_model->where('img_id',$id)->orderBy('order')->get();

        return $this->returnApi(200,'ok',$res);
    }
}