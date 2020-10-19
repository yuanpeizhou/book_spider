<?php
namespace App\Lib;
use Illuminate\Support\Facades\Log;

/*工具类*/
class Common
{      

    /*get请求*/
    public static function getData($url, $decode = true, $assoc = true, $ssl = false){

        // $url = 'http://www.cdbottle.com/';

        $start_time = time();

        $curl = curl_init();

        // curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式

        // curl_setopt($curl, CURLOPT_PROXY, "119.23.207.56"); //代理服务器地址  

        // curl_setopt($curl, CURLOPT_PROXYPORT,80); //代理服务器端口


        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);

        curl_setopt($curl, CURLOPT_URL, $url);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36");
        
        /**随机ip */
        $cip = '123.125.68.'.mt_rand(0,254);
        $xip = '125.90.88.'.mt_rand(0,254);
        $header = array(
        'CLIENT-IP:'.$cip,
        'X-FORWARDED-FOR:'.$xip,
        );

        curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);

        $rawData = @curl_exec($curl);

        $end_time = time();

        echo date("Y-m-d H:i:s")."：抓取时间:" . ($end_time - $start_time)."s\r\n";

        Log::notice(date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒');

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

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