<h1 align="center">Laravel Menu</h1>
<h3 align="center">Create database based Menu and Menu items to your Laravel projects.</h3>
<p align="center">
<a href="https://packagist.org/packages/balajidharma/laravel-menu"><img src="https://poser.pugx.org/balajidharma/laravel-menu/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/balajidharma/laravel-menu"><img src="https://poser.pugx.org/balajidharma/laravel-menu/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/balajidharma/laravel-menu"><img src="https://poser.pugx.org/balajidharma/laravel-menu/license" alt="License"></a>
</p>

## Table of Contents

- [Installation](#installation)
- [Demo](#demo)
- [Create Menu](#create-menu)
- [Create Menu Item](#create-menu-item)
- [Create multiple Menu Items](#create-multiple-menu-items)
- [Menu Tree](#menu-tree)
- [Menu Link Tokens](#menu-link-tokens)

## Installation
- Install the package via composer
```bash
composer require balajidharma/laravel-menu
```
- Publish the migration and the config/menu.php config file with
```bash
php artisan vendor:publish --provider="BalajiDharma\LaravelMenu\MenuServiceProvider"
```
- Run the migrations
```bash
php artisan migrate
```

## Demo
The "[Basic Laravel Admin Penel](https://github.com/balajidharma/basic-laravel-admin-panel)" starter kit come with Laravel Menu

## Create Menu
```php

use BalajiDharma\LaravelMenu\Models\Menu;

Menu::create([
    'name' => 'Main Menu',
    'machine_name' => 'main_menu',
    'description' => 'Site main menu',
]);
```

## Create Menu Item
```php

use BalajiDharma\LaravelMenu\Models\Menu;
use BalajiDharma\LaravelMenu\Models\MenuItem;

$menu = Menu::create([
    'name' => 'Main Menu',
    'machine_name' => 'main_menu',
    'description' => 'Site main menu'
]);

$menu->menuItems()->create([
    'name' => 'Home',
    'uri' => '/',
    'enabled' => 1,
    'parent_id' => null,
    'weight' => 0
]);

```

## Create multiple Menu Items
```php
$menu = Menu::create([
    'name' => 'Admin',
    'machine_name' => 'admin',
    'description' => 'Admin Menu',
]);

$menu_items = [
    [
        'name'      => 'Dashboard',
        'uri'       => '/dashboard',
        'enabled'   => 1,
        'weight'    => 0,
    ],
    [
        'name'      => 'Permissions',
        'uri'       => '/<admin>/permission',
        'enabled'   => 1,
        'weight'    => 1,
    ],
    [
        'name'      => 'Roles',
        'uri'       => '/<admin>/role',
        'enabled'   => 1,
        'weight'    => 2,
    ],
    [
        'name'      => 'Users',
        'uri'       => '/<admin>/user',
        'enabled'   => 1,
        'weight'    => 3,
    ],
    [
        'name'      => 'Menus',
        'uri'       => '/<admin>/menu',
        'enabled'   => 1,
        'weight'    => 4,
    ]
];

$menu->menuItems()->createMany($menu_items);
```

## Menu Tree
- Get menu tree by using menu id
```php
use BalajiDharma\LaravelMenu\Models\MenuItem;

$items = (new MenuItem)->toTree($menu->id);
```

- Get menu tree by using menu machine name
```php
use BalajiDharma\LaravelMenu\Models\Menu;

$items = Menu::getMenuTree('admin');
```

## Menu Link Tokens
- Enter `<admin>` to add admin prefix to the link.
- Enter `<nolink>` for non link menu.

Get the generated uri on `link` attribute
```php
use BalajiDharma\LaravelMenu\Models\MenuItem;


MenuItem::find(1)->link;
```