<?php

namespace App\Models;

use Awcodes\Curator\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Media extends BaseMedia
{
    use HasUuids;
}
