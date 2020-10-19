<?php

namespace App\Http\Controllers\Api;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
// use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class JwtController extends CommonController{

    public function index(){
        // $data = '{"user_id": "1213"}';
        $data = array('uid' => '002800001');
        $secret = 'e826b503-0d9b-453a-8603-7b6acbc14fdc';
        $day = 7;
        $builder = (new Builder())
        ->issuedAt(time())
        ->expiresAt(strtotime("+$day day"))
        ->withClaim("data",json_encode($data));

        $token = $builder->getToken(new Sha256(),new Key($secret));
        var_dump((string)$token);exit;

        // return $token;\
        // echo $token;
    }
}