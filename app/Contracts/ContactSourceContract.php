<?php

namespace App\Contracts;

use App\DTOs\ContactPayload;

interface ContactSourceContract
{
    public function key(): string;
    
    public function toPayload(array $input): ContactPayload;
}
