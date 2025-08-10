<?php


namespace EslamFaroug\PermissionPlus\Models;

use Illuminate\Database\Eloquent\Model;
use EslamFaroug\PermissionPlus\Traits\HasTranslatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGuard extends Model
{
    use HasTranslatable;

    protected $fillable = [
        'name',
        'key',
    ];

    /**
     * The attributes that are translatable.
     */
    protected array $translatable = ['name'];


    protected $casts = [
        'name' => 'array'
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'permission_guard_id');
    }
}
