<?php

namespace App\Services\Contacts;

use App\Contracts\ContactSourceContract;
use InvalidArgumentException;

class ContactSourceRegistry
{

    private array $sources = [];


    public function __construct(iterable $sources)
    {
        foreach ($sources as $source) {
            $this->sources[$source->key()] = $source;
        }
    }

    public function resolve(string $source): ContactSourceContract
    {
        if (! isset($this->sources[$source])) {
            throw new InvalidArgumentException("Unsupported contact source [{$source}].");
        }

        return $this->sources[$source];
    }
}
