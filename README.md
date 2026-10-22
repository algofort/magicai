# MagicAI deployment handoff

Purchased MagicAI source with dependency lock files and supplied frontend build.
Keep this repository private. `vendor/`, `node_modules/`, real `.env`, databases,
runtime storage and activation data are intentionally excluded.

## Requirements and setup

- PHP >=8.4.1 required by the supplied composer.lock (despite composer.json ^8.2).
  Local runtime was PHP 8.5.12; verify the target server with Composer.
- PHP CLI and FPM extensions: curl, zip, fileinfo, gd, mbstring, openssl,
  pdo_mysql, sodium, intl and exif; check all lock requirements on the server.
- MariaDB/MySQL, Composer 2 and Node.js 24 if rebuilding frontend assets.
- Nginx/Apache web root MUST be `public/`, never the repository root.

```bash
cp .env.example .env
# Configure your own database and HTTPS domain in .env first.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
composer install --no-dev --prefer-dist --no-interaction
php artisan key:generate
php artisan storage:link
php artisan optimize:clear
composer check-platform-reqs --no-dev
```

For an existing installation, preserve its original APP_KEY and transfer the
database/storage privately BEFORE startup; do not regenerate its encryption key.
Database SQL is not in Git. The local installation used the SQL supplied in the
vendor package. Ask the owner for it or a current private export; do not assume
running migrations alone recreates all vendor settings and seed data.

Use APP_ENV=production, APP_DEBUG=false and your real HTTPS APP_URL on a VPS.
Grant the PHP service write access only where needed (storage/bootstrap/cache
and any feature-specific upload directories); do not use chmod 777.

The supplied `public/build/manifest.json` and built assets are retained. No new
frontend build was performed for this upload. To rebuild on Linux:

```bash
npm ci
npm run build
```

Keep `rt-client-0.4.7.tgz`: package.json installs that local dependency. The vendor
Vite config removes duplicate SCSS files during builds; review build changes.

Use a supervised `php artisan queue:work` process and one cron entry running
`php artisan schedule:run` every minute when the configured features need jobs.
Configure SMTP, selected AI/payment providers and public webhook URLs separately.

## Activation and current verification

Local Laravel startup, database migration status and supplied build-file presence
were checked. HTTP returned the official license activation page. Dashboard,
AI generation, payments and Linux VPS operation have NOT been validated.
Activate your own purchase through Liquid Portal; no activation state or purchase
code is included in this repository. Paid extensions are not implied by demo UI.

The local audit reported four active Composer advisories affecting two packages
and four ignored advisories affecting two packages. Review `composer audit` and
`npm audit` before public deployment; no forced dependency upgrades were applied.
The npm runtime-only audit was zero, which does not cover build dependencies.

Back up the database, runtime storage and APP_KEY securely off-server. Do not
commit API keys, database dumps or customer files. The included Windows launcher
expects the separate local MagicAI-Package/php85 runtime and is not a VPS script.
