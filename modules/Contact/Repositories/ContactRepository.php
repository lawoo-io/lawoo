<?php

namespace Modules\Contact\Repositories;

use Modules\Contact\Models\Contact;
use Modules\Core\Abstracts\BaseRepository;

class ContactRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Contact());
    }
}
