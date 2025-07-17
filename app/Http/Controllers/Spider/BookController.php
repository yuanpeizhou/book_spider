<?php
namespace App\Http\Controllers\Spider;

use App\Models\BookModel;
use App\Models\ChapterModel;
use App\Models\WebsiteModel;

class BookController extends CommonController{

    protected $bookModel;

    protected $websiteModel;

    protected $chapterModel;

    protected $website = null;

    protected $bookRule = null;

    // 未更新书籍计数
    protected $notUpdateCount;

    protected $notUpdateLimit = 300;

    public function __construct()
    {
        $this->bookModel = New BookModel();
        $this->websiteModel = New WebsiteModel();
        $this->chapterModel = New ChapterModel();
    }

    /**
     * 扫描网站书籍
     */
    public function handle($websiteId){
        $this->website = $this->websiteModel->find($websiteId);
        if (!$this->website) {
            throw new \Exception("无效的网站id");
        }

        echo "开始爬取书库\r\n";
        $this->bookRule = $this->website->rule['book'];
        $pageData = $this->getPageData($this->bookRule['url'], $this->website);
        switch ($this->bookRule['page_model']) {
            case 'all':
                $this->spiderPageByAll($this->website, $pageData, $this->bookRule['page_rule'], [$this, 'handleBook']);
                break;
            case 'next':
                $this->spiderPageByNext($this->website, $pageData, $this->bookRule['page_rule'], [$this, 'handleBook']);
                break;
            default:
                die("未知的抓取模式");
        }

        echo "爬取完毕\r\n";
    }

    /**
     * 解析书籍数据
     * @param mixed $pageData
     * @return void
     */
    public function handleBook($pageData) {
        $bookList = $this->parseRule($pageData, $this->bookRule['content_rule']);
        foreach ($bookList as $book) {
            $this->saveBook($book);
        }

        if ($this->notUpdateCount >= $this->notUpdateLimit) {
            echo "未更新书籍计数大于" . $this->notUpdateLimit . ", 结束此次爬取\r\n";exit;
        }
    }

    /**
     * 保存书籍数据
     * @param array $bookData
     * @return void
     */
    public function saveBook(array $bookData) {
        $book = $this->bookModel->where("website_id", '=', $this->website->id)
            ->where("book_name", "=", $bookData['book_name'])->first();
        if (!$book) {
            unset($bookData['last_chapter_name']);
            $bookData['website_id'] = $this->website->id;
            $bookData['created_at'] = date("Y-m-d H:i:s");
            $bookData['is_update'] = BookModel::UPDATED;
            $this->bookModel->insert($bookData);
            echo "书籍《" .  $bookData['book_name'] . "》入库完毕\r\n";
        } else {
            // 对比下最新章节名称是不是一样的, 不一样就说明更新了
            $lastChapter = $this->chapterModel->where("website_id", '=', $this->website->id)
                ->where("book_id", $book->id)->orderBy('chapter_order', "desc")->first();
            if ($lastChapter['chapter_name'] != $bookData['last_chapter_name']) {
                unset($bookData['last_chapter_name']);
                $bookData['is_update'] = BookModel::UPDATED;
                $bookData['updated_at'] = date("Y-m-d H:i:s");
                $bookData['last_update_date'] = isset($bookData['last_update_date']) ? $bookData['last_update_date'] : date("Y-m-d");
                $this->bookModel->where("id", $book->id)->update($bookData);
                echo "书籍《" . $book->book_name . "》有更新，已标记\r\n";
            } else {
                $data = [
                    'is_update' => BookModel::NOT_UPDATE,
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                $this->bookModel->where("id", $book->id)->update($data);
                $this->notUpdateCount++;
                echo "书籍《" . $book->book_name . "》无更新，略过\r\n";
            }
        }
    }
}