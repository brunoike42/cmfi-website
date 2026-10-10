# Church website (PHP + MySQL)

Plain PHP with no framework and no Composer. Data is stored in MySQL (or MariaDB, which XAMPP uses).

## What you need
- PHP 8.1 or newer with the `pdo_mysql` and `mbstring` extensions (both are on by default in XAMPP).
- MySQL 5.7+ or MariaDB 10.3+.

## Run it on Windows with XAMPP (easiest)
XAMPP supplies both MySQL and PHP. You only need MySQL running, and PHP's built-in server serves the site.
1. Install XAMPP, open the XAMPP Control Panel and click **Start** next to **MySQL** (Apache is not needed).
2. Unzip this project anywhere, for example `E:\church-php-mysql`, and open a terminal inside that folder.
3. Run: `C:\xampp\php\php.exe -S localhost:8000 -t public public/index.php`
   (If PHP is already installed and in your PATH, `php -S localhost:8000 -t public public/index.php` works too.)
4. Open http://localhost:8000/admin in your browser. The first visit creates the `church` database and all the tables by itself, then asks you to create the admin account.
5. Add your content in the admin. The public site is at http://localhost:8000
6. Leave the terminal open while you use the site. Press Ctrl+C to stop it.

With a fresh XAMPP, `config.php` already works as it is (user `root`, empty password).

## Using Apache instead
Page links start with `/`, so the site must sit at the root of its address. Point an Apache virtual host (or your hosting domain) at the `public` folder. Putting the project inside a subfolder of `htdocs` will break the links.

## Creating the database yourself (optional)
If MySQL will not let your user create databases, open phpMyAdmin (http://localhost/phpmyadmin), create an empty database called `church` (collation `utf8mb4_unicode_ci`), and put your user and password in `config.php`. The tables are created automatically.

## On real hosting (cPanel and similar)
1. In cPanel create a MySQL database, a user, and give the user all privileges on it.
2. Put those details in `config.php` (host is usually `localhost`) and set `debug` to `false`.
3. Upload the files. Point the domain at the `public` folder if you can. Make `public/uploads` writable.

## Where things are
- `config.php` - site name and database settings.
- `src/entities.php` - every kind of content (fields and labels). The tables are built from it.
- `src/pages/` - one file per public page. `public/assets/` - style and slideshow script.
- `src/admin.php` - admin dashboard. `src/submit.php` - all public forms.

## Admin
- Content: Churches, Leaders, Ministries, Events, Resources, Projects.
- Pages: About page, Livestream, Giving.
- Inbox: Prayer requests, Messages, Event registrations, Ministry sign-ups (only a logged-in admin can read them).
- Lines like "year|event" or "name|description|link" are explained under each field.

## Notes
- If you add a field in `src/entities.php` after the tables exist, add the column in phpMyAdmin too (or drop that table so it is recreated).
- Online payments are not connected. The Give page shows giving methods and optional payment links you add in the admin.
- Back up the `church` database (phpMyAdmin, Export) and `public/uploads` regularly.
