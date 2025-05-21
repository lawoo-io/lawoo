<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\YamlFile;

class YamlFileRepository extends BaseRepository
{

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct(new YamlFile);
    }

    /**
     * Get or create a YamlFile model record from a given file path.
     *
     * If the file is new or has changed (based on modification time or hash),
     * the model will be created or updated accordingly.
     *
     * @param object $file     SplFileInfo or similar file object
     * @param int $moduleId    ID of the associated module
     * @return YamlFile|int    Returns the YamlFile model or 0 if nothing changed
     */
    public function getOrCreate(object $file, int $moduleId): YamlFile|int
    {
        $path = str_replace(base_path() . '/', '', $file->getPathname());

        $yamlFile = $this->model->where('path', $path)->first();

        $changed = false;
        if (!$yamlFile) {
            $yamlFile = new $this->model;
            $yamlFile->path = $path;
            $yamlFile->file_modified_at = $file->getMTime();
            $yamlFile->file_hash = hash_file('md5', $file->getPathname());
            $yamlFile->module_id = $moduleId;
            $yamlFile->save();
            $changed = true;
        } elseif ($yamlFile->file_modified_at < $file->getMTime() || $yamlFile->file_hash !== hash_file('md5', $file->getPathname())) {
            $yamlFile->file_modified_at = $file->getMTime();
            $yamlFile->file_hash = hash_file('md5', $file->getPathname());
            $yamlFile->save();
            $changed = true;
        }

        if (!$changed) return 0;
        return $yamlFile;
    }
}
