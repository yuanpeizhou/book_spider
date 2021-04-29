<?php
namespace App\Models\Admin;

use App\Models\BaseModel;

/**
 * 管理员model
 */
class ManageModel extends BaseModel{

    protected $table = 'manage';

    // /**关联角色 */
    // public function conRole(){
    //     return $this->belongsToMany(RoleModel::class, ManageRoleModel::class, 'role_id', 'manage_id');
    // }
}