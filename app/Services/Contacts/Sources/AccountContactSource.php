<?php

namespace App\Services\Contacts\Sources;

use App\Contracts\ContactSourceContract;
use App\DTOs\ContactPayload;
use InvalidArgumentException;

class AccountContactSource implements ContactSourceContract
{
    public function key(): string
    {
        return 'account';
    }

    public function toPayload(array $input): ContactPayload
    {
        $firstName = (string) ($input['first_name'] ?? '');
        $lastName = (string) ($input['last_name'] ?? '');

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('Account source requires first_name and last_name.');
        }

        return new ContactPayload(
            firstName: $firstName,
            lastName: $lastName,
            email: isset($input['email']) ? (string) $input['email'] : null,
            phone: isset($input['phone']) ? (string) $input['phone'] : null,
            source: $this->key(),
            sourceId: isset($input['account_id']) ? (int) $input['account_id'] : null,
        );
    }
}
