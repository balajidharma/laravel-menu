<?php

namespace BalajiDharma\LaravelMenu\Traits;

if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
    trait SpatiePermission
    {
        use \Spatie\Permission\Traits\HasPermissions, \Spatie\Permission\Traits\HasRoles;

        public $hasSpatiePermission = true;

        protected $guard_name = 'web';
    }
} else {
    trait SpatiePermission
    {
        public $hasSpatiePermission = false;
    }
}
