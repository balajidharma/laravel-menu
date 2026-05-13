<?php

namespace BalajiDharma\LaravelMenu\Exceptions;

use InvalidArgumentException;

class InvalidParent extends InvalidArgumentException
{
    public static function create()
    {
        return new static('Invalid Parent');
    }
}
