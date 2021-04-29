<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\CommonController;

class Home extends CommonController
{
    /**管理员model */
    protected $user_model;

    /**权限model */
    protected $permission_model;

    public function __construct()
    {
        $this->user_model = New \App\Models\Admin\ManageModel();
    }

    /**登录 */
    public function login(){
        $account = request()->account;
        $password = request()->password;

        if(!$account || !$password){
            return $this->returnApi(201,'参数传递错误');
        }


        $user = $this->user_model->where('account',$account)->find();

        if(!$user){
            return $this->returnApi(202,'账号或密码输入错误');
        }

        if(md5($password.config('private.password_str')) != $user->password){
            return $this->returnApi(202,'账号或密码输入错误');
        }

        /*生成token*/
        $tokenInfo = $this->setToken($user);

        $user->token = $tokenInfo['token'];
        $user->token_expire = $tokenInfo['expire_time'];
        $res = $user->save();

        if(!$res){
            return $this->returnApi(202,'登录失败,请稍后重试');
        }

        // $sidebar_list = $this->getSideBar($user);

        // if(!$sidebar_list){
        //     return $this->returnApi(202,'资源加载失败,请联系管理员检查是否分配权限');
        // }

        // $route_list = $this->getRouteList($user);

        // if(!$route_list){
        //     return $this->returnApi(202,'资源加载失败,请稍后重试');
        // }



        // $permission_list = $this->gerPermissionListAll($user);

        // $result = [
        //     'account' => $user->account , 
        //     'token' => $tokenInfo['token'], 
        //     'route_list' => $route_list,
        //     'sidebar_list' => $sidebar_list,
        //     'permission_list' => $permission_list
        // ];

        // return $this->returnApi(200, "成功", true, $result);
    }

    // /**修改密码 */
    // public function passwordRest(){
    //     if(!request()->user){
    //         return $this->returnApi(201,'参数传递错误');
    //     }
        
    //     if(!request()->password){
    //         return $this->returnApi(201,'参数传递错误');
    //     }
        
    //     $user = request()->user;
    //     $user->password = md5(request()->password.config('md5_str'));
        
    //     $res = $user->save();

    //     if(!$res){
    //         return $this->returnApi(202,'修改失败');
    //     }

    //     return $this->returnApi(200, "修改成功", true, []);

    // }

    // /**获取路由列表 需修改*/
    // public function getRouteList($user){
    //     $permission_model = New \app\common\model\PermissionModel();

    //     $permission_id = $this->getUserPermissionId($user);

    //     $permission_list = $permission_model
    //     ->field('id,route_name,route_path,component_path,pid')
    //     ->whereIn('id',$permission_id)
    //     ->where('is_del','NO')
    //     ->whereIn('type',[2,3])
    //     ->select()->toArray();

    //     if(empty($permission_list)){
    //         return [];
    //     }

    //     return $permission_list;
    // }

    // /**获取侧边栏 */
    // public function getSideBar($user){
    //     $permission_model = New \app\common\model\PermissionModel();

    //     $permission_id = $this->getUserPermissionId($user);

    //     $permission_list = $permission_model
    //     ->field('id,icon,route_name,sidebar_name,route_path,pid,type')
    //     ->whereIn('id',$permission_id)
    //     ->where('is_del','NO')
    //     ->whereIn('type',[1,2])
    //     ->order('level')
    //     ->order('order')
    //     ->select()->toArray();

    //     if(empty($permission_list)){
    //         return [];
    //     }

    //     $sidebar_list = [];
    //     foreach ($permission_list as $key => $value) {
    //         if($value['type'] == 1){
    //             $sidebar_list[] = $this->getTree($value,$permission_list);
    //         }
    //     }
    //     return $sidebar_list;
    // }

    // public function gerPermissionListAll($user){
    //     $permission_model = New \app\common\model\PermissionModel();

    //     $permission_id = $this->getUserPermissionId($user);

    //     $permission_list = $permission_model
    //     ->whereIn('id',$permission_id)
    //     ->fieldRaw("id,path,CONCAT_WS('|',`permission_name`,`order`) as permission_name,pid,type,route_path,api_path")
    //     ->where('is_del','NO')
    //     ->order('level')
    //     ->order('order')
    //     ->select()->toArray();

    //     if(empty($permission_list)){
    //         return [];
    //     }

    //     return $permission_list;
    // }

    // /**获取权限树形列表 */
    // public function gerPermissionList($user){
    //     $permission_model = New \app\common\model\PermissionModel();

    //     $permission_id = $this->getUserPermissionId($user);

    //     $permission_list = $permission_model
    //     ->whereIn('id',$permission_id)
    //     ->fieldRaw("id,path,CONCAT_WS('|',`permission_name`,`order`) as permission_name,pid,type")
    //     ->where('is_del','NO')
    //     ->order('level')
    //     ->order('order')
    //     ->select()->toArray();

    //     if(empty($permission_list)){
    //         return [];
    //     }

    //     $res = [];
    //     foreach ($permission_list as $key => $value) {
    //         if($value['type'] == 1){
    //             $res[] = $this->getTree($value,$permission_list);
    //         }
    //     }
    //     return $res;
    // }

    // public function getUserPermissionId($user){
    //     $permission_model = New \app\common\model\PermissionModel();
    //     $permission_all_list = $permission_model->field('id,path')->where('is_del','NO')->select();

    //     if($user->id == 1){
    //         $permission_all_list = $permission_all_list->toArray();

    //         return array_column($permission_all_list,'id');
    //     }

    //     $permisson_ids = [];

    //     foreach ($user->conRole as $key => $value) {
    //         $user_permission_list = $value->conPermission;
    //         foreach ($user_permission_list as $permission_key => $permission_value) {
    //             $permisson_ids[] = $permission_value->id;
    //         }
    //     }
    //     return $permisson_ids;
    // }

    // function getTree($arr,$menu){
    //     if(!$arr['pid']){
    //         $arr['children']= [];
    //     }

    //     foreach ($menu as $key => $value) {
    //         if($arr['id'] == $value['pid']){
    //             $temp = [];
    //             $temp = $this->getTree($value,$menu);
    //             if($temp){
    //                 $arr['children'][] = $temp;
    //             }
    //         }
    //     }

    //     return $arr;
    // }
}