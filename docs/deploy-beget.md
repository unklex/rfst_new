# Deploying to Beget (shared hosting)

Step-by-step for shipping this Laravel 11 + Filament 3 app to a Beget shared-hosting account. Everything runs via SSH + Beget's control panel. No Node.js is needed on the server — `public/build` is committed so Vite assets ship with the repo.

## Prerequisites — in the Beget control panel

1. **PHP version**: set the site to **PHP 8.2 (or 8.3)** under *Сайты → [site] → PHP*. Extensions to enable: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd` (with WebP), `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
2. **MySQL DB**: *MySQL → Создать базу*. Note the DB name, user, password, host (usually `127.0.0.1` or `localhost`).
3. **Domain → document root**: point the domain at `~/public_html/<site>/public` — NOT the Laravel root. Beget's panel: *Сайты → [site] → Корневая директория → `/public_html/<site>/public`*. If Beget forbids a non-top-level public folder, use the **symlink workaround** at the bottom of this guide.
4. **Mailbox**: *Почта → Создать ящик* → `no-reply@<your-domain>`. Copy the SMTP password.
5. **SSH**: *Доступ → SSH* → enable it (paid plans). Upload your public key.

## First deploy (run on Beget via SSH)

Replace `<site>` with your folder and `<repo>` with `https://github.com/unklex/rfst_new.git`.

```bash
# 1. Clone the repo into the site folder
cd ~
rm -rf public_html/<site>
git clone <repo> public_html/<site>
cd public_html/<site>

# 2. Install PHP deps (no dev packages, optimized autoloader)
composer install --no-dev --optimize-autoloader --no-interaction

# 3. App key + environment
cp .env.example .env
php artisan key:generate
# Open .env in your editor and fill in:
#   APP_URL=https://your-domain.ru
#   DB_DATABASE / DB_USERNAME / DB_PASSWORD (from Beget panel)
#   MAIL_USERNAME / MAIL_PASSWORD / MAIL_FROM_ADDRESS (from Beget mailbox)
#   ADMIN_EMAIL / ADMIN_PASSWORD / ADMIN_NAME (temporary; change after first login)
nano .env

# 4. Storage symlink (serves uploaded images under /storage/...)
php artisan storage:link

# 5. Set writable perms on storage + bootstrap/cache
chmod -R 775 storage bootstrap/cache
# If Beget complains, try 755 and add the web user to your group, or just 775

# 6. Migrate + seed (all 18 migrations + admin user + 12 content seeders + settings defaults)
php artisan migrate --force
php artisan db:seed --force

# 7. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components
# If Filament isn't registering any hooks, the last one can be skipped

# 8. Verify
curl -sI https://your-domain.ru/ | head -1
# → HTTP/2 200

curl -s https://your-domain.ru/admin/login | grep -c 'Криптон'
# → 2

curl -sI https://your-domain.ru/robots.txt | head -1
# → HTTP/2 200
```

Visit `https://your-domain.ru/admin/login` → sign in with `ADMIN_EMAIL`/`ADMIN_PASSWORD` → change the password immediately under *Моя учетная запись*. Paste the Cloudflare Turnstile site key + secret into **Настройки → Интеграции**; paste `notify_email` (the inbox that should receive lead notifications).

## Subsequent deploys (pull latest code)

```bash
cd ~/public_html/<site>

# Put the app into maintenance so visitors don't see half-deployed state
php artisan down --render="errors::503"

git fetch origin
git reset --hard origin/main        # use 'main' or whichever branch you deploy

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

**Do NOT run `npm run build` on Beget** — the repo ships prebuilt assets under `public/build`. To rebuild after CSS/JS changes:

```bash
# --- on your local machine ---
npm ci
npm run build
git add -f public/build
git commit -m "chore: rebuild vite assets"
git push
```

…then pull on the server as above.

## Queue worker (only if you switch off `QUEUE_CONNECTION=sync`)

For a brochure site with < 100 leads/day, `sync` is fine — mail sends inside the HTTP request. If lead volume grows, switch to `database`:

```bash
# 1. Edit .env: QUEUE_CONNECTION=database
# 2. Create the queue tables (already in Laravel 11 defaults — run once)
php artisan queue:table
php artisan queue:failed-table
php artisan migrate --force

# 3. Add a Beget cron job (Панель → Cron) running every minute:
cd ~/public_html/<site> && php artisan schedule:run >> /dev/null 2>&1

# 4. Inside routes/console.php (already stock-Laravel) register the worker:
#    Schedule::command('queue:work --stop-when-empty --max-time=55')->everyMinute();
```

## Scheduled cleanup (optional)

Add a daily cron to clear old sessions + prune the rate-limiter cache:

```bash
0 3 * * * cd ~/public_html/<site> && php artisan cache:prune-stale-tags >/dev/null 2>&1
0 4 * * * cd ~/public_html/<site> && php artisan session:prune >/dev/null 2>&1 || true
```

## Symlink workaround (if Beget won't point the domain at `/public`)

Beget sometimes insists the document root must be `public_html/<site>`. In that case:

```bash
cd ~/public_html/<site>
mv public ../<site>-public
mv ../<site>-public/* ./
rmdir ../<site>-public

# Rewrite public-path references: edit index.php
sed -i "s|__DIR__\\.'/../vendor/autoload.php'|__DIR__.'/vendor/autoload.php'|" index.php
sed -i "s|__DIR__\\.'/../bootstrap/app.php'|__DIR__.'/bootstrap/app.php'|" index.php
```

Better: **don't do this**. Ask Beget support to set the document root to `/public` — it's a one-time ticket reply.

## Rollback

```bash
cd ~/public_html/<site>
git log --oneline -10          # find the commit you want to roll back to
git reset --hard <sha>
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback   # if the bad deploy added migrations
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

## What NOT to commit / what NOT to run on Beget

- ❌ Don't commit `.env` (already in `.gitignore`)
- ❌ Don't commit `database/database.sqlite` (ignored via `database/.gitignore`)
- ❌ Don't commit uploaded user content from `storage/app/public/*` (ignored)
- ❌ Don't run `php artisan migrate:fresh` on production — it drops every table including leads
- ❌ Don't run `php artisan db:seed` on subsequent deploys — it's first-deploy-only; seeders are idempotent but the admin user seeder will log a warning if the row already exists

## Smoke test after first deploy

```
# on the server
php artisan about | grep -E 'Environment|Debug|Url|Database|Cache|Queue'
# Expect: Environment=production, Debug Mode=ENABLED=false, Url=https://..., Database=mysql

php artisan migrate:status | tail -5
# All migrations Ran

php artisan route:list --path=admin/contact-requests | wc -l
# 3 routes (index, view, edit) + header line

php artisan tinker --execute="echo app(App\Settings\GeneralSettings::class)->site_name;"
# → Криптон
```

## Disaster-recovery checklist

1. Beget DB backup: *MySQL → [db] → Создать бэкап* weekly (or use their auto-backup)
2. Mirror `storage/app/public/` to an off-site location weekly — uploaded media lives only there
3. `.env` is not in git. Keep the password and mail/DB creds in a password manager

## Common gotchas on Beget

- **"Call to undefined function Spatie\Image\Drivers\Gd\extension_loaded"**: the GD extension isn't enabled in PHP — turn it on in the panel and click *Применить*.
- **White page / 500**: check `storage/logs/laravel.log`. Usually `.env` missing `APP_KEY` or `storage/` not writable.
- **`/admin` redirects to `/admin/login` forever**: `SESSION_DOMAIN` in `.env` doesn't match the actual host. Set it to `your-domain.ru` (no scheme, no slash) OR leave it `null`.
- **Media files 404 after upload**: `php artisan storage:link` didn't run, or Beget's open_basedir blocks the symlink. Fall back to setting `FILESYSTEM_DISK=public` and manually copying `storage/app/public` contents into `public/storage/`.
- **Emails not arriving**: Beget sometimes rate-limits outbound SMTP. Check *Почта → Журнал отправки*. `MAIL_FROM_ADDRESS` must match the mailbox you created, or they'll bounce SPF.
