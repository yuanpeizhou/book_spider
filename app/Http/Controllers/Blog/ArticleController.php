<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Blog\Article;

class PositionLevelController extends Controller
{
    protected $articleModel;

    public function __construct()
    {
        $this->articleModel = New Article();
    }

    /**
     * 等级列表
     * @param int $page 当前页码，不传默认为1
     * @param int $limit 分页大小，不传默认为10
     * @param string $type 获取数据模式 all 获取全部数据 page 分页获取数据 
     * @param string $keyword 搜索关键词(文章名称)
     * @param int $tagIds 标签
     * @return \Illuminate\Http\Response
     */
    public function index(ArticleRequest $request)
    {
        $limit = $request->limit;
        $keyword = $request->keyword;
        $type = $request->type ? $request->type : 'page';

        $res = $this->articleModel->select('id','title','cover','content','user_id','created_at');

        if($keyword){
            $res = $res->where('title','like',"%$keyword%");
        }

        if($type == 'all'){
            $res = $res->get();
        }else{
            $res = $res->paginate($limit);
        }

        return $this->returnApi(200, '查询成功',$res);
    }

    /**
     * 新增
     * @param string $title 文章名称
     * @param string $cover 文章封面
     * @param text $content 文章内容
     * @return \Illuminate\Http\Response
     */
    public function store(ArticleRequest $request)
    {
        $data = $request->scene('insert')->with('insert')->validate();

        $this->articleModel->fill($data);
        $res = $this->articleModel->save();

        if ($res === false) {
            return $this->returnApi(202, '新增失败,请稍后再试');
        }

        return $this->returnApi(200, '新增成功');
    }

    /**
     * 详情
     * @param int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ArticleRequest $request,$id)
    {
        $request->scene('info')->validate();

        $positionLevel = $this->positionLevelModel->find($id);

        return $this->returnApi(200, '查询成功',$positionLevel);
    }

    /**
     * 更新
     * @param int $id
     * @param string $title 等级名称
     * @return \Illuminate\Http\Response
     */
    public function update(ArticleRequest $request, $id)
    {
        $data = $request->scene('update')->with('update')->validate();

        $positonLevel = $this->positionLevelModel->find($id);

        $positonLevel->fill($data);
        $res = $positonLevel->save();

        if ($res === false) {
            return $this->returnApi(202, '编辑失败,请稍后再试');
        }

        return $this->returnApi(200, '编辑成功');
    }

    /**
     * 删除
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ArticleRequest $request, $id)
    {
        $request->scene('delete')->validate();

        $res = $this->positionLevelModel->destroy($id);

        if ($res === false) {
            return $this->returnApi(202, '删除失败,请稍后再试');
        }

        return $this->returnApi(200, '删除成功');
    }
}
