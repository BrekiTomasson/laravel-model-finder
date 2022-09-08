<?php

namespace BrekiTomasson\LaravelModelFinder;

use BrekiTomasson\LaravelModelFinder\DataObjects\ValueObject;
use BrekiTomasson\LaravelModelFinder\Traits\ModelFinderShared;
use Illuminate\Database\Eloquent\Model;

trait CanBeSoleSearched
{
    use ModelFinderShared;

    public static function findSole(mixed $value) : Model
    {
        $valueObject = new ValueObject($value);

        $cache = self::getCacheHelper($valueObject);

        if ($cache->exists()) {
            return $cache->get();
        }

        $result = self::searchInModel($valueObject);

        return $cache->put($result);
    }
}
