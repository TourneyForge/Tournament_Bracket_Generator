# Dependency security upgrade (CodeIgniter 4.7 + patched packages)

Date: 2026-11-05.

## Why this change

Two tracks of publicly-disclosed vulnerabilities prompted this upgrade:

1. **PHP2Shell** (CVE-2026-109618 / CVE-2026-109619) — a pair of flaws in the PHP
   runtime itself (unauthenticated RCE chain, CVSS 9.2), fixed upstream in PHP
   8.2.35, 8.3.36, 8.4.27 and 8.5.12. This is fixed by running a patched, supported
   PHP version on the host; it is **not** fixed by application code, so it is out of
   scope for this repository. It is noted here only to explain why the app now
   targets a newer PHP baseline.

2. **Framework / dependency advisories** — the application was pinned to
   **CodeIgniter 4.6.x** and older library versions that carry their own published
   CVEs, notably:
   - **CVE-2026-63223** — upload-validation bypass in the `is_image` / `mime_in`
     rules (fixed in CodeIgniter 4.7.4).
   - **CVE-2026-48062** — upload extension bypass in the `ext_in` rule.
   - Transitive advisories in `guzzlehttp/guzzle` and `guzzlehttp/psr7`.

## What this PR changes

- `composer.json`: `php` → `^8.2`, `codeigniter4/framework` → `^4.7.4`.
- `composer.lock`: full update to patched releases — CodeIgniter **4.7.6**,
  Shield **1.4.1**, Guzzle **7.16.1**, psr7 **2.13.2**, and others. `composer audit`
  reports **no advisories**. Dependency resolution is pinned to the target runtime
  via `config.platform.php = 8.3.30`; Symfony is held on the 7.4 line (PHP-8.2
  compatible) rather than 8.x (which requires PHP 8.4).
- `app/Config/Format.php`: adds `public int $jsonEncodeDepth = 512;`. CodeIgniter
  4.7's `JSONFormatter` reads this new config property; without it JSON API
  responses fail. (Found and fixed during testing.)

## Deployment gate

This change requires **PHP ≥ 8.2**. Do **not** deploy it on a PHP 8.1 (or older)
host — `composer install` will refuse to resolve, and the app will not run. It must
ship together with moving the host to a supported, patched PHP branch (8.2 / 8.3 /
8.4).

## Testing

Verified against a copy of the application database:

- Unit tests pass; `spark routes` loads all routes.
- Home / login / register / terms pages render.
- The gallery, leaderboard, analysis and tournament-list JSON APIs return 200 with
  no new application-log errors.
- The guest create → add-participants → generate-brackets → view flow works
  end-to-end, and the scheduled `task:run` command completes cleanly.

---

*Operational and host-level security findings (server configuration, the live PHP
version, infrastructure hardening, and any incident-review notes) are tracked
privately by the maintainers and are intentionally kept out of this public
repository.*
