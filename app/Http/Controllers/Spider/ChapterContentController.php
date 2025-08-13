<?php

namespace App\Http\Controllers\Spider;

use App\Models\BookModel;

/**
 * 章节数据爬取
 */
class ChapterContentController extends CommonController{

    protected $websiteModel;
    protected $bookModel;
    protected $chapterModel;
    protected $website;
    protected $chapterContentRule;
    protected $currentChapter;

    public function __construct()
    {
        $this->websiteModel = New \App\Models\WebsiteModel();
        $this->bookModel = New BookModel();
        $this->chapterModel = New \App\Models\ChapterModel();
        ini_set('memory_limit', '1024M');
    }

    public function handle($websiteId, $start = 0) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website) {
            throw new \Exception("无效的网站id");
        }
        $this->chapterContentRule = $this->website->rule['chapter_content'];

        if ($start == 0) {
            $start = $this->getFirstBook();
        }
        if ($start == 0 ) {
            echo "没有需要爬取的章节\r\n";exit;
        }

        while(true) {
            $bookList = $this->bookModel
                ->where('website_id', $this->website->id)
                ->where("is_update", '=', BookModel::WAIT_SIPDER)
                ->where('id', ">=", $start)
                ->orderBy('id')
                ->limit(100)->get();
            if(count($bookList) == 0) {
                echo "所有书籍都已爬取完毕\r\n";
                break;
            }

            foreach ($bookList as $book) {
                echo "开始爬取《".$book['book_name']."》章节\r\n";

                $chapterList = $this->chapterModel
                    ->where("book_id", "=", $book->id)
                    ->where('is_spider', '=', 0)
                    ->get();

                foreach ($chapterList as $chapter) {
                    $this->currentChapter = "";
                    $url = $this->website->url . $chapter->url;
                    // 检查url模式
                    if (isset($this->chapterContentRule['page_url_model']) && $this->chapterContentRule['page_url_model'] == 'relative_url') {
                        $tempArray = explode('/', $url);
                        array_pop($tempArray);
                        $this->website->relative_url = implode('/', $tempArray) . "/";
                    }
                    $pageData = $this->getPageData($url, $this->website);
                    
                    // 根据扫描模式执行不同的策略
                    switch ($this->chapterContentRule['page_model']) {
                        case "all":
                            $pageStart = isset($this->chapterContentRule['page_start']) ? $this->chapterContentRule['page_start'] : 1;
                            $this->spiderPageByAll($this->website, $pageData, $this->chapterContentRule['page_rule'], $pageStart,[$this, 'handleChapter']);
                            break;
                        case "next":
                            $this->spiderPageByNext($this->website, $pageData, $this->chapterContentRule['page_rule'], [$this, 'handleChapter']);
                            break;
                        default:
                            die("未知的抓取模式");
                    }

                    $chapterData = [
                        'content' => $this->currentChapter,
                        'is_spider' => 1,
                        "updated_at" => date("Y-m-d H:i:s")
                    ];
                    $this->chapterModel->where('id', '=', $chapter->id)->update($chapterData);
                    echo "章节-" . $chapter->chapter_name . "爬取完毕\r\n";
                }

                $bookData = [
                    'chapter_order' => $book->chapter_num,
                    'is_update' => BookModel::NOT_UPDATE,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->bookModel->where("id", '=', $book->id)->update($bookData);
                echo "书籍-" . $book->book_name . "爬取完毕\r\n";
                $start = $book->id;
            }
        }
    }

    public function handleChapter($pageData) {
        $chapterContent = $this->parseRule($pageData, $this->chapterContentRule['content_rule']);
        
        $this->currentChapter = $this->currentChapter.$chapterContent;
    }

    // 获取需要爬取的第一章id
    public function getFirstBook() {
        $firstBook = $this->bookModel->where("website_id", '=', $this->website->id)
            ->where("is_update", "=", BookModel::WAIT_SIPDER)
            ->first();
        if (!$firstBook) {
            return 0;
        }
        return $firstBook->id;
    }
}