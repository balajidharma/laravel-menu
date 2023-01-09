<?php

namespace BalajiDharma\LaravelMenu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class MenuItem extends Model
{
    use HasRecursiveRelationships;

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

    public static function dropdown($menu_id, $item_id = null) {
        $result = [];
        $query = MenuItem::where('menu_id', $menu_id);
        if($item_id) {
            $query->where(function ($query) use ($item_id) {
                $query->where('parent_id', '!=', $item_id)->orWhereNull('parent_id');
            });
        }
        $query->orderBy('weight');
        $items = $query->get();
        self::buildDropdownTree($items, $result);
        return $result;
    }
    
    public static function tree($menu_id)
    {
        return MenuItem::where('menu_id', $menu_id)->tree()->breadthFirst()->get()->toTree();
    }

    protected static function buildDropdownTree($menu_items, &$result, $parent_id = 0, $depth = 0)
    {
        $items = $menu_items->filter(function ($item) use ($parent_id) {
            return $item->parent_id == $parent_id;
        });

        foreach ($items as $item)
        {
            $result[$item->id] = str_repeat('-', $depth) . $item->name;
            self::buildDropdownTree($menu_items, $result, $item->id, $depth + 1);
        }
    }
}
