<?php
namespace App\Models\Blog;

use App\Models\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 博客文章model
 */
class Article extends BaseModel{

    const TABLE = 'articles';

    public $table = 'articles';

    //关联作者
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}