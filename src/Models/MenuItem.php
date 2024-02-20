<?php

namespace BalajiDharma\LaravelMenu\Models;

use BalajiDharma\LaravelMenu\Traits\MenuTree;
use BalajiDharma\LaravelMenu\Traits\SpatiePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use MenuTree {
        MenuTree::boot as treeBoot;
    }
    use SpatiePermission;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function getTable()
    {
        return config('menu.table_names.menu_items', parent::getTable());
    }

    public function menu(): BelongsTo
    {
        return $this->BelongsTo(config('menu.models.menu'));
    }

    public function setWeightAttribute($weight)
    {
        $this->attributes['weight'] = $weight ?? 0;
    }
}
