<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class VideoController extends CommonController{

    public function videoList(){
        $url = 'http://hhhhapi.111eee.net/live/live/xnmsf.html';
        $lib = New \App\Lib\Common();
        $res = $lib->getData($url,false);

        return $this->returnApi(200,'ok',$res);
    }
}