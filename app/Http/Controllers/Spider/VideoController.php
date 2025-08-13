<?php

namespace App\Http\Controllers\Spider;
use App\Http\Controllers\Spider\CommonController;
use App\Models\VideoModel;
use App\Models\WebsiteModel;

/**
 * 视频抓取
 */
class VideoController extends CommonController {

    protected $websiteModel;

    protected $videoModel;
    protected $website;

    protected $parseRule;

    protected $config;

    public function __construct()
    {
        $this->websiteModel = New WebsiteModel();
        $this->videoModel = New VideoModel();
    }

    public function handle($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website || $this->website->type != WebsiteModel::VIDEO) {
            die('错误的网站参数');
        }

        $this->parseRule = $this->website->rule['video'];
        $url = $this->website->url . $this->parseRule['url'];
        if ($this->parseRule['page_url_model'] == 'relative') {
            $this->website->url = $this->website->url . $this->parseRule['url'];
        }

        echo "开始爬取视频信息\r\n";
        $pageData = $this->getPageData($url, $this->website);
        switch ($this->parseRule['page_model']) {
            case 'all':
                $pageStart = isset($this->parseRule['page_start']) ? $this->parseRule['page_start'] : 1;
                $this->spiderPageByAll($this->website, $pageData, $this->parseRule['page_rule'], $pageStart,[$this, 'scanVideo']);
                break;
            case 'next':
                $this->spiderPageByNext($this->website, $pageData, $this->parseRule['page_rule'], [$this, 'scanVideo']);
                break;
            default:
                die("未知的抓取模式");
        }

        echo "爬取完毕\r\n";
    }

    public function scanVideo($pageData) {
        $videoList = $this->parseRule($pageData, $this->parseRule['content_rule']);
        foreach ($videoList as $video) {
            $this->saveVideo($video);
        }
    }

    public function saveVideo($videoData) {
        $video = $this->videoModel->where("website_id", '=', $this->website->id)
            ->where("web_url", "=", $videoData['web_url'])->first();
        if (!$video) {
            $videoData['website_id'] = $this->website->id;
            $videoData['created_at'] = date("Y-m-d H:i:s");
            $this->videoModel->insert($videoData);
            echo "视频《" .  $videoData['name'] . "》入库完毕\r\n";
        } else {
            // echo "套图《" . $roseData['rose_name'] . "》已在库中，停止爬取\r\n";
            // exit;
            echo "视频《" . $videoData['name'] . "》已在库中，跳过\r\n";
        }
    }

    public function handle1($websiteId) {
        $this->website = WebsiteModel::find($websiteId);
        if (!$this->website || $this->website->type != WebsiteModel::VIDEO) {
            die('错误的网站参数');
        }

        $this->config = $this->website->config;

        while(true) {
            $videoList = $this->videoModel->where("website_id", '=', $websiteId)
                ->where("name", "like", "%NANA%")
                ->where('status', '=', 1)->limit(10)->get();

            if (count($videoList) == 0) {
                break;
            }
            foreach ($videoList as $video) {
                $this->spiderVideo($video);
            }
            
        }
    }

    public function handle2($websiteId) {
        $this->website = WebsiteModel::find($websiteId);
        if (!$this->website || $this->website->type != WebsiteModel::VIDEO) {
            die('错误的网站参数');
        }

        $this->config = $this->website->config;
        $this->config['proxy'] = false;
        $this->website->config = json_encode($this->config);

        while(true) {
            $videoList = $this->videoModel->where("website_id", '=', $websiteId)
                ->whereNull('local_cover')
                ->limit(100)->get();

            if (count($videoList) == 0) {
                break;
            }
            foreach ($videoList as $video) {
                $coverPath = $this->getDirPath($video->id). DIRECTORY_SEPARATOR . "cover.jpg";
                $imgData = $this->getPageData($video->web_cover, $this->website);
                $this->saveImgToLocal($coverPath, $imgData);

                $video->local_cover = $this->website->config['dir'] . DIRECTORY_SEPARATOR . $video->id . DIRECTORY_SEPARATOR . "cover.jpg";
                $video->save();
            }
            
        }        
    }

    public function saveImgToLocal($filePath, $fileData) {
        $dir = dirname($filePath); 
        if (!is_dir($dir)) {  
            mkdir($dir, 0777, true);  
        }

        file_put_contents($filePath,$fileData);
    }

    public function spiderVideo($video) {
        // 获取mu38文件的地址
        $temp_array = explode('/', $video->web_url);
        $videoUrl =  $this->website->config['ts_api_url'] . array_pop($temp_array);
        $mu38Data = $this->getPageData($videoUrl, $this->website);
        $mu38Url = json_decode($mu38Data, true)['m3u8'];
        
        // 抓取mu38文件
        $url = $this->website->url . $mu38Url;
        $data = $this->getPageData($url, $this->website);
        $tsList = $this->getTsList($data);

        // 解析出密钥
        $key = $this->getKey($data);

        // 抓文件的时候不要代理
        $this->config['proxy'] = false;
        $this->website->config = json_encode($this->config);

        foreach ($tsList as $value) {
            $temp_array = explode('/', $value);
            $fileName = array_pop($temp_array);
            $filePath = $this->getFilePath($video->id, $fileName);
            if (file_exists($filePath) && filesize($filePath) > 0) {
                continue;
            }
            $data = $this->getPageData($value, $this->website);
            $deData = openssl_decrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, hex2bin('00000000000000000000000000000000'));

            if (!$deData) {
                var_dump($data);exit;
            }
            $this->saveToLocal($this->getFilePath($video->id, $fileName), $deData);
        }

        // 合并文件
        $this->combineVideo($video);

        // 保存数据
        $data = [
            'status' => 2,
            'local_url' => $this->website->config['dir'] . DIRECTORY_SEPARATOR . $video->id . DIRECTORY_SEPARATOR . 'video.mp4',
            "updated_at" => date("Y-m-d H:i:s")
        ];
        $this->videoModel->where('id', '=', $video->id)->update($data);

        // 抓完文件重新设置代理
        $this->config['proxy'] = true;
        $this->website->config = json_encode($this->config);
    }

    public function getDirPath($dirName) {
        return base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->website->config['dir'] . DIRECTORY_SEPARATOR . $dirName;
    }
    public function getFilePath($dirName, $fileName) {
        $path = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->website->config['dir'] . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . $fileName;

        $dir = dirname($path);
        if (!is_dir($dir)) {  
            mkdir($dir, 0777, true);  
        }

        return $path;
    }

    public function getTsList($content) {
        $lines = explode("\n", $content);
 
        $tsFiles = [];
        foreach ($lines as $line) {
            // 检查行是否包含有效的 .ts 文件 URL 或路径
            if (preg_match('/http:\/\/|\/\//', $line)) { // 检查是否为完整的 URL
                $tsFiles[] = trim($line);
            } elseif (preg_match('/\.ts$/', $line)) { // 检查是否为相对路径或文件名结尾为 .ts
                $tsFiles[] = $line; // 可能需要进一步处理以形成完整的 URL 或路径
            }
        }
        return $tsFiles;
    }

    public function getKey($m3u8Content) {
        
        $pattern = '/#EXT-X-KEY:METHOD=AES-128,URI="([^"]+)"/';
        preg_match($pattern, $m3u8Content, $matches);
        if (!empty($matches[1])) {
            $keyUrl = $matches[1];
        } else {
            die("Key URI not found.");
        }

        $keyContent = $this->getPageData($this->website->url . $keyUrl, $this->website, true, false, false);
        return $keyContent;
    }


    public function saveToLocal($filePath, $data) {
        file_put_contents($filePath,$data);
    }

    public function combineVideo($video) {
        $dir = $this->getDirPath($video->id);
        $fileList = scandir($dir);
        $combineFilePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . "ff.txt";

        usort($fileList, function($a, $b) use ($dir) {
            $timeA = filemtime($dir . '/' . $a);
            $timeB = filemtime($dir . '/' . $b);
            return $timeA - $timeB; // 升序排序，如果想降序则返回 $timeB - $timeA
        });

        // 提前删除文件
        unlink($combineFilePath);

        // 读取文件列表并写入
        foreach ($fileList as $key => $value) {
            if ($value == '.' || $value == '..' || strpos($value,".jpg") !== false) {
                continue;
            }
            $fileStr = "file '" .  $dir . DIRECTORY_SEPARATOR . $value . "'\r\n";

            file_put_contents($combineFilePath, $fileStr,FILE_APPEND);
        }

        $command =  'ffmpeg -f concat -safe 0 -i ' . $combineFilePath . ' -c copy ' . $this->getFilePath($video->id, "video.mp4");
        $output = [];
        $return_var = -1;
        exec($command, $output, $return_var);
        
        // 检查命令执行结果
        if ($return_var === 0) {
            echo "视频合并成功！\r\n";
        } else {
            echo "视频合并失败。错误信息：" . implode("\n", $output);
        }
    }

    public function test() {
        $this->website = WebsiteModel::find(4);
        $combineFilePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . "ff.txt";
        for ($i=1; $i < 500; $i++) { 
            $dir = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . "taiav" . DIRECTORY_SEPARATOR . $i;
            $fileList = scandir($dir);

            if (!in_array('video.mp4', $fileList)) {
                continue;
            }

            $fileList = array_diff($fileList, ['.', '..', "cover.jpg", "video.mp4"]);

            usort($fileList, function($a, $b) use ($dir) {
                $timeA = filemtime($dir . '/' . $a);
                $timeB = filemtime($dir . '/' . $b);
                return $timeA - $timeB; // 升序排序，如果想降序则返回 $timeB - $timeA
            });

            // 读取文件列表并写入
            unlink($combineFilePath);
            unlink($dir . DIRECTORY_SEPARATOR . 'video.mp4');
            foreach ($fileList as $key => $value) {
                $fileStr = "file '" .  $dir . DIRECTORY_SEPARATOR . $value . "'\r\n";
                file_put_contents($combineFilePath, $fileStr,FILE_APPEND);
            }

            $command =  'ffmpeg -f concat -safe 0 -i ' . $combineFilePath . ' -c copy ' . $this->getFilePath($i, "video.mp4");
            $output = [];
            $return_var = -1;
            exec($command, $output, $return_var);
            
            // 检查命令执行结果
            if ($return_var === 0) {
                echo "视频合并成功！\r\n";
            } else {
                echo "视频合并失败。错误信息：" . implode("\n", $output);
            }
        }

    }
}

