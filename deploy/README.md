# deploy/

Deployment and auto-update tooling only - nothing else in this repo was
touched to add it. For the full walkthrough (Cloudflare Tunnel, GitHub
webhook, first-time server setup), see the separate "VitaDB Deploy Guide"
document that came with this.

| File | Purpose |
|---|---|
| `Dockerfile` | PHP 8.2 + Apache runtime image. App code is bind-mounted at runtime, not baked into the image. |
| `docker-compose.yml` | The app container + a MariaDB container. |
| `php-uploads.ini` | Enables short PHP tags (`<? ?>`), raises upload limits. |
| `apache-vitadb.conf` | Blocks direct HTTP access to `deploy/`, `.git/`, `config.php`, `*.env`. |
| `.env.example` | Template for the real secrets file, which lives *outside* this repo. |
| `update.sh` | `git reset --hard` to the latest commit on the deploy branch, then `docker compose up -d --build`. This is both the first-deploy command and the auto-update command. |
| `webhook_listener.py` | Stdlib-only HTTP server on :9010 that verifies GitHub's HMAC signature and runs `update.sh` on a push to the deploy branch. |
| `systemd/vitadb-webhook.service.example` | Reference systemd unit for the listener. |

Expected layout on the server:

```
~/vitadb/
├── VitaDB/          <- this git repo
├── vitadb.env       <- real secrets, chmod 600, NOT in git
└── config.php       <- real DB credentials, NOT in git, bind-mounted over
                        the repo's own placeholder config.php at runtime
```

**Known quirk inherited from upstream:** `screenshots.php`, `icon0.php`, and
`avatar.php` write uploads to a hardcoded absolute path from the original
author's shared hosting (`/customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/...`).
Rather than edit those files, `Dockerfile` creates that exact path and
`docker-compose.yml` mounts named volumes onto it, so uploads work without
touching the PHP.

**Not included:** a SQL schema/dump. `config.php` and this tooling assume a
database already exists with VitaDB's tables in it (e.g. a dump from an
existing install). Import it once after the first `docker compose up`.
