
#Task 1: CRM Contact Core

Laravel 12 API-first implementation for creating CRM contacts from multiple sources.
This project provides a REST API to create CRM contacts from different sources such as:
1) Account
2) Lead

The system is designed to be extensible, allowing new sources to be added easily in the future.

# PHP: 8.2.12
# Laravel Framework - 12.58.0
# Database: MySQL

#Follow these steps from the project root:

1. Install dependencies:
   - `composer install`
2. Create environment file:
   - `copy .env.example .env` 
3. Set database credentials in `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=<your_database_name>`
   - `DB_USERNAME=<your_username>`
   - `DB_PASSWORD=<your_password>`
4. Generate app key:
   - `php artisan key:generate`
5. Run migrations:
   - `php artisan migrate`
6. Start the application:
   - `php artisan serve`
7. Use API endpoint:
   - `POST http://127.0.0.1:8000/api/contacts`

## How to Run Tests 

Run the full automated test:

- `php artisan test`

## API Endpoint

- `POST /api/contacts`

### Request (Account Source)

```json
{
  "source": "account",
  "data": {
    "first_name": "Ajay",
    "last_name": "k",
    "email": "ajay@gmail.com",
    "phone": "9999999999",
    "account_id": 10
  }
}
```

### Request (Lead Source)

```json
{
  "source": "lead",
  "data": {
    "first_name": "Vishnu",
    "last_name": "k",
    "email": "vishnu@gmail.com",
    "phone": "8888888888",
    "lead_id": 20
  }
}

- At least one contact channel is required: `email` OR `phone`.


## Task 2: MySQL Query Optimization

# 1) Performance Summary

- Initial execution time: 20+ seconds
- Optimized execution time: below 5 seconds
- Target requirement: `< 5 seconds` (achieved)

# 2) Identified Bottlenecks

The original query was slow due to:

- No suitable composite index for:
  - `account_id = 1`
  - `deleted_at IS NULL`
- Expensive sorting overhead for:
  - `ORDER BY id DESC`

# 3) Optimization Strategy

Added a composite B-Tree index:

```sql
KEY idx_account_deleted_id (account_id, deleted_at, id)
```

Improved works:

- Prefix filtering
  - `account_id` and `deleted_at` are first, so MySQL narrows matching rows quickly.
- Index-based sorting
  - Including `id` helps satisfy `ORDER BY id DESC` efficiently.
- Efficient pagination
  - `LIMIT 100` allows early stop after required rows are found.

# 4) Submission Content

- `optimized_table.sql`: Full table creation syntax including the composite index.
- `optimized_query.sql`: Final fetch query returning the same data in the same order.
