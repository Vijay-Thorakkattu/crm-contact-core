<?php

namespace App\DTOs;

class ContactPayload
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly string $source,
        public readonly ?int $sourceId = null,
    ) {
    }
}
