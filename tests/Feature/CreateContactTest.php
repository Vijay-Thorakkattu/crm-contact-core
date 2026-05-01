<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_contact_from_account_source(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'account',
            'data' => [
                'first_name' => 'Account',
                'last_name' => 'Owner',
                'email' => 'account.owner@gmail.com',
                'phone' => '7777777777',
                'account_id' => 1,
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.source', 'account')
            ->assertJsonPath('data.first_name', 'Account');

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Account',
            'last_name' => 'Owner',
            'source' => 'account',
            'source_id' => 1,
        ]);
    }

    public function test_it_creates_contact_from_lead_source(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'lead',
            'data' => [
                'first_name' => 'Lead',
                'last_name' => 'Owner',
                'email' => 'lead.owner@gmail.com',
                'phone' => '6666666666',
                'lead_id' => 2,
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.source', 'lead')
            ->assertJsonPath('data.first_name', 'Lead');

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Lead',
            'last_name' => 'Owner',
            'source' => 'lead',
            'source_id' => 2,
        ]);
    }

    public function test_it_rejects_unsupported_source(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'import',
            'data' => [
                'first_name' => 'New',
                'last_name' => 'Source',
                'email' => 'new.source@gmail.com',
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['source']);
    }

    public function test_it_accepts_email_only_without_phone(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'account',
            'data' => [
                'first_name' => 'Email',
                'last_name' => 'Only',
                'email' => 'email.only@gmail.com',
                'account_id' => 3,
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Email',
            'last_name' => 'Only',
            'email' => 'email.only@gmail.com',
            'phone' => null,
            'source' => 'account',
            'source_id' => 3,
        ]);
    }

    public function test_it_accepts_phone_only_without_email(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'lead',
            'data' => [
                'first_name' => 'Phone',
                'last_name' => 'Only',
                'phone' => '5555555555',
                'lead_id' => 4,
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Phone',
            'last_name' => 'Only',
            'email' => null,
            'phone' => '5555555555',
            'source' => 'lead',
            'source_id' => 4,
        ]);
    }

    public function test_it_requires_at_least_email_or_phone(): void
    {
        $response = $this->postJson('/api/contacts', [
            'source' => 'account',
            'data' => [
                'first_name' => 'No',
                'last_name' => 'ContactPoint',
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.email', 'data.phone']);
    }

    public function test_it_rejects_duplicate_email(): void
    {
        $this->postJson('/api/contacts', [
            'source' => 'account',
            'data' => [
                'first_name' => 'Email',
                'last_name' => 'Duplicate',
                'email' => 'dup.email@gmail.com',
                'phone' => '9000000001',
                'account_id' => 10,
            ],
        ])->assertCreated();

        $response = $this->postJson('/api/contacts', [
            'source' => 'lead',
            'data' => [
                'first_name' => 'Another',
                'last_name' => 'Person',
                'email' => 'dup.email@gmail.com',
                'phone' => '9000000002',
                'lead_id' => 11,
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.email']);
    }

    public function test_it_rejects_duplicate_phone(): void
    {
        $this->postJson('/api/contacts', [
            'source' => 'account',
            'data' => [
                'first_name' => 'Phone',
                'last_name' => 'Duplicate',
                'email' => 'first.phone@gmail.com',
                'phone' => '9000000010',
                'account_id' => 20,
            ],
        ])->assertCreated();

        $response = $this->postJson('/api/contacts', [
            'source' => 'lead',
            'data' => [
                'first_name' => 'Second',
                'last_name' => 'Person',
                'email' => 'second.phone@gmail.com',
                'phone' => '9000000010',
                'lead_id' => 21,
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data.phone']);
    }
}
