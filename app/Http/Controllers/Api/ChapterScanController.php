<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

/**
 * 章节数据爬取
 */
class ChapterScanController extends CommonController{

    protected $websiteModel;
    protected $bookModel;
    protected $chapterModel;
    protected $website;

    protected $start1 = 75951;

    protected $start2 = 24488;

    protected $bookSize = 5;

    protected $chapterLastPage = 1;

    public function __construct()
    {
        $this->websiteModel = New \App\Models\WebsiteModel();
        $this->bookModel = New \App\Models\BookModel();
        $this->chapterModel = New \App\Models\ChapterModel();
        ini_set('memory_limit', '1024M');
    }

    public function handle($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website) {
            throw new \Exception("无效的网站id");
        }

        // 先查一下第一条
        $chapterFirst = $this->chapterModel
            ->where('website_id', $this->website->id)
            // ->where('id', ">=", $this->start1)
            ->where("is_spider", '=', 0)
            ->first();
        if (!$chapterFirst) {
            echo "没查询到需要爬取的章节\r\n";exit;
        }else {
            $start = $chapterFirst->id;
            echo "查询完毕, 从" . $start . "开始\r\n";
        }

        $chapterContentRule = $this->website->rule['chapter_content'];
        $handle = true;
        while($handle) {
            echo $start . "\r\n";
            $chapterList = $this->chapterModel
                ->where('website_id', $this->website->id)
                ->where("is_spider", '=', 0)
                ->where('id', ">=", $start)
                ->limit(100)->get();
            if(count($chapterList) == 0) {
                $handle = false;
                break;
            }

            foreach ($chapterList as $chapter_index => $chapter) {
                $url = $this->website->url . $chapter->url;
                $pageData = $this->getPageData($url, $this->website);
                
                // 尝试获取分页
                $chapterPageList = [];
                try {
                    $chapterPageList = $this->parseRule($pageData, $chapterContentRule['page_rule']);
                } catch (\Exception $e) {
                    if ($e->getCode() != 501) {
                        $this->saveHtml($pageData);
                        echo $e->__toString();exit;
                    }
                }

                // 先提取第一页数据
                $content = null;
                $chapterPageContent = null;
                try {
                    $chapterPageContent= $this->parseRule($pageData, $chapterContentRule['content_rule']);
                } catch (\Exception $e) {
                    $this->saveHtml($pageData);
                    echo $e->__toString();exit;
                }

                if (is_array($chapterPageContent)) {
                    $content = $content . $chapterPageContent[0];
                } else {
                    $content = $content . $chapterPageContent;
                }

                // 处理剩下的页码
                foreach ($chapterPageList as $key => $value) {
                    if ($this->website->id == 2) {
                        $tempArr = explode('/', $chapter->url);
                        $tempStr = '/' . $tempArr[1] . '/' . $tempArr[2] . "/";
                        $url = $this->website->url . $tempStr . $value;
                    } else {
                        $url = $this->website->url . $value;
                    }

                    if ($this->website->id == 2 && $key == 0) {
                        continue;
                    }
                    
                    $pageData = $this->getPageData($url, $this->website);
                    try {
                        $chapterPageContent= $this->parseRule($pageData, $chapterContentRule['content_rule']);
                    } catch (\Exception $e) {
                        $this->saveHtml($pageData);
                        echo $e->__toString();exit;
                    }
                    
                    if (is_array($chapterPageContent)) {
                        $content = $content . $chapterPageContent[0];
                    } else {
                        $content = $content . $chapterPageContent;
                    }
                }

                $data = [
                    'content' => $content,
                    'is_spider' => 1,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->chapterModel->where('id', '=', $chapter->id)->update($data);

                echo "章节-" . $chapter->chapter_name . "爬取完毕\r\n";

                $book_data = [
                    'chapter_order' => $chapter->chapter_order,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->bookModel->where("id", '=', $chapter->book_id)->update($book_data);

                $start = $chapter->id;
            }
        }
    }
}
