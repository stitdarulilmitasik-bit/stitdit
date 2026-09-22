# ByetHost Auto Deploy

Workflow: `.github/workflows/deploy-byethost.yml`

## GitHub Actions secrets

Repository -> Settings -> Secrets and variables -> Actions -> New repository secret:

- `BYETHOST_FTP_SERVER` = FTP hostname from ByetHost
- `BYETHOST_FTP_USERNAME` = FTP username
- `BYETHOST_FTP_PASSWORD` = FTP password
- `BYETHOST_FTP_PORT` = `21`

The workflow deploys the Laravel project to `/httpdocs/` whenever code is pushed to `main`.

## First-time ByetHost setup

1. Create the MySQL database and user in ByetHost.
2. Import the existing SIAKAD database with phpMyAdmin.
3. Create the production `.env` directly on the server. Do not put it in GitHub.
4. Set at least:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://YOUR-DOMAIN/`
   - `APP_KEY=base64:...`
   - production MySQL host/database/user/password
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `QUEUE_CONNECTION=database` only if the queue tables and a worker/cron are available
5. Keep the Laravel project under `htdocs`. The repository's root `.htaccess` forwards web requests to `public/`.
6. The current ByetHost document root identified for this account is `/httpdocs/`. If the FTP account exposes that directory as the FTP root instead, use `server-dir: /`.

## Important

This workflow builds `vendor/` and `public/build/` on GitHub Actions, so Composer and Node.js do not need to run on ByetHost.

Database migrations, cache clearing, and other Artisan commands are intentionally not executed over FTP because FTP cannot execute PHP commands on the server. Run those from a server-side terminal/cron if the hosting plan provides one.

The production `.env` and uploaded files in `storage/app/public` are preserved by the deployment workflow.
