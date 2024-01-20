<?php

namespace BalajiDharma\LaravelMenu\Traits;

use BalajiDharma\LaravelMenu\Exceptions\InvalidParent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

trait MenuTree
{
    /**
     * @var \Closure
     */
    protected $queryCallback;

    /**
     * Get children of current node.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, $this->getParentColumn());
    }

    /**
     * Get parent of current node.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, $this->getParentColumn());
    }

    /**
     * GET all parents.
     *
     * @return \Illuminate\Support\Collection
     */
    public function parents()
    {
        $parents = collect([]);

        $parent = $this->parent;

        while (! is_null($parent)) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    /**
     * @return string
     */
    public function getParentColumn()
    {
        if (property_exists($this, 'parentColumn')) {
            return $this->parentColumn;
        }

        return 'parent_id';
    }

    /**
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->{$this->getParentColumn()};
    }

    /**
     * Set parent column.
     *
     * @param  string  $column
     */
    public function setParentColumn($column)
    {
        $this->parentColumn = $column;
    }

    /**
     * Get title column.
     *
     * @return string
     */
    public function getTitleColumn()
    {
        if (property_exists($this, 'titleColumn')) {
            return $this->titleColumn;
        }

        return 'name';
    }

    /**
     * Set title column.
     *
     * @param  string  $column
     */
    public function setTitleColumn($column)
    {
        $this->titleColumn = $column;
    }

    /**
     * Get order column name.
     *
     * @return string
     */
    public function getOrderColumn()
    {
        if (property_exists($this, 'orderColumn')) {
            return $this->orderColumn;
        }

        return 'weight';
    }

    /**
     * Set order column.
     *
     * @param  string  $column
     */
    public function setOrderColumn($column)
    {
        $this->orderColumn = $column;
    }

    /**
     * @return string
     */
    public function getMenuRelationColumn()
    {
        if (property_exists($this, 'menuRelationColumn')) {
            return $this->menuRelationColumn;
        }

        return 'menu_id';
    }

    /**
     * Set menu relation column.
     *
     * @param  string  $column
     */
    public function setMenuRelationColumn($column)
    {
        $this->menuRelationColumn = $column;
    }

    /**
     * Set query callback to model.
     *
     *
     * @return $this
     */
    public function withQuery(?\Closure $query = null)
    {
        $this->queryCallback = $query;

        return $this;
    }

    /**
     * Format data to tree like array.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toTree($menuId, $includeDisabledItems = false, $checkPermission = false)
    {
        return $this->buildNestedItems($menuId, $includeDisabledItems, $checkPermission);
    }

    /**
     * Build Nested array.
     *
     * @param  int  $parentId
     * @return \Illuminate\Support\Collection
     */
    protected function buildNestedItems($menuId, $includeDisabledItems = false, $checkPermission = false, $nodes = null, $parentId = 0)
    {
        $branch = collect();

        if (empty($nodes)) {
            $nodes = $this->allNodes($menuId, null, $includeDisabledItems);
        }
        $nodes->each(function ($node) use ($menuId, $nodes, $includeDisabledItems, $checkPermission, $parentId, &$branch) {
            $hasPermission = true;
            $parentColumn = $this->getParentColumn();
            $keyName = $this->getKeyName();

            if ($checkPermission && ! $this->checkHasPermission($node)) {
                $hasPermission = false;
            }
            if ($parentId == $node->$parentColumn && $hasPermission) {
                $children = $this->buildNestedItems($menuId, $includeDisabledItems, $checkPermission, $nodes, $node->$keyName);
                if ($children) {
                    $node->children = $children;
                }

                $branch->push($node);
            }
        });

        return $branch;
    }

    /**
     * Get all elements.
     *
     * @return mixed
     */
    public function allNodes($menuId, $ignoreItemId = null, $includeDisabledItems = false)
    {
        $self = new static();

        if ($this->queryCallback instanceof \Closure) {
            $self = call_user_func($this->queryCallback, $self);
        }

        if ($ignoreItemId) {
            return $self->where($this->getMenuRelationColumn(), $menuId)
                ->where(function ($query) use ($ignoreItemId) {
                    $query->where($this->getParentColumn(), '!=', $ignoreItemId)->orWhereNull($this->getParentColumn());
                })
                ->when(! $includeDisabledItems, function ($query) {
                    $query->where('enabled', true);
                })
                ->when($this->hasSpatiePermission, function ($query) {
                    $query->with('roles');
                })
                ->orderBy($this->getOrderColumn())->get();
        }

        return $self->where($this->getMenuRelationColumn(), $menuId)
            ->when(! $includeDisabledItems, function ($query) {
                $query->where('enabled', true);
            })
            ->when($this->hasSpatiePermission, function ($query) {
                $query->with('roles');
            })
            ->orderBy($this->getOrderColumn())->get();
    }

    /**
     * Get options for Select field in form.
     *
     * @param  string  $rootText
     * @return array
     */
    public static function selectOptions($menuId, $ignoreItemId = null, $includeDisabledItems = false, ?\Closure $closure = null)
    {
        $options = (new static())->withQuery($closure)->buildSelectOptions($menuId, $ignoreItemId, $includeDisabledItems);

        return collect($options)->all();
    }

    /**
     * Build options of select field in form.
     *
     * @param  int  $parentId
     * @param  string  $prefix
     * @param  string  $space
     * @return array
     */
    protected function buildSelectOptions($menuId, $ignoreItemId, $includeDisabledItems = false, $nodes = null, $parentId = 0, $prefix = '', $space = '&nbsp;')
    {
        $prefix = $prefix ?: '┝'.$space;

        $options = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes($menuId, $ignoreItemId, $includeDisabledItems);
        }

        $nodes->each(function ($node) use ($menuId, $nodes, $includeDisabledItems, $parentId, $prefix, $space, &$options) {
            $parentColumn = $this->getParentColumn();
            $keyName = $this->getKeyName();
            $titleColumn = $this->getTitleColumn();
            if ($parentId == $node->$parentColumn) {
                $node->$titleColumn = $prefix.$space.$node->$titleColumn;

                $childrenPrefix = str_replace('┝', str_repeat($space, 6), $prefix).'┝'.str_replace(['┝', $space], '', $prefix);

                $children = $this->buildSelectOptions($menuId, null, $includeDisabledItems, $nodes, $node->$keyName, $childrenPrefix);

                $options[$node->$keyName] = $node->$titleColumn;

                if ($children) {
                    $options += $children;
                }
            }
        });

        return $options;
    }

    /**
     * Build the link based on uri
     */
    protected function getLinkAttribute()
    {
        $uri = trim($this->uri);

        if (strpos($uri, '<nolink>') !== false) {
            $uri = '';
        }

        if (strpos($uri, '<admin>') !== false) {
            $uri = str_replace('<admin>', config('admin.prefix', 'admin'), $uri);
        }

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $parentColumn = $this->getParentColumn();
        $newParent = $this->$parentColumn ?? null;
        $this->where($this->getParentColumn(), $this->getKey())->update([$this->getParentColumn() => $newParent]);

        return parent::delete();
    }

    public function initializeMenuTree()
    {
        $this->appends = array_unique(array_merge($this->appends, ['link']));
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $branch) {
            $parentColumn = $branch->getParentColumn();

            if (Request::filled($parentColumn) && Request::input($parentColumn) == $branch->getKey()) {
                throw InvalidParent::create();
            }

            return $branch;
        });
    }

    protected function checkHasPermission($menuItem)
    {
        if (! $this->hasSpatiePermission) {
            return true;
        }
        $roles = $menuItem->roles;

        if ($roles->isEmpty()) {
            return true;
        }
        $user = auth()->user();
        if ($user) {
            return $user->hasAnyRole($roles);
        }

        return false;
    }
}
