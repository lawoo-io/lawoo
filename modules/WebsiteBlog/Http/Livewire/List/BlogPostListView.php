<?php

namespace Modules\WebsiteBlog\Http\Livewire\List;


use Modules\Web\Http\Livewire\List\BaseListView;
use Modules\WebsiteBlog\Repositories\BlogPostRepository;

class BlogPostListView extends BaseListView
{
    /**
    * !Required
    * @var string
    */
    public ?string $moduleName = 'WebsiteBlog';

    /**
     * !Required
     * @var string
     */
    public ?string $modelClass = 'BlogPost';

    /**
     * !Required
     * @var string
     */
    protected string $repositoryClass = BlogPostRepository::class;

    /**
     * @var array
     */
    public array $defaultColumns = ['id', 'name', 'is_public'];

    /**
     * @var string
     */
    public string $createViewRoute = 'lawoo.website.blog.posts.create';

    /**
     * @var string
     */
    public string $formViewRoute = 'lawoo.website.blog.posts.update';

    /**
     * Function boot
     */
    public function boot(): void
    {
        $this->title = __t('Posts', 'WebsiteBlog');
        parent::boot();
    }

    /**
     * Function setSearchFields
     */
    public static function setSearchFields(): array
    {
        return [
            'name' => __t('Name', 'WebsiteBlog'),
        ];
    }

    /**
     * Function getAvailableColumns
     */
    public function getAvailableColumns(): array
    {
        return [
            'id' => [
                'label' => __t('ID', 'WebsiteBlog')
            ],
            'name' => [
                'label' => __t('Name', 'WebsiteBlog'),
            ],
            'is_public' => [
                'label' => __t('Published', 'WebsiteBlog'),
                'type' => 'switch',
            ]
        ];
    }
}
