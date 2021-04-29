<?php
namespace App\Http\Controllers\Api;

class TinyController
{  
    public function img(){
        $this->test();
    }

    public function test(){
        $input_dir = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'admin_img';

        echo "开始扫描图片\r\n";
        $count = 0;
        $dir1List = scandir($input_dir);
        foreach ($dir1List as $dir1_key => $dir1_value) {

            $dir1_path = $input_dir . DIRECTORY_SEPARATOR . $dir1_value;

            
            if($dir1_value != '.' && $dir1_value != '..' && is_dir($dir1_path)){
                
                $dir2List = scandir($dir1_path);

                foreach ($dir2List as $dir2_key => $dir2_value) {
                    
                    if($dir2_value != '.' && $dir2_value != '..'){
                        $img_path = $dir1_path . DIRECTORY_SEPARATOR . $dir2_value;
                        $is_limit = $this->handleimg($img_path,$dir1_path);
                        if(!$is_limit){
                            echo "次数用尽";die;
                        }
                    }
                    
                }
            }
        }
    }

    public function handleimg($path,$dir){
        $to_dir = str_replace('admin_img','admin_img1',$path);
        $dir = str_replace('admin_img','admin_img1',$dir);

        if(file_exists($to_dir)){
            $compressionsThisMonth = \Tinify\compressionCount();

            echo "该图片已处理,已用".  $compressionsThisMonth . "额度\r\n\r\n";

            if($compressionsThisMonth > 450){
                return false;
            }else{
                return true;
            }
        }

        if(!is_dir($dir)){
            mkdir ($dir,0777,true);
        }

        echo "开始压缩图片：". $path . "\r\n";
        \Tinify\setKey("FRcr66LMPz29zSlxR0LX6Ryh7X0nTbrv");

        $source = \Tinify\fromFile($path);
        $source->toFile($to_dir);

        $compressionsThisMonth = \Tinify\compressionCount();

        echo $path . "压缩完成,已用".  $compressionsThisMonth . "额度\r\n\r\n";

        if($compressionsThisMonth > 499){
            return false;
        }else{
            return true;
        }
    }
}