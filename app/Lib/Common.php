<?php
namespace App\Lib;
use Illuminate\Support\Facades\Log;

/*工具类*/
class Common
{      
    public static function getData($url, $config){
        $curl = curl_init();

        if (isset($config['proxy']) && $config['proxy']) {
            curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
            curl_setopt($curl, CURLOPT_PROXY, $config['proxy_http']); //代理服务器地址  
            curl_setopt($curl, CURLOPT_PROXYPORT,$config['proxy_port']); //代理服务器端口
        }

        if (isset($config['http']) && $config['http'] == 2) {
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_3);
        }

        // 加载header配置
        if (isset($config['header']) && count($config['header']) > 0) {
            curl_setopt ($curl, CURLOPT_HTTPHEADER, $config['header']);
        }

        $start_time = time();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_URL, $url);
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $rawData = @curl_exec($curl);

        $end_time = time();
        $str = date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒';
        echo $str . "\r\n";
        Log::notice($str);

        // 判断错误情况
        $error_log = curl_errno($curl);
        if ($error_log) {
            curl_close($curl);
            return ["res" => false, "data" => $error_log];
        }

        // 判断200返回情况下的错误
        if (isset($config['error_tag']) && count($config['error_tag']) > 0) {
            foreach ($config['error_tag'] as $error_tag) {
                if(strpos($rawData,$error_tag) !== false){
                    return ["res" => false, "data" => $rawData];
                }
            }
        }
        
        return  ["res" => true, "data" => $rawData];
    }

    // /*post请求*/
    // public static function postData($url, $data, $decode = true, $assoc = true, $ssl = false, $postJSON = true){
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
    //     curl_setopt($curl, CURLOPT_HEADER, 0);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($curl, CURLOPT_POST, 1);
    //     if($postJSON){
    //         $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, [
    //             'Content-Type: application/json; charset=utf-8',
    //             'Content-Length: ' . strlen($jsonData)
    //         ]);
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
    //     }else{
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //     }

    //     $rawData = curl_exec($curl);
    //     curl_close($curl);

    //     if($decode){
    //         return json_decode($rawData, $assoc);
    //     }else{
    //         return $rawData;
    //     }
    // }
}