<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class CommonController extends Controller{

    public function __construct()
    {
        
    }

    /**
     * api返回数据
     */
    public function returnApi($code = 200 , $msg = 'ok', $data = null){
        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

}

