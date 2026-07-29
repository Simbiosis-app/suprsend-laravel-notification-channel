# Plan — Upgrade to Laravel 13

Target: support **Laravel 12 + 13** on **PHP ^8.3**, with a real test suite.
This is a semver-major for the package (drops Laravel 6–11 / PHP < 8.3).

Reference: [Laravel 13 release notes](https://laravel.com/docs/13.x/releases) ·
[12.x → 13.0 upgrade guide](https://laravel.com/docs/13.x/upgrade)

## Relevant Laravel 13 changes for this package

- **PHP 8.3 minimum** (supported 8.3 – 8.5). Released 2026-03-17.
- **`Manager::extend()` callback binding** — custom driver closures are now rebound to the
  manager instance via reflection, and `extend()` throws `RuntimeException` if binding fails.
  Directly affects the `Notification::resolved()` block in `SuprSendServiceProvider`.
- **`phpunit/phpunit` → `^12.0`** — forces a config-schema and test-metadata migration.
- Not applicable here: request forgery protection, cache `serializable_classes`,
  `upsert`/`DELETE JOIN`, queue events, pagination views, `Js::from`.

## Todos

- [x] Read the Laravel 13 release notes and upgrade guide
- [x] Audit the package source against the 13.x breaking-change list
- [x] Decide the support window (Laravel 12 + 13, PHP ^8.3)
- [x] Bump `composer.json` (php, illuminate/*, guzzle, dev deps)
- [x] Migrate `phpunit.xml.dist` from the PHPUnit 9 schema to PHPUnit 12
- [x] Add optional constructor injection to `SuprSendChannel` / `SuprSendClient` (test seams,
      backwards compatible)
- [x] Modernize `src/` for PHP 8.3 (typed returns, readonly/promoted props, `#[\Override]` n/a)
- [x] Write the test suite (see below)
- [x] Update the GitHub Actions matrix (PHP 8.3/8.4/8.5 × Laravel 12/13, prefer-lowest)
- [x] Update `README.md` requirements + `CHANGELOG.md`
- [x] Run the full suite green on Laravel 12 and Laravel 13

## Test suite

Using `orchestra/testbench` (`^10.0` for L12, `^11.0` for L13) so the tests boot a real
application container rather than mocking the framework.

- `SuprSendServiceProviderTest` — provider registers the `suprSend` driver through the real
  `ChannelManager`; covers the Laravel 13 rebound-callback path explicitly.
- `SuprSendChannelTest` — end-to-end `send()` against a Guzzle `MockHandler`: distinct-id
  derivation, user append, event trigger, notifiable payload, error wrapping.
- `SuprSendClientTest` — request shape: URI, headers, `Date`, bearer auth, JSON bodies for
  both `triggerEvent` and `appendUser`.
- `SuprSendMessageTest` — fluent builder.
- `CouldNotSendNotificationTest` — exception wrapping.
