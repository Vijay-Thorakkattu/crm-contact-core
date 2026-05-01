<?php

namespace App\Services\Contacts;

use App\Models\Contact;

class CreateContactService
{
    public function __construct(private readonly ContactSourceRegistry $registry)
    {
    }

    public function create(string $source, array $data): Contact
    {
        $handler = $this->registry->resolve($source);
        $payload = $handler->toPayload($data);

        return Contact::create([
            'first_name' => $payload->firstName,
            'last_name' => $payload->lastName,
            'email' => $payload->email,
            'phone' => $payload->phone,
            'source' => $payload->source,
            'source_id' => $payload->sourceId,
        ]);
    }
}
