<?php
namespace App\Http\Controllers\Api;
use App\Exceptions\ParamsErrorException;

/*图片爬取处理*/
class WordController
{   
    public function __construct()
    {
        $this->model = New \App\Models\WordModel();
        $this->common = New \App\Lib\Common();
    }

    /*根据标签处理*/
    public function handle($tag){
        $website = 'https://www.diyibanzhu4.pro/toimg/data';
        echo "开始检索".$tag."目录\r\n";

        for ($i=1; $i <= 500; $i++) { 
            $file_name = $tag.$i.'.png';
            echo '获取'.$file_name.'资源   ';
            $file = $this->common->getData($website.'/'.$file_name,false);
            if($this->checkRes($file)){
                $path =$this->saveImg($file_name,$file);

                $res = $this->model->where('tag',$file_name)->first();
                if(!$res){
                    $data = [
                        'url' => $website,
                        'tag' => $file_name,
                        'local_url' => $path,
                        'created_at' => date("Y-m-d H:i:s",time())
                    ];
                    $res = $this->model->insert($data);

                    if($res){
                        echo '录入'.$file_name."\r\n";
                    }else{
                        echo '录入'.$file_name."失败\r\n";
                        exit;
                    }
                }else{
                    echo $file_name."已录入跳过\r\n";
                }
            }else{
                echo $file_name."无效资源\r\n";
            }
        }

        echo "检索完成\r\n";
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
        // echo '接收文件'.$fileName;
        return $savePath;
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

