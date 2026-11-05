# Deployed (production) vs GitHub `main`

Snapshot taken: 2026-11-05.

Production was deployed from commit `1cf6ac0` ("Merge pull request #6") and then
edited in place on the server. This document lists what production contains that
GitHub `main` (`cb5696e`) did not, and how each difference is handled in this branch.

## Summary

| Area | File | Status |
|------|------|--------|
| Guest tournament save with audio/video settings | `app/Controllers/Api/TournamentController.php` | **Ported** (was production-only) |
| yt-dlp YouTube client selection | `app/Controllers/Api/TournamentController.php` | **Ported** (was production-only) |
| Group member query / reused-group count | `ParticipantsController.php`, `participant_helper.php` | Already on `main` (only whitespace differs) |
| Terms "Last Updated" date | `app/Views/terms.php` | Already on `main` |
| Email transport settings | `app/Config/Email.php` | **Not ported**, set via `.env` (see below) |
| YouTube cookies file path | `app/Config/UploadConfig.php` | **Not ported**, set via `.env` (see below) |
| Dependency lock | `composer.lock` | Not ported, production ran `composer update` in place |

## 1. Guest save loses `foreign_key_checks = 0` after DB reconnect (bug fix)

**Problem on `main`:** `TournamentController::save()` disables foreign key checks for
guest users (`user_id = 0`), because guest rows reference a user that does not exist.
When an audio or video setting is enabled, the controller calls `$db->reconnect()`
(media download can outlive the MySQL connection). Reconnecting opens a **new MySQL
session**, and the new session has foreign key checks enabled again. The following
`music_settings` insert then fails:

```
Cannot add or update a child row: a foreign key constraint fails
(`music_settings`, CONSTRAINT `music_settings_user_id_foreign` ...)
```

Result: a guest who saves a tournament with music or video enabled gets an error. The
tournament row is created, but its audio settings are not.

**Fix (from production):**
- New private helpers `configureGuestDbSession()`, `reconnectAndConfigureGuestDb()` and
  `restoreGuestDbSession()` that run `SET SESSION foreign_key_checks = 0/1` on the
  current connection.
- After every reconnect, the guest session setting is applied again.
- The body of `save()` is wrapped in `try { ... } finally { restore }`, so checks are
  switched back on even when the method returns early or throws.
- `user_id` is cast to `int`.
- `music_settings` is written with `$db->table('music_settings')->insert()`.
- Response arrays and long expressions are reformatted. There is no change to response
  shapes or messages.

Verified locally against a copy of the production database: on `main` the guest save
fails with the error above. With this branch it succeeds, and the MySQL query log
shows `SET SESSION foreign_key_checks = 0` issued again on the new connection before
the insert.

## 2. yt-dlp: force the YouTube `web` player client

Both yt-dlp commands (audio extraction and 480p video download) now pass:

```
--extractor-args youtube:player_client=web
```

Production needed this to keep downloads working from the server.

## 3. Configuration differences: set in `.env`, not in code

Production had these values hardcoded in config classes. They are environment-specific,
and one of them was a credential, so they are **not** committed. Set them in the
server's `.env` instead. CodeIgniter overrides config properties from `.env`
automatically.

### Email (`app/Config/Email.php`)

Production values were:

```
Email.protocol    = sendgrid
Email.SMTPHost    =
Email.SMTPPort    = 25
Email.SMTPCrypto  = tls
Email.mailType    = text
```

- `Email.protocol = sendgrid` is already in the production `.env`.
- The SMTP values are ignored when the protocol is `sendgrid`.
- `mailType` was `text` in production but `html` on `main`. Add `Email.mailType = text`
  to `.env` if production should keep plain-text mail.
- The SendGrid API key is read from the environment: `SendGridEmailService` and
  `EmailTestController` call `getenv('SENDGRID_API_KEY')`. Keep the key in `.env`
  only (`SENDGRID_API_KEY = ...`) and never hardcode it in a config class.

### YouTube cookies file (`app/Config/UploadConfig.php`)

yt-dlp needs a cookies file. Set the path in `.env`. The key must be lowercase
`uploadconfig`:

```
uploadconfig.cookiesPath = /absolute/path/outside/the/web/root/cookies.txt
```

Keep the cookies file **outside** any web-served directory. It holds a logged-in session.

## 4. Known issue, not changed here

New `music_settings` rows get `created_at` and `updated_at` values of
`0000-00-00 00:00:00`. This happens both through `AudioSettingModel` and through the
direct table insert, so this branch does not cause it. Worth a separate fix.

## Deploying this branch to production

1. Add `uploadconfig.cookiesPath` (and optionally `Email.mailType`) to the server `.env`.
2. Confirm `SENDGRID_API_KEY` is supplied via `.env` (not committed to the repo).
3. Deploy with git, so the server matches GitHub again and is not hand-edited.
