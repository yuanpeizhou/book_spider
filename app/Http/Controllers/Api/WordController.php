<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;

/*图片爬取处理*/
class WordController
{   
    public function __construct()
    {
        $this->model = 1;
        $this->common = New \App\Lib\Common();
    }
    /*循环处理图片*/
    public function handle(){
        // return $this->saveImg('123');
        $website = request()->website;
        $letterList = $this->getLetter();

        foreach ($letterList as $key => $value) {
            for ($i=1; $i < 50; $i++) { 
                $file_name = $value.$i.'.png';
                $file = $this->common->getData($website.'/'.$file_name,false);
                if($this->checkRes($file)){
                    $this->saveImg($file_name,$file);
                }
            }
            var_dump('ok');exit;
        }
    }

    /*检查是否返回了图片*/
    public function checkRes($data){
        if(strpos($data,'<title>404</title>') === false){
            return true;
        }else{
            return false;
        }
        
    }

    /*保存图片*/
    public function saveImg($fileName,$data = null){
        $savePath = 'public' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $fileName;
        $path = base_path() . DIRECTORY_SEPARATOR . $savePath;
        $file = fopen($path,'w');
        fwrite($file,$data);
        fclose($file);
        // return $path;
        echo '接收文件'.$fileName;
    }

    public function getLetter(){
        $res = [];
        for($i=97; $i<122; $i++)
        {
            $res[] = chr($i);
        }
        return $res;
    }

    // public function getNum(){

    // }
}

