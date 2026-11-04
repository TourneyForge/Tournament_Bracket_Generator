# Security review: PHP2Shell (CVE-2026-109618 / CVE-2026-109619) and dependency CVEs

Date: 2026-11-05.

## TL;DR

- The production server runs **PHP 8.1.34**, which is **end-of-life** (upstream
  security support ended 2025-12-31) and has **no official fix** for PHP2Shell.
  The site is therefore **exposed** to PHP2Shell, an actively-exploited,
  unauthenticated RCE chain (CVSS 9.2) that has been hit in the wild since
  mid-October 2026.
- No evidence of compromise was found, but log retention is short and predates
  nothing useful before ~2026-10-11, while exploitation began ~2026-10-17, so
  "no evidence" is not a clean bill of health.
- The real fix is a **server-side PHP upgrade** off 8.1 to a patched branch
  (8.3.36 is already available on the box). This cannot be done from the repo.
- Separately, the app ran **CodeIgniter 4.6.x** and several dependencies with
  their own CVEs. This branch upgrades them to patched versions. Because
  CodeIgniter 4.7 requires **PHP ≥ 8.2**, this dependency upgrade must be
  deployed **together with** the server PHP upgrade, not before it.

## 1. The attack: PHP2Shell

Two flaws in the PHP runtime itself, exploited as a chain:

| CVE | Class | Trigger |
|-----|-------|---------|
| CVE-2026-109618 | Memory-exhaustion during request init → UAF | A POST with automatic body parsing, a reachable `memory_limit`, and array-syntax field names. Fails *before* any app code runs. |
| CVE-2026-109619 | Stale worker state across requests (UAF) | Upload processing via the session extension's upload-progress feature (`session.upload_progress.enabled`, on by default), even if the app never calls `session_start()`. |

Both give **command execution as the web worker user**, unauthenticated. Neither
is stopped by application authentication, because the vulnerable code runs inside
PHP's request setup, before the framework or any route.

Fixed PHP versions: 8.2.35, 8.3.36, 8.4.27, 8.5.12. **PHP 7.4 / 8.0 / 8.1 have no
official upstream release** — the only complete fix for those branches is to move
to a supported, patched branch. PHP states no configuration change fully mitigates
the flaws; mitigations only reduce exposure.

## 2. Are we vulnerable, and why

**Yes — the preconditions are met:**

| Factor | Observed on server | Effect |
|--------|--------------------|--------|
| Runtime | PHP **8.1.34** via **PHP-FPM** | EOL branch, no official PHP2Shell fix. Both CVEs are confirmed exploitable on PHP-FPM. |
| `session.upload_progress.enabled` | **not set → default ON** | Enables the CVE-2026-109619 upload path. |
| `post_max_size` / `upload_max_filesize` | **3G / 2G** | Large bodies allowed, easing the CVE-2026-109618 memory-exhaustion trigger. |
| `memory_limit` | 128M | Reachable/exhaustible, which is what the first CVE needs. |
| `disable_functions` | only `pcntl_*` | `exec`, `shell_exec`, `system`, etc. are **enabled**, so a code-exec primitive can run OS commands directly. |
| `open_basedir` | set to the site paths | Good — limits file reach, but does not stop the RCE. |

The application has public, unauthenticated upload endpoints (participant image,
group image, audio, video, CSV), which are exactly the kind of multipart-POST
surface PHP2Shell abuses — though note the flaw is in PHP, not in these handlers:
patching the app does not remove it.

## 3. Compromise check (performed 2026-11-05)

Checked, with **no indicators found**:

- No PHP memory-exhaustion fatals (`Allowed memory size ... exhausted ... Unknown
  on line 0`) in PHP-FPM / web error logs.
- No worker segfaults (`SIGSEGV` / `Segmentation fault`).
- No `PHP_SESSION_UPLOAD_PROGRESS` markers or multi-MB POSTs in access logs.
- No PHP/phtml/phar files anywhere under the uploads tree (every `<?=`/`<?php`
  byte-match was inside a genuine PNG / MP3 / CSV, confirmed with `file`).
- No web-shell patterns (`eval(base64_decode`, `system($_POST)`, …) in the webroot.
- No unexpected cron entries, no SUID/exec droppers in `/tmp`, `/var/tmp`,
  `/dev/shm`, no unexpected shell users, `authorized_keys` counts normal.
- No web-worker-spawned shells in the process snapshot.

Caveats:
- Access-log history only reaches ~2026-10-11 and rotates; exploitation began
  ~2026-10-17. A short, rotated-away attack burst could be invisible.
- The usual scanner noise is present (probes for `xmlrpc.php`, `wp-login.php`,
  `/vendor/phpunit/.../eval-stdin.php`, `wp-json/batch/v1`) — all returned 404,
  all generic bots, none PHP2Shell-shaped.

Unrelated good news: the kernel (6.8.0-147) is **already past** the Fragnesia
(CVE-2026-46300) fix (6.8.0-124), so that LPE half of the public chain is closed.

## 4. Secondary findings: framework & dependency CVEs

The app ran **CodeIgniter 4.6.x**. Relevant advisories fixed in **4.7.4+**:

- **CVE-2026-63223** (critical) — `is_image` / `mime_in` upload-validation bypass,
  RCE in some configs. The audio/video upload endpoints here use `mime_in`.
- **CVE-2026-48062** (critical) — `ext_in` upload extension bypass. The CSV import
  uses `ext_in`.
- **CVE-2026-63221** (critical) — SQLi in `deleteBatch()` (not used by this app).
- **CVE-2026-63222** (high) — path traversal in `UploadedFile::move()` with
  client-supplied names. This app uses `store()` (random names), so lower risk.
- **CVE-2026-63220** (medium) — spoofable forwarded-HTTPS headers.

`composer audit` also flagged ~40 advisories across transitive packages
(notably `guzzlehttp/guzzle` and `guzzlehttp/psr7`, pulled in by the Google and
SendGrid SDKs).

## 5. The fix

### 5a. Server — the actual PHP2Shell fix (must be done on the host)

The only complete fix is to stop serving the site on EOL PHP 8.1. The box already
has PHP 8.3 installed, and the PHP2Shell-patched build (8.3.36) is available from
the package repo. Outline (run by an operator, with confirmation):

1. Refresh packages and upgrade the installed PHP 8.3 (and 8.2/8.4) FPM+CLI to the
   patched builds: `8.2.35+ / 8.3.36+ / 8.4.27+`.
2. Switch the site's PHP-FPM pool/handler from `php8.1-fpm` to `php8.3-fpm`.
3. Restart **every** PHP-FPM worker (old workers keep the vulnerable runtime).
4. Interim mitigation only if the upgrade must wait: set
   `session.upload_progress.enabled = 0` (and on PHP 8.5, `fatal_error_backtraces = 0`)
   in the FPM `php.ini`, then restart FPM. This *reduces* exposure; it is not a fix.
5. Also tighten `disable_functions` and move the site to a maintained PHP branch
   permanently so future PHP security releases actually apply.

> These are production, outward-facing changes. They are intentionally **not**
> automated from this repository.

### 5b. Repo — dependency upgrade (this branch, tested)

- `composer.json`: `php` → `^8.2`, `codeigniter4/framework` → `^4.7.4`.
- `composer.lock`: full update to patched versions — CodeIgniter **4.7.6**,
  Shield **1.4.1**, Guzzle **7.16.1**, psr7 **2.13.2**, and others.
  `composer audit` → **no advisories**. Resolution is pinned to the target runtime
  via `config.platform.php = 8.3.30`, and Symfony stays on the 7.4 line
  (PHP-8.2-compatible) rather than 8.x (which needs PHP 8.4).
- `app/Config/Format.php`: added `public int $jsonEncodeDepth = 512;` — CI 4.7's
  `JSONFormatter` reads this new config property; without it every JSON API
  response 500s. (Found and fixed during testing.)

Verified locally against a copy of the production database: unit tests pass,
`spark routes` loads all 99 routes, and the home/login/register/terms pages plus
the gallery, leaderboard, analysis and tournament-list JSON APIs all return 200
with zero new errors in the application log.

**Deployment gate:** this branch requires **PHP ≥ 8.2**. Do **not** deploy it while
the server is still on PHP 8.1 — `composer install` would refuse, and the app would
break. Ship it together with step 5a.

## 6. Other hardening already noted (separate from PHP2Shell)

- `database.sql` and `cookies.txt` are downloadable from the public web root —
  remove them from any web-served directory. (Most urgent, unrelated to PHP2Shell.)
- One shared password across SSH/root/panel/DB; enable key-only SSH and give each
  account its own secret.
- Site runs with `CI_ENVIRONMENT = development` — switch to `production`.
- A full `vendor/` (including `phpunit`) sits under the document root; keep only
  `public/` web-served, or block `vendor/` at the web server.
