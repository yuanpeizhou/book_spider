<?php
namespace app\common\model;

use think\model\Pivot;

/**
 * 管理员与权限关联model
 */
class RolePermissionModel extends Pivot
{

    protected $table = 'role_permission';

}
