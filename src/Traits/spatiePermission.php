<?php

namespace BalajiDharma\LaravelMenu\Traits;

if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
    trait spatiePermission
    {
        use \Spatie\Permission\Traits\HasRoles, \Spatie\Permission\Traits\HasPermissions;
        public $hasSpatiePermission = true;

        protected $guard_name = 'web';
    }
} else {
    trait spatiePermission
    {
        public $hasSpatiePermission = false;
    }
}