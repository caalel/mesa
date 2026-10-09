# MESA Production Deployment

This document covers MESA's production infrastructure, configuration, and
automated deployment process using Docker, GitHub Actions, and Northflank.

## Production Architecture

The public application is available at the
[live demo](https://mesa--web--gpbj9lmc4t6r.code.run).

| Resource | Responsibility |
| --- | --- |
| GitHub Actions | PHP tests, frontend build, current-commit check, and release webhook trigger. |
| Northflank Build Service `build` | Build or reuse the Docker image for the requested commit. |
| Northflank Job `migrate` | Run production migrations using that image and the private database connection. |
| Northflank Deployment Service `web` | Serve that image with one instance and Steady Rollout. |
| Private MySQL 8.4 addon `mysql` | Persist Foods, Users, Meals, MealItems, and sessions independently of container replacement. |

These resources belong to the Northflank project `mesa`. The Workflow
`production-deploy` runs `build-app`, `migrate`, and `deploy-web` sequentially.
The flow has been validated through successful manual, webhook-triggered, and
automated GitHub Actions releases. Independent Northflank build and deployment
triggers are disabled; GitHub Actions initiates releases after CI succeeds.

The [Dockerfile](../Dockerfile) has three stages: Composer installs locked PHP
dependencies without development packages or scripts; Node 22.12.0 runs `npm ci`
and `npm run build`; PHP 8.3 with Apache serves the final application. The image
includes `vendor`, compiled Vite assets, migrations, seeders, and the versioned
nutritional CSV. Local `.env` files and `node_modules` are excluded. Apache runs
as `apache2-foreground`, listens on port 80, and serves only `public/` with rewrite
support. `storage/` and `bootstrap/cache/` are writable by `www-data`.

Northflank terminates public HTTPS and forwards HTTP to port 80. Laravel exposes
`/up` as the readiness endpoint. It checks application boot and health event
listeners; MESA has no custom database health listener, so HTTP 200 does not prove
MySQL connectivity or dataset availability.

MySQL uses persistent storage and a private TLS connection. Database sessions
store guest Meals and authentication state outside the container, allowing session
continuity when the database, application key, and unexpired browser cookie are
retained. Account Meals are separate database records and remain after a session
expires.

The container filesystem is ephemeral: local cache, compiled views, temporary
files, and file-based maintenance state do not survive container replacement.
Runtime edits to dataset files are also lost. MESA stores no user-uploaded files
and requires no persistent application filesystem volume. Logs are sent to stderr.

## Configuration

Application credentials are stored as Northflank runtime environment variables.
The `web` service and `migrate` job share the same secret `APP_KEY` and database
configuration. The same application key is preserved across releases.
The configuration below describes production; [`.env.example`](../.env.example)
remains the local development template.

| Purpose | Production configuration |
| --- | --- |
| Laravel | `APP_NAME=MESA`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_LOCALE=pt_BR`, `APP_FALLBACK_LOCALE=en`; `APP_KEY` is a shared, stable secret. |
| URL / HTTPS | `APP_URL=https://mesa--web--gpbj9lmc4t6r.code.run`; forwarded protocol handling is described below. |
| Database | `DB_CONNECTION=mysql`; `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and secret `DB_PASSWORD` use the private addon connection values. |
| Database TLS | TLS is enabled on the addon. `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt` supplies the CA bundle through `config/database.php`. The addon flag `TLS_ENABLED` is not consumed by Laravel. |
| Sessions | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `SESSION_ENCRYPT=true`, `SESSION_PATH=/`, `SESSION_DOMAIN=null`, `SESSION_SECURE_COOKIE=true`. The default database connection and `sessions` table are used. |
| Logs | `LOG_CHANNEL=stderr`, `LOG_LEVEL=info`. Laravel writes to `php://stderr` for container log collection. |
| Cache / filesystem | `CACHE_STORE=file`, `FILESYSTEM_DISK=local`; local runtime files are disposable. |
| Queue | `QUEUE_CONNECTION=sync`; jobs execute synchronously, without a worker service. |
| Email | `MAIL_MAILER=log`; messages are logged rather than delivered. Current account flows do not include password recovery or email verification. |

Database session payloads are encrypted using `APP_KEY`.
`SESSION_DOMAIN=null` produces host-only session cookies.

### Trusted Proxies and Cookies

[`bootstrap/app.php`](../bootstrap/app.php) trusts all immediate proxies (`at: '*'`)
for **only** `Request::HEADER_X_FORWARDED_PROTO`. It does not trust forwarded client
IP, host, port, or prefix headers, and does not force the URL scheme. Northflank
receives HTTPS publicly and sends HTTP to Apache; Laravel reads
`X-Forwarded-Proto: https` to recognize the original protocol and generate HTTPS
URLs, avoiding Mixed Content. `APP_URL` alone does not correct incoming requests.

Public HTTPS tests validated this behavior and showed that the ingress overwrites
the header even when a client supplies `X-Forwarded-Proto: http`. Wildcard proxy
trust relies on this sanitization.

Production explicitly sets `SESSION_SECURE_COOKIE=true`. `SESSION_HTTP_ONLY`
and `SESSION_SAME_SITE` are not overridden, so `config/session.php` supplies
`true` and `lax`, respectively.

## Initial Provisioning

The production database was initialized from an application container connected
to the private MySQL addon, using:

```bash
php artisan migrate --seed --force
```

This command created the schema, including the `sessions` table from
`2026_10_01_000000_create_sessions_table.php`, then invoked `DatabaseSeeder`.
That seeder calls only `FoodSeeder`, which imports
`database/data/foods/taco-v4.csv` through `FoodImporter` with source `taco`,
version `4`. It does not create demo accounts or Meals. The current canonical CSV
contains 592 Foods and is included in the image; translation generation and CSV
preparation are not required during provisioning. Initial migrations and the
official dataset import have already been completed in production.

Normal releases run only:

```bash
php artisan migrate --force
```

They do not seed, regenerate datasets, generate a new application key, or reset
the database. `migrate:fresh` is excluded from production operations. Migrations
run in the Northflank migration job, not during Docker build or Apache startup.

## Automated Deployment

The [CI/CD workflow](../.github/workflows/ci-cd.yml) runs on pushes to `main` and
pull requests targeting `main`. Pull requests run CI only; pushes also make
deployment eligible after both jobs succeed.

1. A push to `main` starts `php-tests` and `frontend-build` independently.
2. `php-tests` runs the PHP test suite against MySQL, while `frontend-build`
   builds the production frontend assets.
3. The `deploy` job requires both jobs to succeed and runs only for pushes to
   `main`. It compares `github.sha` with the remote branch HEAD and skips outdated
   commits. A failed lookup prevents the webhook from being triggered.
4. For a current SHA, the job triggers the Northflank webhook using the secret
   `NORTHFLANK_WEBHOOK_URL`, passing the SHA as `commit_sha`. The job requires
   an HTTP 2xx response; missing secrets or request failures fail the job.
5. Northflank `production-deploy` builds or reuses the image for that commit
   in `build-app`.
6. `migrate` runs `php artisan migrate --force` with that same image and production
   database configuration. The workflow advances only when migrations succeed.
7. `deploy-web` publishes the same image to `web`, where rollout/readiness checks
   determine whether it becomes available.

GitHub Actions has `contents: read` permission and receives only the release
webhook secret, not production database or application credentials. It does not
build a production Docker image, run production migrations, or poll Northflank.
A successful `deploy` job confirms webhook acceptance, **not completion of the
remote release**.

## Deployment Behavior

### Concurrency and Release Ordering

The GitHub Actions `deploy` job uses the concurrency group
`mesa-production-deploy` with `cancel-in-progress: false`. Jobs in this group do
not run simultaneously, and a running job is not canceled. Pending jobs can be
replaced, and execution order is not guaranteed.

GitHub Actions concurrency applies only until the webhook-triggering job ends;
it does not control the full remote release lifecycle. The Northflank Workflow
uses its own `Latest` concurrency policy.

Before triggering the webhook, the job compares the commit SHA with the current
HEAD of `main`. This check prevents commits that are already outdated from
triggering a release. A small race window remains if a new commit reaches main
between the check and the webhook request.

### Database Migrations and Rollback

Migrations run before the new image rollout, while the previous application
version may still serve requests. Schema changes therefore need to remain
compatible with that version, including if the new rollout fails.

A failed migration prevents the workflow from advancing to deployment. In MySQL,
it may leave partially applied schema changes. Reverting the application image
does not undo migrations that have already run.

### Data Persistence and Backups

Application data and Laravel sessions are stored in persistent MySQL storage,
independently of the application containers' ephemeral filesystem.

The MySQL addon has weekly snapshots with 14-day retention. Snapshot restoration
has been successfully tested in an isolated environment.
