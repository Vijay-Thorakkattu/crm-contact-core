<?php

namespace App\Services\Contacts\Sources;

use App\Contracts\ContactSourceContract;
use App\DTOs\ContactPayload;
use InvalidArgumentException;

class LeadContactSource implements ContactSourceContract
{
    public function key(): string
    {
        return 'lead';
    }

    public function toPayload(array $input): ContactPayload
    {
        $firstName = (string) ($input['first_name'] ?? '');
        $lastName = (string) ($input['last_name'] ?? '');

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('Lead source requires first_name and last_name.');
        }

        return new ContactPayload(
            firstName: $firstName,
            lastName: $lastName,
            email: isset($input['email']) ? (string) $input['email'] : null,
            phone: isset($input['phone']) ? (string) $input['phone'] : null,
            source: $this->key(),
            sourceId: isset($input['lead_id']) ? (int) $input['lead_id'] : null,
        );
    }
}
