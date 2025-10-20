<?php

namespace Modules\WebsiteBlog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Abstracts\BaseModel;
use Modules\Core\Models\Traits\HasFiles;
use Modules\Core\Models\UserExtended;

/**
 * Database model description
 *
 * @property int $id
 */

class BlogPost extends BaseModel
{
    use SoftDeletes, HasFiles;

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'blog_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_public',
        'short_description',
        'content',
        'blog_category_id',
        'meta_title',
        'meta_description',
        'robot_index',
        'robot_follow',
        'website_id',
        'company_id',
        'user_id',
    ];

    public array $publishableFileFields = ['image'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id')
            ->select(['id', 'name', 'slug', 'meta_title', 'meta_description']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserExtended::class, 'user_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $model) {
            if ($model->wasChanged('is_public')) {
                if ($model->wasChanged('is_public') && $model->image) {
                    if ($model->wasChanged('is_public')) {
                        $file = $model->image()->first();
                        if ($file) {
                            $model->is_public ? $file->publish() : $file->unpublish();
                            $file->is_public = $model->is_public;
                            $file->save();
                        }
                    }
                }
            }
        });
    }

}
