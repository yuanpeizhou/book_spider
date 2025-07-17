<?php

namespace App\Http\Controllers\Spider;
use App\Http\Controllers\Spider\CommonController;
use App\Models\BookModel;
use App\Models\RoseModel;
use App\Models\WebsiteModel;

/**
 * 落花有声网
 */
class RoseController extends CommonController {

    protected $websiteModel;

    protected $roseModel;

    protected $website;

    protected $roseRule;

    public function __construct()
    {
        $this->roseModel = New RoseModel();
        $this->websiteModel = New WebsiteModel();
    }

    public function handle($websiteId) {
        $this->website = $this->websiteModel->find($websiteId);

        if (!$this->website || $this->website->type != WebsiteModel::IMG) {
            die('错误的网站参数');
        }
        $this->roseRule = $this->website->rule['rose'];

        echo "开始爬取套图\r\n";
        $pageData = $this->getPageData($this->website->url . $this->roseRule['url'], $this->website);
        switch ($this->roseRule['page_model']) {
            case 'all':
                $this->spiderPageByAll($this->website, $pageData, $this->roseRule['page_rule'], [$this, 'handleRose']);
                break;
            case 'next':
                $this->spiderPageByNext($this->website, $pageData, $this->roseRule['page_rule'], [$this, 'handleRose']);
                break;
            default:
                die("未知的抓取模式");
        }

        echo "爬取完毕\r\n";

        
        switch ($this->roseRule['page_model']) {
            case 'all':
                $this->handle1();
                break;

            case 'next':
                $this->handle2();
                break;
            
            default:
                die("未知的处理页面方式，请检查配置");
        }
    }

    public function handleRose($pageData) {
        $roseList = $this->parseRule($pageData, $this->roseRule['content_rule']);
        foreach ($roseList as $rose) {
            $this->saveRose($rose);
        }
    }

    /**
     * 保存套图信息
     * @param mixed $roseData
     * @return void
     */
    public function saveRose($roseData) {
        $rose = $this->roseModel->where("website_id", '=', $this->website->id)
            ->where("url", "=", $roseData['url'])->first();
        if (!$rose) {
            $roseData['website_id'] = $this->website->id;
            $roseData['created_at'] = date("Y-m-d H:i:s");
            $this->roseModel->insert($roseData);
            echo "套图《" .  $roseData['rose_name'] . "》入库完毕\r\n";
        } else {
            echo "套图《" . $roseData['rose_name'] . "》已在库中，停止爬取\r\n";
            exit;
        }
    }
}