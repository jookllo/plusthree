# Deploying to Dokploy

The repository builds into a single production image: Vite assets, production Composer dependencies and the PHP/Apache runtime.

## 1. Application

- **Source:** `git@github.com:jookllo/plusthree.git`, branch `main`
- **Build type:** Dockerfile
- **Dockerfile path:** `Dockerfile` (repository root)
- **Build context:** repository root (not `app/`)
- **Internal port:** `80`
- **Health check path:** `/up`

Every push to `main` can trigger an automatic redeploy.

## 2. Database

Create a MySQL service in the same Dokploy project (MySQL 8.4 or compatible). Use its **internal service name** as `DB_HOST` and port `3306`. Do not reuse the local `eden-test-mysql` container, and do not copy the local `3307` port.

## 3. Environment

Set these in the Dokploy application's environment panel. Generate `APP_KEY` with `php artisan key:generate --show` locally; never commit it.

```dotenv
APP_NAME=Plusthree
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=plusthree-db
DB_PORT=3306
DB_DATABASE=plusthree
DB_USERNAME=plusthree
DB_PASSWORD=...
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@your-domain.example
MAIL_FROM_NAME="Plusthree"

BCRYPT_ROUNDS=12
```

Notes:

- `APP_URL` must be the final HTTPS URL. Trusted proxies are configured in `app/bootstrap/app.php` so Dokploy's TLS termination is honoured.
- Keep `QUEUE_CONNECTION=sync` until a worker is configured — `database` without a worker silently queues work that never runs.
- `MAIL_MAILER=log` is an honest placeholder; nothing sends mail yet. Password reset and notifications arrive with a real provider.

## 4. First deploy

1. Deploy the application.
2. Open the Dokploy terminal for the container and run:
   ```sh
   php artisan migrate --force
   ```
3. Create the owner account deliberately (never seed a default password):
   ```sh
   php artisan tinker --execute="\App\Models\User::updateOrCreate(['email' => 'you@example.com'], ['name' => 'Owner', 'password' => Illuminate\Support\Facades\Hash::make('choose-a-strong-password'), 'is_admin' => true]);"
   ```
4. Sign in at `https://your-domain.example/mdosi`.

## 5. Storage and operations

- **Article images need a persistent volume.** In Dokploy, add a volume to the application mounted at `/var/www/html/storage/app/public`. Mount that directory, not all of `storage/app`: a broader mount hides the framework's cache/session directories from the image and causes permission surprises. The `public/storage` symlink is created during the image build; the volume only holds uploaded files.
- Back up that volume as well as MySQL — a database restore without the matching images leaves articles with broken pictures. Test both restores before launch.
- Logs go to `storage/logs` via the daily channel; add volume or log shipping if you need retention.
- The public site is deliberately `noindex, nofollow` until launch; indexing is switched on deliberately later.

## 6. Local image check

```sh
docker build -t plusthree:local .
docker run --rm -p 8081:80 \
  -e APP_ENV=production -e APP_DEBUG=false \
  -e APP_KEY="$(cd app && php artisan key:generate --show)" \
  -e APP_URL=http://127.0.0.1:8081 \
  -e DB_HOST=host.docker.internal -e DB_PORT=3307 \
  -e DB_DATABASE=plusthree -e DB_USERNAME=plusthree -e DB_PASSWORD=<local-password> \
  plusthree:local
```

Then open `http://127.0.0.1:8081/`. On Linux, `host.docker.internal` requires `--add-host=host.docker.internal:host-gateway`.

## 7. Known limits of this deployment

- Single container, no queue worker, no scheduler — none are needed yet.
- Sessions and cache use the MySQL database.
- Uploaded images live on the container's local disk and are lost on redeploy unless the `/var/www/html/storage/app/public` volume is mounted. No object storage is configured.
- Mail is logged, not delivered.
- Password recovery and Google sign-in are still on the TODO list.
