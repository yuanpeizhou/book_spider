<?php

namespace App\Http\Controllers\Spider;
use App\Http\Controllers\Spider\CommonController;
use App\Models\BookModel;
use App\Models\ImgModel;
use App\Models\RoseModel;
use App\Models\WebsiteModel;

/**
 * 图片列表抓取
 */
class ImgController extends CommonController {

    protected $websiteModel;

    protected $roseModel;

    protected $imgModel;

    protected $website;

    protected $rose;

    protected $scanBookNum = 100;

    protected $imgRule;

    protected $currentOrder = 0;

    public function __construct()
    {
        $this->roseModel = New RoseModel();
        $this->websiteModel = New WebsiteModel();
        $this->imgModel = New ImgModel();
    }

    public function handle($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website || $this->website->type != WebsiteModel::IMG) {
            die('错误的网站参数');
        }
    
        $this->imgRule = $this->website->rule['img_list'];

        // 先查到需要爬取的第一套图
        $start = 0;
        $bookFirst = $this->roseModel->where("website_id", $this->website->id)
            ->where("is_scan", "=", 1)->first();
        if ($bookFirst) {
            $start = $bookFirst->id;
        }

        // 循环获取需要扫描的套图
        $handle = true;
        while($handle) {
            $roseList = $this->roseModel->where("website_id", $this->website->id)
                ->where("id", ">=", $start)
                ->where("is_scan", "=", 1)
                ->limit($this->scanBookNum)->orderBy("id")->get();
            if (count($roseList) == 0) {
                break;
            }

            foreach ($roseList as $rose) {
                echo "开始扫描《".$rose['rose_name']."》图片\r\n";

                $this->currentOrder = 0;
                $this->rose = $rose;
                $url = $this->website->url . $rose->url;
                $pageData = $this->getPageData($url, $this->website);

                // 根据扫描模式执行不同的策略
                switch ($this->imgRule['page_model']) {
                    case 'all':
                        $pageStart = isset($this->imgRule['page_start']) ? $this->imgRule['page_start'] : 1;
                        $this->spiderPageByAll($this->website, $pageData, $this->imgRule['page_rule'], $pageStart,[$this, 'handleImg']);
                        break;
                    case 'next':
                        $this->spiderPageByNext($this->website, $pageData, $this->imgRule['page_rule'], [$this, 'handleImg']);
                        break;
                    default:
                        die("未知的抓取模式");
                }
                $roseData = [
                    "is_scan" => 2,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->roseModel->where("id", $rose->id)->update($roseData);
                echo  "套图《".$rose['rose_name']."》扫描完毕\r\n";
            }

        }
        echo "所有套图扫描并录入完毕\r\n";
    }

    public function handleImg($pageData) {
        $imgList = $this->parseRule($pageData, $this->imgRule['content_rule']);
        $this->saveImgList($imgList);
    }

    public function saveImgList($imgList) {
        foreach ($imgList as $imgUrl) {
            // 洗一下链接
            if (strpos($imgUrl, "')") !== false) {
                $imgUrl = explode("')", $imgUrl)[0];
            }
            
            $this->currentOrder++;
            $img = $this->imgModel->where("rose_id", '=', $this->rose->id)
                ->where("img_web_url", "=", $imgUrl)->first();
            if ($img) {
                echo "图片: " . $imgUrl . "已录入, 略过\r\n";
                continue;
            }

            $imgData = [
                "website_id" => $this->website->id,
                "rose_id" => $this->rose->id,
                "img_web_url" => $imgUrl,
                "img_order" => $this->currentOrder,
                "created_at" => date("Y-m-d H:i:s")
            ];
            $this->imgModel->insert($imgData);
            echo "图片: " . $imgUrl . "录入成功\r\n";
        }
    }

    public function saveImg($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website || $this->website->type != WebsiteModel::IMG) {
            die('错误的网站参数');
        }

        while(true) {
            $imgList = $this->imgModel->where('website_id', $websiteId)->where("is_spider", 1)->limit(500)->get();
            if (count($imgList) == 0) {
                echo "所有图片都已爬取完毕\r\n";break;
            }

            foreach ($imgList as $key => $img) {
                if (strpos($img->img_web_url, "http") === false) {
                    $url = $this->website->url . $img->img_web_url;
                } else {
                    $url = $img->img_web_url;
                }
                
                $res = $this->getPageData($url, $this->website);
                $localPath = $this->saveImgToLocal($img, $res);
                $imgData = [
                    "img_local_url" => $localPath,
                    "is_spider" => 2,
                    "updated_at" => date("Y-m-d H:i:s"),
                ];
                $this->imgModel->where('id', '=', $img->id)->update($imgData);
            }
        }
    }

    public function saveImgToLocal($img, $data) {
        $temp_array = explode('/', $img->img_web_url);
        $fileName = array_pop($temp_array);
        $filePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->website->config['dir'] . DIRECTORY_SEPARATOR . $img->rose_id . DIRECTORY_SEPARATOR . $fileName;

        $dir = dirname($filePath); 
        if (!is_dir($dir)) {  
            mkdir($dir, 0777, true);  
        }

        file_put_contents($filePath,$data);

        return $this->website->dir . DIRECTORY_SEPARATOR . $img->rose_id . DIRECTORY_SEPARATOR . $fileName;
    }
}