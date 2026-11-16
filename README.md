# Chama Ledger

A Laravel 13 app for Kenyan Chamas and small SACCOs to keep member, contribution and loan records in one place. Amounts are shown in Kenyan shillings; the app records M-Pesa transaction codes but does not initiate M-Pesa payments.

## Requirements

- PHP 8.3+ with PDO MySQL (and SQLite for the test suite)
- Composer
- Node.js 20+ and npm
- MySQL 8+

## Setup

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`, then run:

```sh
php artisan migrate --seed
npm run build
php artisan serve
```

Open the local URL printed by Artisan. For local development with live asset rebuilding, use `npm run dev` in a second terminal.

## Demo accounts

The database seeder creates one admin, one Treasurer, and five member accounts. Each seeded account uses the password `password`.

| Role | Email |
| --- | --- |
| Admin | `admin@chama.test` |
| Treasurer | `treasurer@chama.test` |
| Member | `peter@chama.test` |
| Member | `mary@chama.test` |
| Member | `john@chama.test` |
| Member | `faith@chama.test` |
| Treasurer/member | `grace@chama.test` |

These are development credentials only. Do not use them in a deployed environment. Without seed data, the first person to register becomes the administrator and can create the Chama; subsequent public registration is closed. Administrators create member accounts and assign the Treasurer role.

## Roles and money workflows

- **Admin:** creates the Chama, adds members and Treasurers, records contributions, and views group-level totals and overdue loans.
- **Treasurer:** records contributions, verifies pending contributions, approves or rejects loan requests, and confirms repayments. Only the Chama's assigned Treasurer can perform confirmations.
- **Member:** views only their own contributions and loans, requests a loan with another active member as guarantor, submits repayments for verification, and downloads a PDF statement.

Contribution and repayment entries begin as pending. Confirmation locks the ledger entry against edits or deletion. Confirmed repayments reduce the approved loan balance in a database transaction; the loan moves to `paid` when the remaining balance reaches zero. Loans accrue a one-time flat 10% interest when approved.

The overdue list includes approved loans that are past their due date and still have a balance. Confirmed contributions only are included in contribution totals and member statements.

## Tests

```sh
php artisan test
php artisan serve --host=127.0.0.1 --port=8000
```

The test environment uses an in-memory SQLite database. Ensure the PHP SQLite extension is enabled.

Laravel Breeze is included as the authentication package dependency; the app uses the Laravel session-authentication flow and Blade-based login and first-admin registration screens.
