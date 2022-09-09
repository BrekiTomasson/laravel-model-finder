<?php

declare(strict_types = 1);

namespace BrekiTomasson\LaravelModelFinder\DataObjects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CacheHelper
{
    /** @var string the snake-case version of the class name, used to generate the cache key */
    private string $class;

    /** @var string a lower-case, trimmed, snake-cased version of the search string, used to generate the cache key */
    private string $search;

    /**
     * @todo move $cache_duration to the config file rather than setting it in the constructor.
     */
    public function __construct(ValueObject $search, Model|string $class, protected int $cache_duration = 86_400)
    {
        $this->search = $search->getStringableValue()->lower()->snake()->toString();

        $this->class = Str::snake(class_basename($class));
    }

    public function exists() : bool
    {
        return Cache::tags($this->getCacheTags())->has($this->getCacheKey());
    }

    public function get()
    {
        return Cache::tags($this->getCacheTags())->get($this->getCacheKey());
    }

    public function put(Model $result)
    {
        Cache::tags($this->getCacheTags())->remember(
            $this->getCacheKey(),
            $this->cache_duration,
            static fn () => $result,
        );

        return $result;
    }

    private function getCacheKey() : string
    {
        return $this->search;
    }

    /**
     * Returns an array containing the default cache tag for the package and the class-based cache tag.
     *
     * @todo Make the default cache key be customizable by the user through a configuration file, both here and in ModelFinderShared.
     *
     * @return string[]
     */
    private function getCacheTags() : array
    {
        return ['laravel-model-finder', $this->class];
    }
}
