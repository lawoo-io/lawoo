<?php

namespace Modules\Core\Services\Schemas;

use Illuminate\Support\Facades\File;
use Modules\Core\Models\YamlFile;
use Modules\Core\Repositories\DbModelRepository;
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

            if (!$parsed) throw new \RuntimeException('YAML parse error: ' . $file->getPathname());

            // Check file structure and base with Table
            static::checkFieldsAndModel($parsed);

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

    /**
     * Check file structure and if base = 0 the DB Table
     * @param array $data
     * @return void
     */
    public static function checkFieldsAndModel(array $data): void
    {
        if (
            count($data) === 1 &&
            is_string(array_key_first($data))
        ) {
            $firstKey = array_key_first($data);
            $firstValue = $data[$firstKey];

            if (
                is_array($firstValue) &&
                array_key_exists('base', $firstValue) &&
                array_key_exists('fields', $firstValue)
            ) {
                // Check ExtendedTable
                if (!$firstValue['base']) static::checkExtendedTable($firstKey);

                if (!$firstValue['fields']) throw new \RuntimeException('Missing fields: ' . $firstKey);

            } else {
                throw new \RuntimeException('Missing "base" or "fields" in the schema: ' . $firstKey);
            }
        } else {
            throw new \RuntimeException('Invalid top-level structure (must have one named key)');
        }
    }

    /**
     * Check extended Table in the DB
     * @param string $name
     */
    public static function checkExtendedTable(string $name): void
    {
        $dbModelRep = app(DbModelRepository::class);

        if (!$dbModelRep->tableExists($name)) {
            throw new \RuntimeException('Table ' . $name . ' does not exist. Please set 1 by base to create a table.');
        }
    }

}
