# FTC Installment Management System

Professional Laravel web application for managing installment sales, customers, products, payment schedules, receipts, ledgers, overdue payments, investment, profit, and reports for FTC.

## Stack

- PHP 8.2+
- Laravel `^12.0 || ^13.0`
- MySQL / MariaDB
- Blade templates
- Bootstrap 5
- No frontend build step required; CDN assets are used for cPanel simplicity

## Core Features

- Secure admin/staff login with hashed passwords
- Role access: Admin and Staff
- Dashboard with collection, investment, profit, pending, overdue, and alert metrics
- Customer CRUD with guarantor details and document/image uploads
- Product CRUD with SKU, stock, cost price, cash price, installment price, and image upload
- Installment sale creation with automatic profit, balance, and schedule generation
- Payment collection with partial payment support and auto allocation to pending installments
- Customer ledger with debit, credit, running balance, print/PDF, and CSV export
- Payment receipt print/PDF page
- Pending, overdue, due today, due week, and missed-this-month sections
- Reports for payments, pending, overdue, sales, investment, profit, active/completed accounts, defaulters, and daily collection
- Company settings for logo, phone, address, currency, footer text, payment methods, and default due day
- Activity log records for major actions
- cPanel-friendly source structure and deployment guide

## Default Admin

```text
Email: admin@ftc.com
Password: admin123
```

Change this password immediately after first login.

## Local Installation

```bash
cp .env.example .env
composer install
php artisan key:generate
```

Create a MySQL database and update `.env`:

```env
DB_DATABASE=ftc_installment
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Run migrations and seeders:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Main Workflows

1. Add customers from `Customers`.
2. Add products from `Products`.
3. Create installment accounts from `Installment Sales`.
4. The system creates:
   - sale debit entry in customer ledger
   - advance payment receipt when advance is entered
   - monthly installment schedule
   - product stock reduction
5. Record collections from `Payments` or `Pending & Overdue`.
6. The system updates:
   - payment receipt
   - customer ledger credit
   - installment schedule paid/partial/overdue status
   - sale paid and pending balances
   - completed account status when fully paid

## Print and Export

- Ledger print/PDF: customer profile or customer ledger page
- Receipt print/PDF: payment receipt page
- Schedule print/PDF: sale detail page
- CSV exports: ledger and major reports
- Browser PDF export is available through the print pages by choosing `Save as PDF`

## Security Notes

- Laravel CSRF protection is enabled on forms.
- Database access uses Eloquent/query builder parameter binding.
- Passwords are hashed with Laravel hashing.
- Uploads are validated as images and stored under `storage/app/public`.
- Admin-only sections are protected by role middleware.
- Do not commit `.env` or database dumps containing real customer data.

## Useful Commands

```bash
php artisan migrate --seed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link
php artisan optimize
```

## GitHub Setup

```bash
git init
git add .
git commit -m "Initial FTC installment management system"
git branch -M main
git remote add origin git@github.com:YOUR-ORG/ftc-installment-management.git
git push -u origin main
```

## Backup

Back up both database and uploaded files.

Database:

```bash
mysqldump -u DB_USERNAME -p DB_DATABASE > ftc_backup.sql
```

Files:

```bash
zip -r ftc_uploads_backup.zip storage/app/public
```

On cPanel, use phpMyAdmin Export for the database and File Manager backup/compress for project files.

## cPanel Deployment

See [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md).
