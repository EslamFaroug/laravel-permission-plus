<?php


namespace EslamFaroug\PermissionPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use EslamFaroug\PermissionPlus\Traits\HasTranslatable;

class Permission extends Model
{
    use HasTranslatable;

    protected $fillable = [
        'name',
        'key',
        'permission_guard_id'
    ];

    /**
     * The attributes that are translatable.
     */
    protected array $translatable = ['name'];


    protected $casts = [
        'name' => 'array'
    ];

    public function permissionGuard(): BelongsTo
    {
        return $this->belongsTo(PermissionGuard::class, 'permission_guard_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_assignments',
            'permission_id',
            'role_id'
        );
    }
}
