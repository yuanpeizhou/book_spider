<?php

namespace App\Http\Requests;
use App\Models\Blog\Article;
use Illuminate\Validation\Rule;


/**
 * 文章验证器
 */
class ArticleRequest extends AbstractRequest
{
    public $scenes = [
        'insert' => 'title,content',
        'update' => 'id,title,content',
        'info' => 'id',
        'delete' => 'id',
    ];

    public function rules():array
    {
        return [
            'id' => ['required','numeric',
                Rule::exists(Article::TABLE)->where(function ($query) {
                    $query->where('deleted_at', null);
                })
            ],
            'title' => ['required','max:50','string'],
            'content' => ['required','max:100000','string']
        ];
    }

    public function messages():array
    {
        return [
            'id.required' => '缺少id',
            'id.numeric' => 'id参数格式错误',
            'id.exists' => 'id不存在',
            'title.required' => '请填写文章名称',
            'title.string' => '文章名称格式错误',
            'title.max' => '文章名称长度上限为50个字符',
            'content.required' => '请填写文章内容',
            'content.max' => '文章内容上限10万字',
            'content.string' => '文章内容格式错误',
        ];
    }

    /**新增规则 */
    public function insertRules():array
    {
        return [
            'title' => ['required','max:20','string',Rule::unique(Article::TABLE)->where(function ($query) {
                $query->where('deleted_at', null);
            })]
        ];
    }

    /**编辑规则 */
    public function updateRules():array
    {
        return [
            'title' => [
                'required', 'string', 'max:20',
                Rule::unique(Article::TABLE)->where(function ($query) {
                    $query->where('deleted_at', null)->where('id', '<>', request()->id);
                }),
            ]
        ];
    }
}