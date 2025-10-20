<?php

namespace Modules\WebsiteBlog\Models;

use Modules\Core\Abstracts\BaseModel;


/**
 * Database model description
 *
 * @property int $id
 */

class BlogCategory extends BaseModel
{

    /**
    * The database table name.
    *
    * @var string
    */
    protected $table = 'blog_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'is_active',
        'is_public',
        'meta_title',
        'meta_description',
        'robot_index',
        'robot_follow',
        'website_id',
        'company_id'
    ];

}
