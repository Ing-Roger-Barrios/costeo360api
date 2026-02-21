<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionRecursoRegion extends Model
{
    protected $table = 'version_recurso_region';

    protected $fillable = [
        'version_id',
        'region_id',
        'obra_recurso_maestro_id',
        'precio_version_regional'
    ];
}
