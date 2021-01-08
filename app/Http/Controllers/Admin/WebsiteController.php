<?php

namespace App\Http\Controllers\Admin;

// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DB;
/**
 * 24FA
 */
class WebsiteController extends CommonController{

    /**
     * 网站模型model
     */
    protected $model;

    public function __construct()
    {
        $this->model = New \App\Models\WebsiteModel();
    }

    /**
     * 网站列表
     */
    public function websiteList(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $website_name = request()->website_name;

        $condition[] = ['id','>',0];

        if($website_name){
            $condition[] = ['website','like',"%$website_name%"];
        }

        $res = $this->model->where($condition)->orderBy('created_at','desc')->paginate($limit);

        if($res->isEmpty()){
            return $this->returnApi(203,'暂无数据');
        }

        foreach ($res as $key => $value) {
            $res[$key]->index = $key + 1 + ($page-1)*$limit;
        }

        return $this->returnApi(200,'ok',$res);
    }
}