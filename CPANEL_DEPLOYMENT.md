# cPanel Deployment Guide

This guide deploys FTC Installment Management System on shared cPanel hosting with PHP and MySQL.

## 1. Hosting Requirements

- PHP 8.2+ for Laravel 12
- PHP 8.3+ if Composer resolves Laravel 13
- MySQL 5.7+ or MariaDB 10.3+
- PHP extensions commonly required by Laravel: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, XML
- Composer access through Terminal/SSH is recommended

## 2. Upload Source Code

Upload the project to a directory outside `public_html`, for example:

```text
/home/USERNAME/ftc-app
```

Keep Laravel source files outside the public web root.

## 3. Point Domain to `public`

Best option:

Set the domain or subdomain document root to:

```text
/home/USERNAME/ftc-app/public
```

If your cPanel does not allow changing document root:

1. Copy the contents of `/home/USERNAME/ftc-app/public` into `/home/USERNAME/public_html`.
2. Edit `public_html/index.php`.
3. Change paths from:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

to:

```php
require __DIR__.'/../ftc-app/vendor/autoload.php';
$app = require_once __DIR__.'/../ftc-app/bootstrap/app.php';
```

Keep the rest of the Laravel project in `/home/USERNAME/ftc-app`.

## 4. Create MySQL Database

In cPanel:

1. Open `MySQL Databases`.
2. Create database, for example `USERNAME_ftc`.
3. Create user, for example `USERNAME_ftcuser`.
4. Assign the user to the database with all privileges.

## 5. Configure `.env`

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Update:

```env
APP_NAME="FTC Installment Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=USERNAME_ftc
DB_USERNAME=USERNAME_ftcuser
DB_PASSWORD=your_secure_password
```

Never upload real `.env` credentials to GitHub.

## 6. Install Dependencies

From Terminal/SSH:

```bash
cd /home/USERNAME/ftc-app
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

If shared hosting blocks Composer, run `composer install --no-dev --optimize-autoloader` locally, upload the `vendor` directory, then continue with the Artisan commands on the server.

## 7. Run Database Migrations and Seeder

```bash
php artisan migrate --seed --force
```

This creates all tables and the default admin user:

```text
admin@ftc.com / admin123
```

## 8. Storage Link

Run:

```bash
php artisan storage:link
```

If symlinks are blocked, create this folder manually:

```text
public/storage
```

and copy uploaded files from:

```text
storage/app/public
```

The preferred approach is still the Laravel storage symlink.

## 9. File Permissions

Set writable permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

If your host uses a strict setup, use cPanel File Manager permissions so the PHP user can write to:

```text
storage
bootstrap/cache
```

Do not make the whole project `777`.

## 10. Cache for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

After changing `.env`, routes, or config:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Login and First Setup

Open your domain and login:

```text
Email: admin@ftc.com
Password: admin123
```

Then:

1. Change the admin password from `Users`.
2. Update company settings from `Settings`.
3. Add products and customers.
4. Start creating installment sales.

## 12. Database Backup

Use cPanel `Backup` or `phpMyAdmin > Export`.

Command line:

```bash
mysqldump -u USERNAME_ftcuser -p USERNAME_ftc > ftc_backup.sql
```

## 13. File Backup

Back up:

```text
/home/USERNAME/ftc-app
```

At minimum, keep:

```text
.env
storage/app/public
database backups
```

## 14. Updates From GitHub

```bash
cd /home/USERNAME/ftc-app
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Always back up database and uploaded files before updating production.
