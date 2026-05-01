<?php

namespace App\Providers;

use App\Services\Contacts\ContactSourceRegistry;
use App\Services\Contacts\Sources\AccountContactSource;
use App\Services\Contacts\Sources\LeadContactSource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(AccountContactSource::class);
        $this->app->bind(LeadContactSource::class);

        $this->app->bind(ContactSourceRegistry::class, function ($app): ContactSourceRegistry {
            return new ContactSourceRegistry([
                $app->make(AccountContactSource::class),
                $app->make(LeadContactSource::class),
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}
