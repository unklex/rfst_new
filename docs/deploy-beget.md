# Deploying to Beget (rf-st.ru)

Step-by-step for shipping this Laravel 11 + Filament 3 app to the Beget shared-hosting account hosting **rf-st.ru**. Everything runs via SSH + the Beget control panel. No Node.js runs on the server — `public/build` is committed so Vite assets ship with the repo.

## Beget folder layout

Beget auto-creates a site folder whose name matches the domain:

```
~/rf-st.ru/                   ← site root (Laravel code lives here)
~/rf-st.ru/public_html/       ← auto-created web root (we replace it with a symlink to /public)
```

The web server serves `~/rf-st.ru/public_html/` by default. Laravel insists on `public/` being the doc root, so we point `public_html` at `public` with a symlink. Zero Beget-support-ticket required.

## One-time panel setup

1. **Домен уже создан** (Beget panel → *Сайты → rf-st.ru*).
2. **PHP version**: *Сайты → rf-st.ru → PHP* → **8.2** or **8.3**.
   Required extensions (tick all): `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd` (с WebP), `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
3. **MySQL**: *MySQL → Создать базу* → name it e.g. `crypton` with a dedicated user. Beget gives you DB host (usually `localhost`), DB name, user, password.
4. **SMTP mailbox**: *Почта → Создать ящик* → `no-reply@rf-st.ru`. Copy the password.
5. **SSH**: *Доступ → SSH* → turn it on, upload your public key.

## First deploy (run via SSH as the Beget account user)

### 1. Replace the auto-generated `public_html/` with the Laravel source

Beget drops a placeholder `index.html` into `~/rf-st.ru/public_html/`. We wipe the whole folder and clone the repo directly into `~/rf-st.ru/`.

```bash
ssh <user>@<beget-ssh-host>
cd ~

# Back up whatever Beget created (optional safety net — remove after you're sure)
mv rf-st.ru rf-st.ru.backup

# Clone the repo into the domain folder
git clone https://github.com/unklex/rfst_new.git rf-st.ru
cd rf-st.ru

# Point Beget's expected public_html at Laravel's /public
ln -s public public_html
ls -la | grep public_html
# → lrwxrwxrwx ... public_html -> public
```

If `rf-st.ru` already contains stuff you want to keep, use the in-place `git init` flow instead:

```bash
cd ~/rf-st.ru
rm -rf public_html
rm -f index.html .htaccess          # remove Beget placeholders
git init -b main
git remote add origin https://github.com/unklex/rfst_new.git
git fetch --depth=1 origin main
git checkout -f main
ln -s public public_html
```

### 2. Install PHP dependencies (no dev packages, optimized autoloader)

```bash
cd ~/rf-st.ru
composer install --no-dev --optimize-autoloader --no-interaction
```

If the Beget shell doesn't have `composer` on `$PATH`, use the absolute path Beget gives you, usually `/usr/local/bin/composer` or `~/.composer/bin/composer`. Ask support for the exact location if unsure.

### 3. App key + environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and fill in:

```
APP_URL=https://rf-st.ru

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<beget-db-name>
DB_USERNAME=<beget-db-user>
DB_PASSWORD=<beget-db-password>

MAIL_MAILER=smtp
MAIL_HOST=smtp.beget.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@rf-st.ru
MAIL_PASSWORD=<mailbox-password>
MAIL_FROM_ADDRESS="no-reply@rf-st.ru"

ADMIN_EMAIL=admin@rf-st.ru
ADMIN_PASSWORD=<temp-strong-password>
ADMIN_NAME=Администратор
```

Save, then:

```bash
nano .env                 # or: vi .env
```

### 4. Storage symlink + permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

If Beget's `open_basedir` blocks `storage:link`, fall back to a hard copy pipeline (documented in "Common gotchas" at the bottom).

### 5. Run migrations + seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

This creates 18 tables, seeds 20 Settings groups with the exact Russian copy from the prototype, and bootstraps the admin user from `ADMIN_EMAIL`/`ADMIN_PASSWORD`.

### 6. Cache everything for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components
php artisan icons:cache                   # optional, speeds up Heroicons resolution
```

> **Turnstile keys are safe to `config:cache`**. They're read from `IntegrationSettings` at runtime (per the core-decisions doc), not from `env()`.

### 7. Smoke tests

```bash
curl -sI https://rf-st.ru/ | head -1
# → HTTP/2 200

curl -s https://rf-st.ru/admin/login | grep -c 'Криптон'
# → 2

curl -sI https://rf-st.ru/robots.txt | head -1
# → HTTP/2 200

php artisan about | grep -E 'Environment|Debug|Url|Database'
# Expect: production / false / https://rf-st.ru / mysql
```

### 8. Sign in + lock down

1. Visit `https://rf-st.ru/admin/login`
2. Sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`
3. Change the password immediately (*profile → изменить пароль*)
4. **Настройки → Интеграции**:
   - `turnstile_site_key` / `turnstile_secret_key` (Cloudflare dashboard → Turnstile → your site)
   - `notify_email` → the inbox that should receive lead notifications
   - `yandex_metrika_id` → numeric counter ID (e.g. `12345678`)
5. **Справочники → Медиа-файлы сайта**: upload `favicon`, `hero_bg`, `og_image`, `about_archive`, `quote_reviewer`
6. Hit `/` and verify: Turnstile widget appears under the contact form, favicon in the tab, OG preview via [cards-dev.twitter.com/validator](https://cards-dev.twitter.com/validator)

## Subsequent deploys

### On your laptop

```bash
# ...edit code...
npm ci                     # first time only, or when package.json changes
npm run build
git add -f public/build
git add -A
git commit -m "<what changed>"
git push
```

### On Beget

```bash
cd ~/rf-st.ru

# Maintenance page while we swap code
php artisan down --render="errors::503"

git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

# Rebuild all caches against the new code
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

php artisan up
```

## Queue worker (only if you switch off `QUEUE_CONNECTION=sync`)

For a brochure site with < 100 leads/day, `sync` is fine — mail sends inside the HTTP request. If lead volume grows, switch to `database`:

```bash
# 1. Edit .env: QUEUE_CONNECTION=database
# 2. Queue tables (Laravel 11 ships them in the stock migration set — already applied)
# 3. Beget panel → Cron → every minute:
cd ~/rf-st.ru && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

# 4. In routes/console.php register the worker (Laravel 11 stock):
#    Schedule::command('queue:work --stop-when-empty --max-time=55')->everyMinute();
```

## Rollback

```bash
cd ~/rf-st.ru
git log --oneline -10           # find the commit you want
git reset --hard <sha>
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback    # if the bad deploy added migrations
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan filament:cache-components
```

## Disaster-recovery checklist

1. Beget DB backup: *MySQL → crypton → Создать бэкап* weekly (or turn on Beget's auto-backup).
2. Mirror `~/rf-st.ru/storage/app/public/` off-site weekly — uploaded media lives only there.
3. `.env` is **not** in git. Keep DB + mailbox + admin passwords in a password manager.

## What NOT to commit / what NOT to run on production

- ❌ Don't commit `.env` (already ignored).
- ❌ Don't commit `database/database.sqlite` (ignored via `database/.gitignore`).
- ❌ Don't commit uploaded user content from `storage/app/public/*` (ignored).
- ❌ **Never** run `php artisan migrate:fresh` on production — it drops every table, including leads.
- ❌ Don't re-run `php artisan db:seed` on subsequent deploys. Seeders are mostly idempotent via `updateOrCreate`, but the admin seeder will no-op and content seeders may flash a warning; safer to skip.
- ❌ Don't commit `node_modules/` or `vendor/` (both ignored).

## Common gotchas on Beget

- **500 Internal Server Error / blank page**: inspect `storage/logs/laravel.log`. Usually `.env` missing `APP_KEY` or `storage/` perms wrong.
- **"Call to undefined function gd_info()"**: the GD extension isn't enabled — toggle it in *Сайты → rf-st.ru → PHP → Расширения* and click *Применить*.
- **`/admin` bounces to `/admin/login` in a loop**: `SESSION_DOMAIN` in `.env` doesn't match. Set it to `rf-st.ru` (no scheme, no slash) OR leave it `null` and let Laravel infer.
- **Media uploads 404**: `storage:link` didn't run, or Beget's `open_basedir` blocks the symlink. Workaround: set `FILESYSTEM_DISK=public` and manually copy `storage/app/public/*` into `public/storage/` on each upload — ugly, but works. Prefer opening a Beget support ticket to allow the symlink.
- **Emails not arriving**: Beget rate-limits outbound SMTP. Check *Почта → Журнал отправки*. Make sure `MAIL_FROM_ADDRESS` matches the mailbox you created or SPF will bounce it.
- **`public_html` exists and is a folder, not a symlink**: you skipped `rm -rf public_html` before creating the symlink. Redo step 1.
- **Git reset fails with "error: unable to unlink old 'storage/...'"**: storage files have the wrong owner. `chmod -R u+rwX storage bootstrap/cache` and retry.
- **Composer memory limit**: Beget sometimes caps CLI PHP memory. Prepend `COMPOSER_MEMORY_LIMIT=-1`: `COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader`.

## Copy-paste command bundle (first deploy, one block)

Replace the placeholders, then run top-to-bottom:

```bash
ssh <user>@<beget-ssh-host>

cd ~
[ -d rf-st.ru ] && mv rf-st.ru rf-st.ru.$(date +%s).backup

git clone https://github.com/unklex/rfst_new.git rf-st.ru
cd rf-st.ru
ln -s public public_html

composer install --no-dev --optimize-autoloader --no-interaction

cp .env.example .env
php artisan key:generate

echo "--> edit .env now: APP_URL, DB_*, MAIL_*, ADMIN_*"
nano .env

php artisan storage:link
chmod -R 775 storage bootstrap/cache

php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

curl -sI https://rf-st.ru/ | head -1
```
