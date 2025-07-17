<?php
namespace App\Lib;
use Illuminate\Support\Facades\Log;

/*工具类*/
class Common
{      
    public static function getData($url, $config){
        $curl = curl_init();
        $decode = false;
        $assoc = true;

        // 解析配置
        if (isset($config['decode']) && !$config['decode']) {
            $decode = false;
        }
        if (isset($config['assoc']) && !$config['assoc']) {
            $assoc = false;
        }
        if (isset($config['http']) && $config['http'] == 2) {
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_3);
        }

        // 加载header配置
        $header = [];
        if (isset($config['referer']) && $config['referer']) {
            $header = array_merge($header, ["Referer: ". $url]);
        }
        if (isset($config['header'])) {
            $header = array_merge($header, $config['header']);
        }

        if (count($header) > 0) {
            curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);
        }

        $start_time = time();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_URL, $url);

        $rawData = @curl_exec($curl);
        
        $end_time = time();
        $str = date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒' . "\r\n";
        echo $str;
        Log::notice($str);

        // 判断错误情况
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }
        if(strpos($rawData,'NAME="robots"') !== false){
            var_dump($rawData);
            curl_close($curl);
            return false;
        }
        $is_error = strpos($rawData, "unavailable");
        if (!is_bool($is_error)) {
            curl_close($curl);
            return false;               
        }
        
        $is_error = strpos($rawData, "error code: 5");
        if (!is_bool($is_error)) {
            curl_close($curl);
            return false;               
        }

        $is_error = strpos($rawData, "服务不可用");
        if(!is_bool($is_error)) {
            curl_close($curl);
            return false;               
        }
        
        $is_error = strpos($rawData, "您访问的页面不存在或暂时不能访问");
        if(!is_bool($is_error)) {
            curl_close($curl);
            return false;               
        }

        $is_error = strpos($rawData, "Cloudflare encountered");
        if(!is_bool($is_error)) {
            curl_close($curl);
            return false;               
        }

        $is_error = strpos($rawData, "Just a moment");
        if(!is_bool($is_error)) {
            curl_close($curl);
            return false;
        }
        
        curl_close($curl);
        
        if($decode){
            return json_decode($rawData, $assoc);
        }else{
            return $rawData;
        }

        // curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
        // curl_setopt($curl, CURLOPT_PROXY, "119.23.207.56"); //代理服务器地址  
        // curl_setopt($curl, CURLOPT_PROXYPORT,80); //代理服务器端口
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