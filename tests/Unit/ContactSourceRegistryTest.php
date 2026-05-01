<?php

namespace Tests\Unit;

use App\Services\Contacts\ContactSourceRegistry;
use App\Services\Contacts\Sources\AccountContactSource;
use App\Services\Contacts\Sources\LeadContactSource;
use InvalidArgumentException;
use Tests\TestCase;

class ContactSourceRegistryTest extends TestCase
{
    public function test_it_resolves_registered_source(): void
    {
        $registry = new ContactSourceRegistry([
            new AccountContactSource(),
            new LeadContactSource(),
        ]);

        $resolved = $registry->resolve('account');

        $this->assertInstanceOf(AccountContactSource::class, $resolved);
    }

    public function test_it_throws_for_unknown_source(): void
    {
        $registry = new ContactSourceRegistry([
            new AccountContactSource(),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $registry->resolve('lead');
    }
}
