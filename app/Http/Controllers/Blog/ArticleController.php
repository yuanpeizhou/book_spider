<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Blog\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected $articleModel;

    public function __construct()
    {
        $this->articleModel = New Article();
    }

    /**
     * 列表
     * @param ArticleRequest $request
     * @return JsonResponse
     * @throws null
     */
    public function index(Request $request):JsonResponse
    {
        $limit = $request->input('limit',10);
        $keyword = $request->input('keyword');
        $type = $request->input('type','page');

        $query = $this->articleModel->query()
            ->select('id','title','cover','content','user_id','created_at')
            ->with('user:id,name');

        if($keyword){
            $query->where('title','like',"%$keyword%");
        }

        if($type == 'all'){
            $articles = $query->get();
        }else{
            $articles = $query->paginate($limit);
        }

        foreach ($articles as $key => $article){

            $articles[$key]->username = $article->user ? $article->user->name : null;

            unset($articles[$key]->user);
        }

        return $this->returnApi(200, '查询成功',$articles);
    }

    /**
     * 详情
     * @param ArticleRequest $request
     * @param int $id 文章id
     * @return JsonResponse
     * @throws null
     */
    public function show(ArticleRequest $request,int $id): JsonResponse
    {
        $request->scene('info')->validate();

        $article = $this->articleModel->query()->find($id);

        return $this->returnApi(200, '查询成功',$article);
    }

    /**
     * 新增
     * @param ArticleRequest $request
     * @return JsonResponse
     * @throws null
     */
    public function store(ArticleRequest $request):JsonResponse
    {
        $data = $request->scene('insert')->with('insert')->check();

        $this->articleModel->fill($data);
        $res = $this->articleModel->save();

        if ($res === false) {
            return $this->returnApi(202, '新增失败,请稍后再试');
        }

        return $this->returnApi(200, '新增成功');
    }

    /**
     * 更新
     * @param ArticleRequest $request
     * @param int $id 文章id
     * @return JsonResponse
     * @throws null
     */
    public function update(ArticleRequest $request,int $id): JsonResponse
    {
        $data = $request->scene('update')->with('update')->check();

        $article = $this->articleModel->query()->find($id);

        $article->fill($data);
        $res = $article->save();

        if ($res === false) {
            return $this->returnApi(202, '编辑失败,请稍后再试');
        }

        return $this->returnApi(200, '编辑成功');
    }

    /**
     * 删除
     * @param ArticleRequest $request
     * @param int $id 文章id
     * @return JsonResponse
     * @throws null
     */
    public function destroy(ArticleRequest $request,int $id):JsonResponse
    {
        $request->scene('delete')->validate();

        $res = $this->articleModel->destroy($id);

        if ($res == false) {
            return $this->returnApi(202, '删除失败,请稍后再试');
        }

        return $this->returnApi(200, '删除成功');
    }
}
