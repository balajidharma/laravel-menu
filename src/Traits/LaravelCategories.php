<?php

namespace BalajiDharma\LaravelMenu\Traits;

if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
    trait LaravelCategories
    {
        use \BalajiDharma\LaravelCategory\Traits\HasCategories;

        public $hasLaravelCategories = true;
    }
} else {
    trait LaravelCategories
    {
        public $hasLaravelCategories = false;
    }
}
