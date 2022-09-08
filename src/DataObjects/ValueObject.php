<?php

declare(strict_types = 1);

namespace BrekiTomasson\LaravelModelFinder\DataObjects;

use Illuminate\Support\Stringable;

class ValueObject
{
    private string $value;

    public function __construct(Stringable|float|int|string $value)
    {
        if ($value instanceof Stringable) {
            $value = $value->toString();
        }

        $this->value = str(html_entity_decode((string) $value))->squish()->toString();
    }

    public function getStringableValue() : Stringable
    {
        return str($this->value);
    }

    public function getValue() : string
    {
        return $this->value;
    }
}
