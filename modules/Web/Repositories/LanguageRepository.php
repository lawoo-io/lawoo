<?php

namespace Modules\Web\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Abstracts\BaseRepository;
use Modules\Core\Models\Language;

class LanguageRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Language());
    }

    public static function getActive(): array
    {
        return Language::where('is_active', true)->get()->map(function (Language $language) {
            return [
                'name' => $language->name,
                'id' => $language->id,
            ];
        })->toArray();
    }
}
