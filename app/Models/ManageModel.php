<?php
namespace app\common\model;

use think\Model;
/**
 * 管理员model
 */
class ManageModel extends Model{

    protected $table = 'manage';

    /**关联角色 */
    public function conRole(){
        return $this->belongsToMany(RoleModel::class, ManageRoleModel::class, 'role_id', 'manage_id');
    }
    
}