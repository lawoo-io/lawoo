<?php

namespace Modules\Core\Services\Makes;

use Illuminate\Support\Facades\File;

class MakeModule
{

    public static function run(string $name): array
    {

        $manifestPath = base_path("modules/{$name}/manifest.json");
        $spPath = base_path("modules/{$name}/Providers/{$name}ServiceProvider.php");
        $stubDir = base_path('modules/Core/Console/Stubs');

        $manifestStubPath = "{$stubDir}/manifest.stub";
        $spStubPath = "{$stubDir}/service-provider.stub";

        $messages = '';
        $type = 'success';

        // === Create Module manifest ===
        if (!File::exists($manifestPath)) {
            $manifestStub = file_get_contents($manifestStubPath);

            $filledManifest = str_replace(['{{name}}'], [$name], $manifestStub);

            if (!is_dir(dirname($manifestPath))) {
                mkdir(dirname($manifestPath), 0755, true);
            }

            file_put_contents($manifestPath, $filledManifest);
            $messages .= "\n✅ Module with manifest file created: {$manifestPath}";
        } else {
            $messages .= "\n⚠️ Module with manifest file already exists: {$manifestPath}";
            $type = 'info';
        }

        // === Create ServiceProvider ===
        if (!File::exists($spPath)) {
            $spStub = file_get_contents($spStubPath);

            $filledSp = str_replace(['{{name}}'], [$name], $spStub);

            if (!is_dir(dirname($spPath))) {
                mkdir(dirname($spPath), 0755, true);
            }

            file_put_contents($spPath, $filledSp);
            $messages .= "\n✅ ServiceProvider file created: {$spPath}";
        } else {
            $messages .= "\n⚠️ ServiceProvider already exists: {$spPath}";
            $type = 'info';
        }

        return [
            'type' => $type,
            'messages' => $messages
        ];
    }

}
