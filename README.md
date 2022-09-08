# `BrekiTomasson\LaravelModelFinder`

A simple trait that makes building your own custom Laravel Model Searches a lot easier and safer. It ensures that your search criteria match one, and only
one, result in the table being searched, allowing you to comfortably type-hint your methods by ensuring that the result of a query will never be `null` or a
`Collection`, but always an instance of the specific Model you're querying. It does this by searching for a row that **uniquely** contains the data you're 
looking for. 

> See `CHANGELOG.md` for more information about version compatibility in case you are running an older version of PHP and/or Laravel.
> > The most recently released version (`v1.0.0`) of this package is built for PHP `v8.0` or greater and Laravel `v9.0` or greater.

## Installation

At the moment, no configuration files are required, although this may be introduced in future versions. To use this library, all you need to do is require
the package in the root directory of your Laravel project:

```shell
composer require brekitomasson/laravel-model-finder
```

## Usage

There are two main ways to use this package; either by (1) building your own Finder-classes or by (2) powering up your models. I recommend the first method, as
it allows for a cleaner separation of concerns and is more feature-complete, but I will describe both ways below and allow you to decide for yourself. For 
both examples, I will be showing the functionality by using a hypothetical `Country` model, but the system works for absolutely any Laravel-powered model.

For the sake of argument, assume the `Country` model contains the following attributes:

- `int $id The database ID of the country.`
- `string $name The official name of the country.`
- `string $short_name A shortened or every-day version of the country's name.`
- `int $numeric The numeric code assigned to this country.`
- `string $alpha2 The two-letter ISO-3166 code for the country.`
- `string $alpha3 The three-letter ISO-3166 code for the country.`

An example of an entry from the `Country` model might therefore look like this:

```json
{
  "id": 213,
  "name": "The Republic of Korea",
  "short_name": "South Korea",
  "numeric": 410,
  "alpha2": "KR",
  "alpha3": "KOR"
}
```

### Method 1: Building Your Own Finder-Classes

Let's create a new `CountryFinder` in the `App\Tools` namespace. It doesn't need to extend or implement any other classes, but it needs to `use` the
`CanFindModelEntries` trait. Depending on your IDE, this will then inform you that you have a method stub for the static method `find` that needs to be
implemented, so let's do that.

Now, since PHP doesn't natively support abstract keywords in their Traits (Seriously, PHP; what's up with that?), we have to implement the next two things
manually. They are the `protected static array $queryKeys` and the `protected static Model|string $queryModel`. You populate these with (`$queryKeys`) the
list of attributes to be searched and (`$queryModel`) the FQN (including `::class`) of the Model being searched. At this point, the file should look
something like this:

```php
<?php

declare(strict_types = 1);

namespace App\Tools;

use BrekiTomasson\LaravelModelFinder\CanFindModelEntries;
use Illuminate\Database\Eloquent\Model;

class CountryFinder
{
    use CanFindModelEntries;
    
    public static array $query_keys = ['name', 'short_name', 'alpha2', 'alpha3'];

    protected static Model|string $query_model = \App\Models\World\Country::class;

    public static function find(mixed $value) : Model
    {
        // TODO: Implement find() method.
    }
}
```

The next step is to implement the `find(mixed $value)` method. The way you implement the `find(mixed $value)` is fairly standardized, but has been left to
the user so that any customization or tweaking can be done here. A fairly standard implementation - with inline comments for added clarification - will
look something like this. Note that I've updated the return type from `Model` to `Country`. This is allowed since `Country` extends `Model`, making this a 
more specific version of the stub method definition:

```php
public static function find(mixed $value) : Country
{
    // First, we 'clean' the value by removing any trailing or leading spaces, squishing extra spaces down to
    // single spaces, casting the result to string, etc.
    $valueObject = new ValueObject($value);
    
    // Then, we generate the Cache Helper using the Value Object we've just generated. This will allow us to query
    // the cache for the search criteria you've entered. Under the hood, the CacheHelper uses the name of the model
    // we defined in `$query_model` to generate the cache tags, allowing us to avoid conflicts when searching for
    // the same value in multiple models.
    $cache = self::getCacheHelper($valueObject);

    // If we've already got the results for this search in our cache, we can return it as is. Since the ValueObject that
    // the CacheHelper was constructed using is case-insensitive, this means that a search for "America" will return a
    // cached value if you previously got a search result for "america".
    if ($cache->exists()) {
        return $cache->get();
    }

    // Perform the search and store the result in $result. This will throw a ModelNotFoundException if no result is
    // found or a MultipleRecordsFoundException if two or more results are found. If no exception is thrown, we can be
    // certain that $result contains a single entry from your Model.
    $result = self::searchInModel($valueObject);

    // Return the $result, ensuring we also store it in the cache for future use.
    return $cache->put($result);
}
```

Once you've implemented the `find` method with the relevant contents, you should be able to `CountryFinder::find('sweden')`, and it will return an entry from 
your `Country` Model where the string  `'sweden'` is found in one of the four columns you named in the `$query_keys` array. You can also call methods or 
reference attributes directly from the returned object. Getting the official name of Iceland, for example, would be as easy as calling something like 
`CountryFinder::find('is')->name`, as `'is'` is a unique identifier for the `alpha2` property.

### Method 2: Powering Up Your Models

Please read through Method 1 first to understand a little more about how this package works under the hood, as that will help you understand this method of 
implementation better.

This way of implementing the package does not require you to create an entirely new class. Instead, all you need to do is `use CanBeSoleSearched` inside of the 
`Model` you wish to be searchable and define the required attribute, `public static array $query_keys`. In this case, you do not need to define the 
attribute `$query_model`, as this will be inherently defined based on the Model that you're using the `CanBeSoleSearched` trait in.

After that is done, you should be able to call `Country::findSole('taiwan')`, for example, and it will scan the columns defined in `$query_keys` and return 
a single result, if available. You can, of course, define your own `findSole` method that overwrites the one defined in the trait, but this is usually not 
necessary.

## Digging Deeper

This package is very much "what you see is what you get", with few bells and whistles under the hood. That said, there are two methods available that are 
not part of normal operations and which allow you to clear the caches defined by this package.

Calling the static method `clearClassCache()` on any class that uses either the `CanFindModelEntries` or the `CanBeSoleSearched`  traits will clear all 
caches related to that particular class. Calling the static method `clearModelFinderCache()` on any class that uses either the  `CanFindModelEntries` or the 
`CanBeSoleSearched` will clear all caches related to **all** models that are searched by this package. However, as the cache is never updated unless a 
single model is found, calling these methods is usually not required, but they exist in case you have a need for them.

## Potential Problems

Due to the way this package works, it should work "out of the box" for about 99% of your use cases (but don't quote me on that). However, there are a couple 
of scenarios where the package will not work as intended. If you're having any problems with the package, please check `TROUBLESHOOTING.md` before opening 
an issue, as it is quite likely that your question is answered there.

## Future Development / Backlog

Here are a number of features/functionality that I want to implement in this package. Pull Requests are always welcome, of course.

- Create a `model-finder.php` configuration file that can be published, allowing the user to set a number of defaults, such as whether to use caching or not,
  how long results should be stored in the cache, as well as a number of tweaks and adjustments to how the `ValueObject` is filtered.
- Update the way the cache key is generated to use the database table's name rather than the Model's class name.
- Explore the need to create a `ServiceProvider` for the package. At the moment, I can't think of any reason to include anything like that, but maybe there
  is value in providing some kind of Dependency Injectable `ModelFinder` object of some sort? Not sure about this one...
- Some "internal" code (`ValueObject` and `DataObject`, for example) feels like it could be cleaned up.
- Some kind of Laravel-native hooks, Observers and/or events to clear the cache when models are updated is probably in order, but I don't want to clear the
  *entire* cache for any given model just because one row has been added or removed. This will have to be explored.
- Explore compatibility with Laravel `v8.x` - am I doing anything in this package that is exclusive to `v9.x`, or can I add support for `v8.x` without
  rewriting anything?
- Test this package with other database providers and "pseudo-databases" like `Sushi` to ensure it works as intended.

## Copyright, License and Permissions

This package is released under the MIT license. See the `LICENSE` for more details.
