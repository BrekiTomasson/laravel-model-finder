<?php

declare(strict_types = 1);

namespace BrekiTomasson\LaravelModelFinder\Traits;

use BrekiTomasson\LaravelModelFinder\DataObjects\CacheHelper;
use BrekiTomasson\LaravelModelFinder\DataObjects\ValueObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait ModelFinderShared
{
    /**
     * Clear all cached searches for the connected Model.
     */
    protected static function clearClassCache() : void
    {
        Cache::tags(Str::snake(class_basename(self::$queryModel)))->flush();
    }

    /**
     * Clear all cached searches for all Models made searchable with the LaravelModelFinder package.
     *
     * @todo Read this field's name from a configuration file rather than hard-coding it here.
     */
    protected static function clearModelFinderCache() : void
    {
        Cache::tags('laravel-model-finder')->flush();
    }

    protected static function getCacheHelper(ValueObject $valueObject) : CacheHelper
    {
        return new CacheHelper($valueObject, self::$queryModel);
    }

    /**
     * @throws ModelNotFoundException<Model>
     * @throws MultipleRecordsFoundException
     */
    private static function searchInModel(ValueObject $value) : Collection|Model
    {
        $query = self::$queryModel::query();

        foreach (self::$queryKeys as $key) {
            $query->orWhere($key, 'ilike', $value->getValue());
        }

        return $query->sole();
    }

}
