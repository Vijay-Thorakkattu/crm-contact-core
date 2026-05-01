<?php

namespace Tests\Unit;

use App\Services\Contacts\Sources\AccountContactSource;
use Tests\TestCase;

class AccountContactSourceTest extends TestCase
{
    public function test_it_maps_account_input_to_contact_payload(): void
    {
        $source = new AccountContactSource();

        $payload = $source->toPayload([
            'first_name' => 'Vijay',
            'last_name' => 'Thorakattu',
            'email' => 'vijay@thorakattu.com',
            'phone' => '8891210660',
            'account_id' => 1,
        ]);

        $this->assertSame('account', $source->key());
        $this->assertSame('Vijay', $payload->firstName);
        $this->assertSame('Thorakattu', $payload->lastName);
        $this->assertSame('vijay@thorakattu.com', $payload->email);
        $this->assertSame('8891210660', $payload->phone);
        $this->assertSame(1, $payload->sourceId);
        $this->assertSame('account', $payload->source);
    }
}
