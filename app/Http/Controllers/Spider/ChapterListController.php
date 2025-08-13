<?php

namespace App\Http\Controllers\Spider;

use App\Models\BookModel;
use App\Models\ChapterModel;
use App\Models\WebsiteModel;

class ChapterListController extends CommonController{
    protected $websiteModel;
    protected $bookModel;
    protected $chapterModel;

    // 当前加载的网站
    protected $website;

    // 当前加载的书籍
    protected $book;
    protected $chapterRule;
    protected $chapterOrder = 0;
    protected $scanBookNum = 100;

    public function __construct()
    {   
        $this->websiteModel = New WebsiteModel();
        $this->bookModel = New BookModel;
        $this->chapterModel = New ChapterModel;
    }

    public function handle($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website) {
            throw new \Exception("无效的网站id");
        }
        $this->chapterRule = $this->website->rule['chapter_list'];

        // 先查到第一本书籍
        $start = $this->getFirstBook();
        if ($start == 0) {
            echo "无需要爬取的书籍\r\n";exit;
        }

        // 循环获取需要扫描的书籍
        while(true) {
            $bookList = $this->bookModel->where("website_id", $this->website->id)
                ->where("id", ">=", $start)
                ->where("status", "=", 1)
                ->where("is_update", '=', BookModel::UPDATED)
                ->limit($this->scanBookNum)->orderBy("id")->get();
            if (count($bookList) == 0) {
                break;
            }

            foreach ($bookList as $book) {
                echo "开始扫描《".$book['book_name']."》章节\r\n";

                // 初始化章节计数
                $this->book = $book;
                $this->chapterOrder = 0;
                $pageData = $this->getPageData($this->website->url . $book->book_url, $this->website);

                // 根据扫描模式执行不同的策略
                switch ($this->chapterRule['page_model']) {
                    case "all":
                        $pageStart = isset($this->chapterRule['page_start']) ? $this->chapterRule['page_start'] : 1;
                        $this->spiderPageByAll($this->website, $pageData, $this->chapterRule['page_rule'], $pageStart, [$this, 'handleChapter']);
                        break;
                    case "next":
                        $this->spiderPageByNext($this->website, $pageData, $this->chapterRule['page_rule'], [$this, 'handleChapter']);
                        break;
                    default:
                        die("未知的抓取模式");
                }
                $bookData = [
                    "is_update" => BookModel::WAIT_SIPDER,
                    "chapter_num" => $this->chapterOrder,
                    "updated_at" => date("Y-m-d H:i:s")
                ];
                $this->bookModel->where("id", $book->id)->update($bookData);
                echo  "书籍《".$book['book_name']."》扫描完毕\r\n";
                $start = $book->id;
            }

        }
        echo "所有书籍扫描并录入完毕\r\n";
    }

    /**
     * 解析章节
     * @param mixed $pageData
     * @return void
     */
    public function handleChapter($pageData) {
        // 尝试抓取章节列表
        $chapterList = $this->parseRule($pageData, $this->chapterRule['content_rule']);
        if (count($chapterList) == 0) {
            $bookData = [
                "status" => 2,
                "updated_at" => date("Y-m-d H:i:s")
            ];
            $this->bookModel->where('id', "=", $this->book->id)->update($bookData);
            echo "书籍可能有问题，标记\r\n"; 
        }

        // 把抓取到的章节入库
        $this->saveChapter($chapterList);
    }

    /**
     * 保存章节数据
     * @param mixed $bookId 书籍id
     * @param mixed $chapterList 抓取的章节列表
     * @return void
     */
    public function saveChapter($chapterList) {
        foreach ($chapterList as $chapterData) {
            // 章节计数
            $this->chapterOrder++;

            // 保存章节数据
            $chapter = $this->chapterModel->where("book_id", "=", $this->book->id)
                ->where('chapter_name', $chapterData['chapter_name'])->first();
            if ($chapter) {
                echo "章节：" . $chapterData["chapter_name"] . "已录入，跳过\r\n";
                continue;
            }

            $chapterData = array_merge($chapterData, [
                "website_id" => $this->website->id,
                "book_id" => $this->book->id,
                "chapter_order" => $this->chapterOrder,
                "created_at" => date("Y-m-d H:i:s")
            ]);
            $this->chapterModel->insert($chapterData);
            echo "章节：" . $chapterData["chapter_name"] . "录入成功\r\n";
        }
    }

    // 获取需要爬取的第一本书
    public function getFirstBook() {
        $firstBook = $this->bookModel->where("website_id", $this->website->id)
            ->where("status", "=", 1)->where("is_update", '=', BookModel::UPDATED)->first();
        if (!$firstBook) {
            return 0;
        }
        return $firstBook->id;
    }
}