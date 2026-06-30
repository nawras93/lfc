# LFC Demo Scaffold

1. Run `composer install` and copy `.env.example` to `.env`, then set your database credentials.
2. Run `php artisan key:generate` and `php artisan migrate --seed`.
3. Start the app with `php artisan serve` and open `/admin`.
4. Log in with `LFC_ADMIN_EMAIL` / `LFC_ADMIN_PASSWORD` from `.env` (defaults: `admin@lfc.test` / `password`).
