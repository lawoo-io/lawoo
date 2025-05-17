<?php

namespace Modules\Core\Services\Schemas;

use Illuminate\Support\Facades\File;
use Modules\Core\Models\YamlFile;
use Symfony\Component\Yaml\Yaml;

class SchemaChecker
{

    public static function run(string $module): array
    {
        $path = base_path('modules') . '/' . $module . '/Database/Schemas';

        if (!is_dir($path)) {
            return [
                'type' => 'error',
                'message' => 'Directory' . $path . ' does not exist.',
            ];
        }

        $ymlFiles = File::allFiles($path);

        $count = 0;

        foreach ($ymlFiles as $file) {
            if ($file->getExtension() !== 'yml' && $file->getExtension() !== 'yaml') continue;

            $parsed = Yaml::parse(File::get($file->getPathname()));

            $result = YamlFile::updateOrCreate($file, $parsed, $module);

            if ($result > 0) $count++;

        }

        if ($count > 0) {
            return [
                'type' => 'success',
                'message' => $count . ' schemas have been updated or created.',
            ];
        }

        return [
            'type' => 'info',
            'message' => 'The schemas have not been changed.',
        ];
    }

}
