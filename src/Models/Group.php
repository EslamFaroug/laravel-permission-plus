<?php


namespace EslamFaroug\PermissionPlus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use EslamFaroug\PermissionPlus\Traits\HasTranslatable;

class Group extends Model
{
    use HasTranslatable;

    protected $fillable = [
        'name',
        'key',
        'description'
    ];

    /**
     * The attributes that are translatable.
     */
    protected array $translatable = ['name', 'description'];


    protected $casts = [
        'name' => 'array',
        'description' => 'array'
    ];

    // Users/Clients/Employees in this group (polymorphic)
    public function members(): MorphToMany
    {
        return $this->morphToMany(
            null, // Will be set by trait on the user model
            'groupable',
            'groupables',
            'group_id',
            'groupable_id'
        );
    }

    // Roles assigned to this group
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            Role::class,
            'groupable',
            'groupables',
            'group_id',
            'groupable_id'
        );
    }
}
