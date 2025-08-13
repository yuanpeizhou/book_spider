<?php

namespace App\Http\Controllers\Api;

use App\Models\BookModel;
use App\Models\RoseModel;
use App\Models\VideoModel;
use Illuminate\Http\Request;

/**
 * 收藏
 */
class CollectController extends CommonController {
    
    public function collect(Request $request) {
        $type = $request->post('type');
        $id = $request->post('id');
        $action = $request->post('action');

        if (!$type || !$id || !$action) {
            return $this->returnApi(500, '参数错误');
        }

        $model = null;
        switch ($type) {
            case 1:
                $model = New BookModel();
                break;
            case 2:
                $model = New RoseModel();
                break;
            case 3:
                $model = New VideoModel();
                break;
            default:
                return $this->returnApi(500, '未知的资源类型');
        }

        $res = $model->where('id', '=', $id)->update(['collect' => $action]);
        if ($res > 0) {
            return $this->returnApi(200, '操作成功');
        } else {
            return $this->returnApi(500, '操作失败');
        }
    }
}