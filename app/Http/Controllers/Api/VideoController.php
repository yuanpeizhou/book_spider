<?php

namespace App\Http\Controllers\Api;

use App\Models\VideoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends CommonController{

    protected $videoModel;

    public function __construct()
    {
        $this->videoModel = New VideoModel();
    }

    public function videoList(Request $request){
        $websiteId = $request->get('website_id');
        $keyword = $request->get('keyword');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        if (!$websiteId) {
            return $this->returnApi(500, '无效网站参数');
        }

        $filter = [['status', '=', 2]];
        if ($keyword) {
            $filter[] = ['name', "like", "%$keyword%"];
        }

        $list = $this->videoModel->where($filter)->paginate($limit);

        return $this->returnApi(200,'ok',$list);
    }
}