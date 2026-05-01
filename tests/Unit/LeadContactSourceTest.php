<?php

namespace Tests\Unit;

use App\Services\Contacts\Sources\LeadContactSource;
use Tests\TestCase;

class LeadContactSourceTest extends TestCase
{
    public function test_it_maps_lead_input_to_contact_payload(): void
    {
        $source = new LeadContactSource();

        $payload = $source->toPayload([
            'first_name' => 'Akhil',
            'last_name' => 'Thorakattu',
            'email' => 'akhil@thorakattu.com',
            'phone' => '8891210661',
            'lead_id' => 2,
        ]);

        $this->assertSame('lead', $source->key());
        $this->assertSame('Akhil', $payload->firstName);
        $this->assertSame('Thorakattu', $payload->lastName);
        $this->assertSame('akhil@thorakattu.com', $payload->email);
        $this->assertSame('8891210661', $payload->phone);
        $this->assertSame(2, $payload->sourceId);
        $this->assertSame('lead', $payload->source);
    }
}
