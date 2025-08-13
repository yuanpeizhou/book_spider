<?php

namespace App\Models;

class WebsiteModel extends BaseModel {

    protected $table = 'website';

    const BOOK = 1;
    const IMG = 2;
    const VIDEO = 3;

    /**
     * 获取解析规则
     */
    public function getRuleAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 获取header
     */
    public function getHeaderAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return json_decode($value, true);
    }

    /**
     * 获取config
     */
    public function getConfigAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return json_decode($value, true);
    }
}