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

    /**
     * 随机生成token
     * @param user  object 用户对象
     */
    public function setToken($user){
        $token = md5(substr(time(),0,8).$user->id.substr(str_shuffle(config('private.token_rand_str')),0,6));
        $expire_time = date("Y-m-d H:i:s",(time() + config('token_refresh_time')));

        return ['token' => $token , 'expire_time' => $expire_time];
    }

}

