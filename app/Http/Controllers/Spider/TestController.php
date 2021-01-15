<?php

namespace App\Http\Controllers\Spider;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
/**
 * 落花有声网
 */
class TestController {

    protected $web_url = 'http://v.luohuays.me/playbook/?931-0-0.html';

    public function test(){
        $data = $this->httpRequest($this->web_url);
        echo $data;
    }
    
        /**
     * 爬取请求(最多重复请求三次)
     * 
     */
    public function httpRequest($url){
        ini_set('memory_limit', '256M');
        $res = $this->httpGet($url);

        if(!$res){
            echo "页面请求失败，延时30秒尝试第二次请求\r\n";
            sleep(30);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败，延时60秒尝试第三次请求\r\n";
            sleep(60);
            $res = $this->httpGet($url);
        }

        if(!$res){
            echo "页面请求失败,跳过该页面\r\n";
            $this->set_img_is_true = false;
            return false;
        }

        $code = mb_detect_encoding($res, array('GB2312','UTF-8', 'GBK'));

        if($code == 'EUC-CN' || $code == 'CP936'){
            $res = mb_convert_encoding($res, 'utf-8', 'gbk');
        }
        
        return $res;
    }

    /**
     * get请求
     */
    public function httpGet($url){

        // $url = "http://www.cdbottle.com/";

        $start_time = time();

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        /**不验证ssl */
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        /**文本流返回 */
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        /**跟随跳转 */
        curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);

        /**超时 */
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        //useragent
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"); 

        /** REFERER(伪造来路)*/
        curl_setopt ($curl, CURLOPT_REFERER, "http://v.luohuays.me/");  

        $rawData = @curl_exec($curl);

        $end_time = time();

        echo date("Y-m-d H:i:s")."：抓取时间:" . ($end_time - $start_time)."s\r\n";

        Log::notice(date("Y-m-d H:i:s").'：抓取页面,网址:'.$url.',耗时'. ($end_time - $start_time) . '秒');

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return false;
        }

        // curl_close($curl);
        // if(mb_strpos($rawData,'404 - ') !== false){
        //     echo "未成功抓取图片";
        //     return false;
        // }

        return $rawData;
    }

}

