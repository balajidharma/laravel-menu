<?php

namespace BalajiDharma\LaravelMenu\Exceptions;

use InvalidArgumentException;

class MenuAlreadyExists extends InvalidArgumentException
{
    public static function create(string $machineName)
    {
        return new static("A `{$machineName}` permission already exists.");
    }
}