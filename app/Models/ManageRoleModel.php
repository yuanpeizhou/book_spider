<?php
namespace app\common\model;

use think\model\Pivot;

/**
 * 管理员与角色关联model
 */
class ManageRoleModel extends Pivot
{

    protected $table = 'manage_role';

}
