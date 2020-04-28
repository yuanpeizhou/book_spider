<?php
namespace App\Http\Controllers\Api;

class SpiderController
{   

    public function __construct()
    {
        $this->model = New \App\Models\WordModel();
        $this->common = New \App\Lib\Common();
    }

    public function test(){
        // $this->getTestData();
        $url = 'https://www.diyibanzhu4.pro/15/15549/327077.html';

        $res = $this->common->getData($url,false);

        $res = mb_convert_encoding($res, 'utf-8', 'gb2312');
        $regexContent="/<div class=\"page-content font-large\".*?>.*?<\/div>/ism"; 
        $regexImg = "/<img.*?>/i";
        if(preg_match_all($regexContent, $res, $matches)){ 
            
            $res = str_replace('&nbsp;',"\r\n",$matches[0][0]);
            $res = str_replace('<noscript>',"",$res);
            $res = str_replace('</noscript>',"",$res);
            $res = str_replace('<br />',"",$res);
            $res = str_replace('<p>',"",$res);
            $res = str_replace('</p>',"",$res);
            preg_match_all($regexImg,$res,$resImg);
            foreach ($resImg[0] as $key => $value) {

                $word = $this->handleWord($value);

                if($word){
                    $res = str_replace($value,$word,$res);
                }else{
                    $res = str_replace($value,"",$res);
                }
                
            }
            $this->writeTxt($res);
        }else{ 
            dd('没匹配到');
        }
        

    }

    public function getTestData(){
        $str = "<img src='/toimg/data/q22.png' />的古玲珑】月上三杆，彩虹城的迎归大会已经进入尾声，大多数人都已经离去，只剩下少许人在清理遗留下的现场，唯有陈林居士与古玲珑依旧留在这里<img src='/toimg/data/q22.png' />";
        $regexImg = "/<img.*?>/i";
        if(preg_match_all($regexImg,$str,$resImg)){
            dd($resImg);
        }else{
            dd("没匹配到");
        }
    }

    public function handleWord($img){

        if(strlen($img) > 40){
            return false;
        }
        $wordModel = New \App\Models\WordModel();
        $exg = preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$img,$match);

        $srcArray = explode("/",$match[1]);
        $fileName = array_pop($srcArray);
        $result = $wordModel->where('tag',$fileName)->first();

        if($result){
            return $result->word;
        }else{
            return false;
        }
    }

    public function writeTxt($data){
        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'test.txt';
        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
    }
}