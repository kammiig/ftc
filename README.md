# FTC Installment Management System

Professional Laravel web application for managing installment sales, customers, products, payment schedules, receipts, ledgers, overdue payments, investment, profit, and reports for FTC.

## Stack

- PHP 8.3+
- Laravel `^13.0`
- MySQL / MariaDB
- Blade templates
- Bootstrap 5
- No frontend build step required; CDN assets are used for cPanel simplicity

## Core Features

- Secure admin/staff login with hashed passwords
- Role access: Admin and Staff
- Simplified dashboard with customer, account, collection, pending, overdue, and due-date metrics
- Customer CRUD with guarantor details and document/image uploads
- Product CRUD with SKU, stock, cost price, cash price, installment price, and image upload
- Installment sale creation with automatic profit, balance, and schedule generation
- Payment collection with partial payment support and auto allocation to pending installments
- Customer ledger with debit, credit, running balance, print/PDF, and CSV export
- Payment receipt print/PDF page with stored receipt PDFs
- WhatsApp Web fallback for ledger, receipt, and payment confirmation messages without API tokens
- Pending, overdue, due today, and due week sections
- Simplified reports for daily/monthly collection, pending, overdue, customer ledgers, active accounts, completed accounts, plus admin-only investment/profit
- Company settings for logo, phone, address, currency, footer text, payment methods, and default due day
- Admin-only finance visibility for product cost, investment, and profit
- Activity log records for major actions
- cPanel-friendly source structure and deployment guide

## Default Admin

```text
Email: contact@ftc.com
Password: admin123
```

Change this password immediately after first login.

## Local Installation

```bash
cp .env.example .env
composer install
composer dump-autoload
php artisan key:generate
```

PDF downloads use `barryvdh/laravel-dompdf`, which is installed through Composer.

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
- Generated ledger and receipt PDFs are stored privately under `storage/app/private/pdfs`
- Ledger and receipt PDFs include a light FTC watermark
- Receipt print/PDF shows only `Authorized Signature`

## WhatsApp Web Fallback

The system does not require any WhatsApp API credentials. Send Receipt, Payment Confirmation, and Ledger WhatsApp buttons generate a PDF and open a fallback page with:

- Download PDF button
- Open WhatsApp button
- Prepared message text
- Instruction to attach the downloaded PDF manually

Customer WhatsApp number is used first. If it is empty, the customer phone number is used. Pakistan mobile numbers like `03XXXXXXXXX` are normalized to `923XXXXXXXXX`.

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

## PDF Troubleshooting on cPanel

If PDF download shows an error, run these from the Laravel project folder on cPanel:

```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

Also confirm the PHP user can write to:

```text
storage
storage/app/private/pdfs
storage/app/dompdf-temp
storage/app/dompdf-fonts
bootstrap/cache
```

The actual PDF error is logged in `storage/logs/laravel.log` with the message `PDF generation failed`.

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

The portal includes an Admin-only backup page at `Backups`.

Backup types:

- Database-only backup
- Full backup including database, uploaded customer/product/guarantor images, CNIC images, FTC logo, signature image, and generated PDFs

Backup ZIP files are stored outside the public web root in:

```text
storage/app/backups
```

Only logged-in Admin users can create, download, and delete backups.

## Automatic Backup Cron

For cPanel cron jobs, use one of these commands.

Full backup:

```bash
cd /home/USERNAME/ftc-app && /usr/local/bin/php artisan ftc:backup full >> /home/USERNAME/ftc_backup.log 2>&1
```

Database-only backup:

```bash
cd /home/USERNAME/ftc-app && /usr/local/bin/php artisan ftc:backup database >> /home/USERNAME/ftc_backup.log 2>&1
```

## Manual Backup Without Portal

Back up both database and uploaded files.

Database:

```bash
mysqldump -u DB_USERNAME -p DB_DATABASE > ftc_backup.sql
```

Files:

```bash
zip -r ftc_files_backup.zip storage/app/public storage/app/private/pdfs
```

On cPanel, use phpMyAdmin Export for the database and File Manager backup/compress for project files.

## How to Move FTC Portal to Another Domain

1. Log in to the old portal as Admin and create a full backup from `Backups`.
2. Download the full backup ZIP.
3. Upload the project source to the new hosting account.
4. Create a new MySQL database and database user.
5. Import the SQL file from the backup ZIP into the new database.
6. Upload the backed-up uploaded files into `storage/app/public`.
7. Upload generated PDFs into `storage/app/private/pdfs` if you need old PDF receipts and ledgers available.
8. Confirm `public/assets/images/ftc-logo.png` exists on the new hosting account.
9. Update `.env` with the new database credentials.
10. Update `APP_URL` in `.env` with the new domain.
11. Set permissions for `storage` and `bootstrap/cache`.
12. Run `php artisan storage:link` or create the storage link manually from cPanel.
13. Clear Laravel cache:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

14. Test login with the admin account.
15. Test dashboard totals.
16. Test a customer profile.
17. Test customer ledger, ledger print, and ledger PDF.
18. Test receipt print and receipt PDF.
19. Test WhatsApp Web fallback.
20. Test reports.
21. Test backup creation and download on the new domain.

## cPanel Deployment

See [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md).
