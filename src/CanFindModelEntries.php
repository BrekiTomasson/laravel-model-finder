<?php

declare(strict_types = 1);

namespace BrekiTomasson\LaravelModelFinder;

use BrekiTomasson\LaravelModelFinder\Traits\ModelFinderShared;
use Illuminate\Database\Eloquent\Model;

trait CanFindModelEntries
{
    use ModelFinderShared;

    abstract public static function find(mixed $value) : Model;
}
