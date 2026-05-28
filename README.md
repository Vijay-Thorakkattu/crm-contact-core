# CRM Contact Core

A Laravel REST API that creates CRM contacts from multiple origins (Account, Lead, and future sources) through one unified endpoint and database table.

---

## What I Built

In a CRM, contacts can be created from different modules. Each module may send slightly different field names (`account_id` vs `lead_id`), but the final stored contact should follow one consistent structure.

This project provides:

- **One API endpoint:** `POST /api/contacts`
- **One database table:** `contacts` (with `source` + `source_id` to track origin)
- **Multiple input sources:** currently `account` and `lead`
- **Extensible design:** new sources can be added without rewriting the controller or service

---

## Versions

| Component | Version |
|-----------|---------|
| PHP | 8.2+ (`^8.2` in `composer.json`) |
| Laravel Framework | 12.x (`v12.58.0` installed) |
| PHPUnit | 11.x (`^11.5`) |
| Database | MySQL |
| API style | REST (JSON) |

Check your local runtime:

```bash
php -v
php artisan --version
```

---

## Design Approach (What, How, Why)

### Layered flow

```
HTTP Request
  → StoreContactRequest   (validation)
  → ContactController     (thin entry point)
  → CreateContactService  (business orchestration)
  → ContactSourceRegistry (resolve source handler)
  → AccountContactSource | LeadContactSource
  → ContactPayload DTO    (normalized shape)
  → Contact model         (database persist)
  → JSON response (201)
```

### Patterns and methods used

| Pattern / practice | Where | Why |
|--------------------|-------|-----|
| **Strategy** | `ContactSourceContract` + source classes | Each source has its own mapping rules without `if/else` in the service |
| **Registry** | `ContactSourceRegistry` | Central lookup of source handlers by key (`account`, `lead`) |
| **DTO** | `ContactPayload` | One internal format before saving, regardless of source input |
| **Service layer** | `CreateContactService` | Keeps controller thin; business logic in one place |
| **Form Request** | `StoreContactRequest` | Laravel-native HTTP validation before controller logic |
| **Dependency Injection** | `AppServiceProvider` | Sources and registry are wired through the container |
| **Eloquent ORM** | `Contact` model + migration | Standard Laravel persistence |
| **Automated tests** | Feature + Unit tests | Proves API behavior and source resolution |

### Why this architecture?

- **Open/Closed Principle:** add sources by creating new classes, not by editing core creation logic
- **Single Responsibility:** controller handles HTTP, service handles workflow, sources handle mapping
- **Maintainability:** validation, mapping, and persistence are separated
- **Testability:** registry and sources can be unit-tested independently

---

## Project Structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/ContactController.php` | API entry point |
| `app/Http/Requests/StoreContactRequest.php` | Request validation rules |
| `app/Services/Contacts/CreateContactService.php` | Contact creation orchestration |
| `app/Services/Contacts/ContactSourceRegistry.php` | Resolves source by key |
| `app/Services/Contacts/Sources/` | Source-specific mapping logic |
| `app/Contracts/ContactSourceContract.php` | Source interface |
| `app/DTOs/ContactPayload.php` | Normalized contact data object |
| `app/Models/Contact.php` | Eloquent model |
| `database/migrations/` | Database schema |
| `tests/Feature/CreateContactTest.php` | End-to-end API tests |
| `tests/Unit/` | Registry and source unit tests |

---

## Setup

From the project root:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure database credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_contact_core
DB_USERNAME=root
DB_PASSWORD=
```

---

## API Usage

**Endpoint:** `POST /api/contacts`

### Account source

```json
{
  "source": "account",
  "data": {
    "first_name": "Ajay",
    "last_name": "K",
    "email": "ajay@example.com",
    "phone": "9999999999",
    "account_id": 10
  }
}
```

### Lead source

```json
{
  "source": "lead",
  "data": {
    "first_name": "Vishnu",
    "last_name": "K",
    "email": "vishnu@example.com",
    "phone": "8888888888",
    "lead_id": 20
  }
}
```

**Rules:**

- `source` must be a registered source key
- `first_name` and `last_name` are required
- At least one of `email` or `phone` is required
- `email` and `phone` must be unique in `contacts`

**Success response:** `201 Created`

```json
{
  "message": "Contact created successfully.",
  "data": {
    "id": 1,
    "first_name": "Ajay",
    "last_name": "K",
    "email": "ajay@example.com",
    "phone": "9999999999",
    "source": "account",
    "source_id": 10
  }
}
```

---

## Recipe: How to Add a New Source

Example: add a source named `import`.

### Step 1 — Create the source class

Create `app/Services/Contacts/Sources/ImportContactSource.php`:

```php
<?php

namespace App\Services\Contacts\Sources;

use App\Contracts\ContactSourceContract;
use App\DTOs\ContactPayload;
use InvalidArgumentException;

class ImportContactSource implements ContactSourceContract
{
    public function key(): string
    {
        return 'import';
    }

    public function toPayload(array $input): ContactPayload
    {
        $firstName = (string) ($input['first_name'] ?? '');
        $lastName = (string) ($input['last_name'] ?? '');

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('Import source requires first_name and last_name.');
        }

        return new ContactPayload(
            firstName: $firstName,
            lastName: $lastName,
            email: isset($input['email']) ? (string) $input['email'] : null,
            phone: isset($input['phone']) ? (string) $input['phone'] : null,
            source: $this->key(),
            sourceId: isset($input['import_id']) ? (int) $input['import_id'] : null,
        );
    }
}
```

> Map source-specific IDs to `sourceId` (e.g. `import_id`, `campaign_id`).

### Step 2 — Register in the container

Update `app/Providers/AppServiceProvider.php`:

```php
use App\Services\Contacts\Sources\ImportContactSource;

// inside register()
$this->app->bind(ImportContactSource::class);

$this->app->bind(ContactSourceRegistry::class, function ($app): ContactSourceRegistry {
    return new ContactSourceRegistry([
        $app->make(AccountContactSource::class),
        $app->make(LeadContactSource::class),
        $app->make(ImportContactSource::class), // add here
    ]);
});
```

### Step 3 — Allow source in validation

Update `app/Http/Requests/StoreContactRequest.php`:

```php
'source' => ['required', 'string', Rule::in(['account', 'lead', 'import'])],
'data.import_id' => ['nullable', 'integer', 'min:1'], // source-specific field
```

### Step 4 — Add tests

1. **Unit test** for mapping in `tests/Unit/ImportContactSourceTest.php`
2. **Feature test** in `tests/Feature/CreateContactTest.php` for `POST /api/contacts` with `"source": "import"`

Run:

```bash
php artisan test
```

### Step 5 — Document API example

Add request example for the new source in this README (same format as Account/Lead examples).

---

### Checklist for any new source

- [ ] Create class implementing `ContactSourceContract`
- [ ] Implement `key()` with unique source name
- [ ] Implement `toPayload()` with source-specific validation/mapping
- [ ] Register class in `AppServiceProvider`
- [ ] Add source key to `StoreContactRequest` `Rule::in([...])`
- [ ] Add source-specific validation fields in `StoreContactRequest`
- [ ] Add unit + feature tests
- [ ] Update README API examples

> You do **not** need to change `ContactController` or `CreateContactService` when adding a standard source.

---

## Tests

Run all tests:

```bash
php artisan test
```

Coverage includes:

- Account and Lead contact creation
- Unsupported source rejection
- Email-only / phone-only creation
- Duplicate email/phone rejection
- Registry resolution and unknown source handling
