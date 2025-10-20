<?php

namespace Modules\Website\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Core\Services\PathService;

class ContentManager
{

    public static function publish(Model $model): void
    {
        try {
            [$filePath, $content] = self::getFilePath($model);

            if (!$filePath) {
                throw new \RuntimeException("Kein gültiger Pfad für {$model->getTable()}");
            }

            $directory = dirname($filePath);

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($filePath, $content);
            Artisan::call('view:clear');

        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function unpublish(Model $model): void
    {
        try {

            [$filePath, $content] = self::getFilePath($model);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            Artisan::call('view:clear');

        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    protected static function getFilePath(Model $model): array
    {
        $path = '';
        $content = '';
        switch ($model->getTable()) {
            case 'layouts':
                [$path, $content] = self::prepareLayout($model);
                break;
            case 'pages':
                [$path, $content] = self::preparePage($model);
                break;
            case 'assets':
                [$path, $content] = self::prepareAsset($model);
                break;
        }
        return [$path, $content];
    }

    protected static function prepareLayout(Model $model): array
    {
        if(empty($model->path)) $path = Str::slug($model->name);
        else $path = $model->path;
        $layoutPath = resource_path('views/websites/website_' . $model->website->slug . '/components/layouts/' . $path . '.blade.php');
        $content = $model->content;

        return [$layoutPath, $content];
    }

    protected static function preparePage(Model $model): array
    {
        if(empty($model->path)) $path = Str::slug($model->name);
        else $path = $model->path;
        $pagePath = resource_path('views/websites/website_' . $model->website->slug . '/pages/' . $path . '.blade.php');

        $websiteModulePath = PathService::getModulePath('Website');
        $pageStubPath = "{$websiteModulePath}/Data/Stubs/pageNew.stub";

        $pageStub = file_get_contents($pageStubPath);

        $layoutName = $model->layout->path;



        $content = str_replace(
            [
                '{{ websiteSlug }}',
                '{{ layoutName }}',
                '{{ metaTitle }}',
                '{{ metaDescription }}',
                '{{ robotIndex }}',
                '{{ robotFollow }}',
                '{{ canonicalUrl }}',
                '{{ content }}'
            ],
            [
                $model->website->slug,
                $layoutName,
                self::prepareTitle($model),
                $model->meta_description,
                $model->robot_index ?? 'index',
                $model->robot_follow ?? 'follow',
                $model->canonical_url ?? url($model->url),
                $model->content
            ],
            $pageStub
        );

        return [$pagePath, $content];
    }

    protected static function prepareTitle(Model $model): string
    {
        if($model->meta_dynamic) return '';
        if(!empty($model->meta_title)) return 'title="'.$model->meta_title . '"';
        else return 'title="' . $model->name . '"';
    }

    protected static function preparePageOld(Model $model): array
    {
        if(empty($model->path)) $path = Str::slug($model->name);
        else $path = $model->path;
        $pagePath = resource_path('views/websites/website_' . $model->website->slug . '/pages/' . $path . '.blade.php');

        $websiteModulePath = PathService::getModulePath('Website');
        $pageStubPath = "{$websiteModulePath}/Data/Stubs/page.stub";

        $pageStub = file_get_contents($pageStubPath);

        $layoutName = 'websites.website_' . $model->website->slug . '.layouts.' . $model->layout->path;

        $content = str_replace(
            [
                '{{ layoutName }}',
                '{{ metaTitle }}',
                '{{ metaDescription }}',
                '{{ robotIndex }}',
                '{{ robotFollow }}',
                '{{ canonicalUrl }}',
                '{{ content }}'
            ],
            [
                $layoutName,
                $model->meta_title ?? $model->name,
                $model->meta_description,
                $model->robot_index ?? 'index',
                $model->robot_follow ?? 'follow',
                $model->canonical_url ?? url($model->url),
                $model->content
            ],
            $pageStub
        );

        return [$pagePath, $content];
    }

    protected static function prepareAsset(Model $model): array
    {
        if(empty($model->path)) $path = Str::slug($model->name);
        else $path = $model->path;
        $layoutPath = resource_path('views/websites/website_' . $model->website->slug . '/assets/' . $model->type . '/' . $path . '.' . $model->type);
        $content = $model->content;

        return [$layoutPath, $content];
    }

}
