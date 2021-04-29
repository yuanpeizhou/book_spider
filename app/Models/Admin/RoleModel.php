<?php
namespace app\common\model;

use think\Model;
/**
 * 角色model
 */
class RoleModel extends Model{

    protected $table = 'role';

    public function conPermission(){
        return $this->belongsToMany(PermissionModel::class, RolePermissionModel::class, 'permission_id', 'role_id');
    }

    public function conUser(){
        return $this->hasOne(ManageModel::class,'id','manage_id');
    }

}