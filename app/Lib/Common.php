<?php
namespace App\Lib;

/*工具类*/
class Common
{      

    /*get请求*/
    public static function getData($url, $decode = true, $assoc = true, $ssl = false){


        $useragent = array(
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)',
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1',
            'Opera/9.27 (Windows NT 5.2; U; zh-cn)',
            'Opera/8.0 (Macintosh; PPC Mac OS X; U; en)',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13 ',
            'Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13'
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // 设置iP和useragent
        // curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36");

        //添加这两行
        // curl_setopt($curl, CURLOPT_ENCODING, "gzip"); 
        // curl_setopt($curl, CURLOPT_HTTPHEADER, ["Origin: $url"]);
        curl_setopt($curl,CURLOPT_COOKIE,'__cfduid=d3c098b882ff2c56691bf1de98945b02b1587967724; UM_distinctid=171ba40e4a2207-0bf41e6060352f-3a365305-1fa400-171ba40e4a35da; PHPSESSID=n412grk6ft6n6uqt16ehrv5q56; CNZZDATA1277837046=381221682-1587964842-https%253A%252F%252Fwww.diyibanzhu4.pro%252F%7C1588133615; CNZZDATA1275907010=1454170211-1587963096-null%7C1588146717');

        $rawData = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
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