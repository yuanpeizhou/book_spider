<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use \App\Models\PositionLevel;

/**职位等级验证器 */
class ArticleRequest extends AbstractRequest
{
    public $scenes = [
        'insert' => 'title',
        'update' => 'id,title',
        'info' => 'id',
        'delete' => 'id',
    ];

    public function rules()
    {
        return [
            'id' => ['required','numeric',
                Rule::exists(PositionLevel::class)->where(function ($query) {
                    $query->where('deleted_at', null);
                })
            ],
            'title' => ['required','max:20','string'],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => '缺少参数1',
            'id.numeric' => '参数格式错误1',
            'id.exists' => '参数传递错误1',
            'title.required' => '请填写职位等级',
            'title.string' => '参数格式错误2',
            'title.max' => '职位等级长度上限为20个字符',
            'title.unique' => '该职位等级已存在'
        ];
    }

    /**新增规则 */
    public function insertRules(){        
        return [
            'title' => ['required','max:20','string',Rule::unique(PositionLevel::class)->where(function ($query) {
                $query->where('deleted_at', null);
            })]
        ];
    }

    /**编辑规则 */
    public function updateRules(){
        return [
            'title' => [
                'required', 'string', 'max:20',
                Rule::unique(PositionLevel::class)->where(function ($query) {
                    $query->where('deleted_at', null)->where('id', '<>', request()->id);
                }),
            ]
        ];
    }
}