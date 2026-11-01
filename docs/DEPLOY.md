# BuntyFun — Local dev, build, deploy, rollback

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # local only — production uses MySQL
php artisan migrate
php artisan db:seed                 # creates a super_admin; see "Local admin login" below
npm install
```

Run both in separate terminals:

```bash
php artisan serve
npm run dev
```

### Local admin login

`RoleUserSeeder` creates a `super_admin` at `ADMIN_EMAIL` (default `admin@buntyfun.com`).
- Locally, if `ADMIN_PASSWORD` isn't set, it generates a random password and prints it to the
  console — copy it from there.
- In any non-local/testing environment, it refuses to run without `ADMIN_PASSWORD` explicitly
  set — it will never seed a guessable production password.

## Building for deploy

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

This produces `public/build/` (hashed, versioned assets) — the only build artifact that needs
to ship; `node_modules` and the Vite dev server never go to production.

## Deploying to WebSouls cPanel (zip upload)

There is no git/SSH deploy pipeline on the server — same pattern as eDirect360. Each deploy is
a new, independently-rollback-able release:

1. On a clean checkout of the commit you're releasing, run the build steps above.
2. Create a release folder named `release/<short-commit-hash>/` containing the full app
   (vendor/, public/build/, everything except `.env`, `storage/*.key`, `node_modules`).
3. Zip that folder and upload it via cPanel File Manager, extracting it into
   `release/<short-commit-hash>/` on the server (never directly into the live document root).
4. Copy the server's existing `.env` into the new release folder (never overwrite it from git).
5. Point the domain's document root symlink (`current` → `release/<short-commit-hash>/public`)
   at the new release. If the account's cPanel plan doesn't support a document-root symlink,
   update the addon domain's document root setting directly to the new release's `public/`
   folder instead.
6. Run once, against the new release:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. Confirm the site loads correctly before deleting any old release folder.

Keep at least the previous two release folders on disk — they're what rollback uses.

## Rollback procedure

If a release is bad:

1. Re-point the document root symlink (or the addon domain's document root setting) back to
   the previous `release/<previous-hash>/public` folder.
2. If the bad release ran a migration that the previous release's code doesn't expect, run that
   migration's `down()` against the database, or restore the pre-deploy database backup — decide
   based on whether the migration is destructive. Never guess; check the migration file first.
3. Clear cached config/routes/views are release-specific (they live inside each release folder),
   so rolling back the symlink is enough — no cache-clear command needed on rollback itself.
4. Confirm the site loads correctly on the restored release before investigating the failed one.

Never delete a release folder until you've confirmed the *next* release works — rollback depends
on it still being on disk.
