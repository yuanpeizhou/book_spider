<?php
namespace App\Lib;

/*工具类*/
class Common
{      

    /*get请求*/
    public static function getData($url, $decode = true, $assoc = true, $ssl = false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $rawData = curl_exec($curl);
        curl_close($curl);

        if($decode){
            return json_decode($rawData, $assoc);
        }else{
            return $rawData;
        }
    }

    /*post请求*/
    public static function postData($url, $data, $decode = true, $assoc = true, $ssl = false, $postJSON = true){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if($postJSON){
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonData)
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $rawData = curl_exec($curl);
        curl_close($curl);

        if($decode){
            return json_decode($rawData, $assoc);
        }else{
            return $rawData;
        }
    }


}