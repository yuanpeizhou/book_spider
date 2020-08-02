<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

class LibScanController extends CommonController{

    protected $webKuUrl;

    protected $webHomeUrl;

    protected $websisteId = 1;

    protected $lastPage = 1;

    protected $shuKuUrl = '';

    protected $startPage = 842;

    public function __construct()
    {   
        $this->webLibUrl = 'https://www.diyibanzhu5.pro/shuku/';
        $this->webHomeUrl = 'https://www.diyibanzhu5.pro/';
        $this->bookModel = New \App\Models\BookModel();
    }

    public function new(){
        $url = 'https://www.lzlib.com/go.htm?c=xin_wen_zhong_xin&url=fwzn&pageIndex=';

        $newData = [];

        for ($i=1; $i < 6; $i++) { 
            $newUrl = $url . $i;

            $pageData = $this->getPageData($newUrl,false);

            $pageContent = $this->getNewContent($pageData);

            foreach ($pageContent as $key => $value) {
                $data = $this->handleNewContent($value);
                $temp = [];

                $temp['type_id'] = 1;
                $temp['title'] = $data['title'];
                $temp['img'] = $data['cover'];
                $temp['content'] = $data['content'];
                $temp['manage_id'] = 1;
                $temp['create_time'] = date("Y-m-d H:i:s",time());
                
                $newData[] = $temp;
            }
        }

        $user = DB::table('lz_news')->insert($newData);
    }

    /**
     * 扫描网站书籍
     */
    public function scan(){
        // $homePageData = $this->getPageData('https://www.diyibanzhu5.pro/shuku/0-lastupdate-0-26.html');
        // dd($homePageData);
        echo "开始爬取书库\r\n";
        $this->handlePageHome();
        $this->handleBookList();
        echo "书库数据爬取完毕\r\n";
    }

    /**处理第一页数据 */
    public function handlePageHome(){
        echo "开始扫描首页信息\r\n";
        /**获取书籍信息 */
        $homePageData = $this->getPageData($this->webLibUrl);
        $pageContent = $this->getLibContent($homePageData);

        $endPageData = $this->getLibLastPage($homePageData);
        $this->shuKuUrl = $endPageData['shuKuUrl'];
        $this->lastPage = $endPageData['lastPage'];

        $this->bookSave($pageContent);

        echo "首页成功入库\r\n";
    }

    /**循环处理剩余页面 */
    public function handleBookList(){
        for ($i = ($this->startPage ? $this->startPage : 2); $i <= $this->lastPage; $i++) { 
            echo "开始爬取第". $i ."页书籍\r\n";
            $url = $this->webHomeUrl . $this->shuKuUrl . '-' . $i . '.html';
            $this->handleLibPage($url);
            echo "成功爬取第". $i ."页书籍\r\n";
        }
    }

    /**
     * 处理单页面
     */
    public function handleLibPage($url){
        $pageData = $this->getPageData($url);
        
        $pageContent = $this->getLibContent($pageData);
        
        $this->bookSave($pageContent,true);
    }

    /**
     * 书籍信息入库
     */
    public function bookSave($bookList,$test = false){
        $bookData = [];

        foreach ($bookList as $key => $value) {
            $data = $this->getBookInfo($value);

            // if($test){
            //     dd($data);
            // }
            $temp = [];
            $temp['website_id'] = $this->websisteId;
            $temp['book_name'] = $data['book_name'];
            $temp['author_name'] = $data['author_name'];
            $temp['url'] = $data['book_url'];
            $temp['last_update_date'] = $data['last_update_date'];
            $temp['words'] = $data['boook_words'];
            $temp['created_at'] = date("Y-m-d H:i:s",time());

            $bookData[] = $temp;
        }

        $bookUrlRes = $this->bookModel->pluck('url')->toArray();

        foreach ($bookData as $key => $value) {
            if(in_array($value['url'],$bookUrlRes)){
                unset($bookData[$key]);
            }
        }

        $this->bookModel->insert($bookData);
    }

}

// https://www.lzlib.com/go.htm?c=xin_wen_zhong_xin&url=ltgk2&pageIndex=28