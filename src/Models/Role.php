<?php


namespace EslamFaroug\PermissionPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use EslamFaroug\PermissionPlus\Traits\HasTranslatable;

class Role extends Model
{
    use HasTranslatable;

    protected $fillable = [
        'name',
        'key'
    ];

    /**
     * The attributes that are translatable.
     */
    protected array $translatable = ['name'];


    protected $casts = [
        'name' => 'array'
    ];

    // Permissions assigned to this role
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_assignments',
            'role_id',
            'permission_id'
        );
    }

    // Groups that have this role
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(
            Group::class,
            'assignable',
            'permission_assignments',
            'assignable_id',
            'role_id'
        );
    }
}
