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

# Leave false until real content is published and the domain is final.
# false serves "noindex, nofollow" and a robots.txt that blocks everything.
SEO_INDEXABLE=false

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

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=info@plusthree.io
MAIL_FROM_NAME="Plusthree"
MAIL_SENDER_ADDRESSES=info@plusthree.io,hello@plusthree.io
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxx

BCRYPT_ROUNDS=12

# Panel sign-in with Google. Comma-separated allowlist; empty disables it.
GOOGLE_LOGIN_ENABLED=false
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=https://your-domain.example/mdosi/auth/google/callback
ADMIN_EMAILS=owner@your-domain.example
```

Notes:

- `APP_URL` must be the final HTTPS URL. Trusted proxies are configured in `app/bootstrap/app.php` so Dokploy's TLS termination is honoured.
- `SEO_INDEXABLE=false` serves `noindex, nofollow` on every page and a `robots.txt` that blocks crawling. Set it to `true` only when the domain is final and real content is published; `APP_DEBUG=true` forces it back off. Filtered job searches stay `noindex` either way, and `/mdosi` is always disallowed.
- Keep `QUEUE_CONNECTION=sync` until a worker is configured — `database` without a worker silently queues work that never runs.
- `MAIL_MAILER=resend` sends through Resend; `RESEND_API_KEY` is required and `MAIL_FROM_ADDRESS` must be on a domain verified in Resend (SPF/DKIM records added at Cloudflare). With `MAIL_MAILER=log` locally, panel emails and password-reset links are written to `storage/logs/laravel.log` instead of being delivered.
- `MAIL_SENDER_ADDRESSES` is the comma-separated list of verified addresses the panel may send from. With two or more, each email gets a **Send from** choice; every listed address must be on the verified Resend domain. The first one is the default.
- The same mailer sends **Forgot password?** links from `/mdosi/login`. The link goes to the address on the admin account, so that address must be one the owner can read.
- The panel sends synchronously, one recipient per provider call, and stores the result on the record. There is no queue worker, so do not switch these sends to `Mail::queue()` without adding one.

## 4a. Receiving mail

Resend sends; it does not receive. To read replies to `info@plusthree.io`, use Cloudflare Email Routing (free) on the same domain: **Email → Email Routing → Enable**, add the destination address you actually read, then create a route `info@plusthree.io → your inbox`. Cloudflare adds the MX record, which does not conflict with Resend because Resend verifies a sending domain with TXT/DKIM records.

Employer enquiries submitted through `/for-employers` do not depend on this: they are stored in the database and appear in `/mdosi/messages`.


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

## 5. Google sign-in

The panel accepts Google accounts only from the `ADMIN_EMAILS` allowlist. Everyone else is refused at the callback: no account is created, no session starts, and the attempt is logged. A newly created account is an admin; an existing account keeps whatever `is_admin` it already had.

1. Google Cloud Console → create a project → **APIs & Services → OAuth consent screen** (External). Add your address as a test user while the app is unverified.
2. **Credentials → Create credentials → OAuth client ID → Web application.**
   - Authorised JavaScript origin: `https://your-domain.example`
   - Authorised redirect URI: `https://your-domain.example/mdosi/auth/google/callback`
3. Put the client ID and secret into Dokploy as `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`, set `ADMIN_EMAILS`, set `GOOGLE_LOGIN_ENABLED=true` when you want the button, and redeploy. While the flag is false the button stays hidden and the routes return 503.

The password login stays enabled as the break-glass route, so a broken OAuth client cannot lock you out.

## 6. Domain and DNS

Dokploy terminates TLS with Traefik and requests the certificate itself.

1. **Cloudflare DNS.** Add an `A` record for the apex (`@`) pointing at the server's public IPv4, and another for `www` if it should resolve. Leave the record **DNS only** (grey cloud) for the first attempt: a proxied record can mask a failing certificate challenge.
2. **Dokploy domain.** Application → Domains → Add Domain: host `your-domain.example`, path `/`, **internal port `80`** (the container's Apache port), HTTPS on, certificate provider Let's Encrypt. Add `www` as a second entry if needed — each hostname gets its own certificate.
3. **Firewall.** Inbound `80` and `443` must reach the server. Traefik answers the ACME challenge on 80 and serves the site on 443.
4. **Verify before reproxying.**
   ```sh
   dig +short your-domain.example
   curl -I http://your-domain.example    # redirect to https
   curl -I https://your-domain.example   # 200 with a valid certificate
   ```
5. **Then proxy it.** Switch the record to Proxied (orange cloud) and set SSL/TLS → Overview → **Full (strict)**. *Flexible* causes redirect loops and leaves the origin connection unencrypted. Keep Always Use HTTPS on.
6. **Update the app.** `APP_URL=https://your-domain.example` and `SESSION_SECURE_COOKIE=true`, then redeploy. `APP_URL` feeds canonical URLs, the sitemap and signed preview links.

Common failures: a certificate stuck pending usually means a proxied record or a blocked port 80; "too many redirects" means Cloudflare is set to Flexible; one hostname working and the other failing means the missing DNS record or the missing Dokploy domain entry.

Do not set `SESSION_SECURE_COOKIE=true` before HTTPS is actually serving, or the panel login will silently fail.

### Troubleshooting a fresh deployment

- **502 from the domain.** The domain's **Container Port** is wrong. This image serves on **80**; Dokploy often defaults to `3000` for a new service. Edit the domain and set the port to 80.
- **500 from every page.** The environment is missing or the database has no tables. Check `DB_HOST` matches the MySQL service's **internal host** (shown on its General tab, e.g. `plusthree-db-3k1d77` — not the display name), then run `php artisan migrate --force` in the container.
- **`duplicate mount point` on deploy.** The same volume was added twice. Remove one entry under Advanced → Volumes and redeploy.
- **Uploads vanish after a redeploy.** The volume is missing or mounted at the wrong path; see §6.
- **`Could not open input file: artisan`** in the terminal. The container shell starts in `/`; run `cd /var/www/html` first.

## 7. Storage and operations

- **Article images need a persistent volume.** In Dokploy, add a volume to the application mounted at `/var/www/html/storage/app/public`. Mount that directory, not all of `storage/app`: a broader mount hides the framework's cache/session directories from the image and causes permission surprises. The `public/storage` symlink is created during the image build; the volume only holds uploaded files.
- Back up that volume as well as MySQL — a database restore without the matching images leaves articles with broken pictures. Test both restores before launch.
- Logs go to `storage/logs` via the daily channel; add volume or log shipping if you need retention.
- The public site is deliberately `noindex, nofollow` until launch; indexing is switched on deliberately later.

## 8. Local image check

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

## 9. Known limits of this deployment

- Single container, no queue worker, no scheduler — none are needed yet.
- Sessions and cache use the MySQL database.
- Uploaded images live on the container's local disk and are lost on redeploy unless the `/var/www/html/storage/app/public` volume is mounted. No object storage is configured.
- Mail is logged, not delivered.
- Password recovery and Google sign-in are still on the TODO list.
