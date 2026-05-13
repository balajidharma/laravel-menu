<?php

namespace BalajiDharma\LaravelMenu\Traits;

if (class_exists(\BalajiDharma\LaravelCategory\CategoryServiceProvider::class)) {
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
