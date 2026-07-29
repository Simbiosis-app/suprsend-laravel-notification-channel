# Changelog

All notable changes to `suprsend` will be documented in this file

## 2.0.0 - Unreleased

### Added

- Support for Laravel 13.x.
- Package auto-discovery, so `SuprSendServiceProvider` no longer needs to be registered by hand.
- Optional constructor injection: `SuprSendChannel` accepts a `SuprSendClient`, and
  `SuprSendClient` accepts a `GuzzleHttp\Client`. Both still build their own default when
  nothing is passed, so existing usage is unchanged.
- A real test suite (59 tests) running against Laravel 12 and 13 via `orchestra/testbench`.

### Changed

- **Breaking:** minimum PHP version is now 8.3 (supported: 8.3 – 8.5).
- **Breaking:** dropped support for Laravel 6.x – 11.x. Supported: 12.x and 13.x.
- **Breaking:** added return and parameter types across `src/`. Subclasses that override
  `SuprSendChannel::send()`, `appendNewUser()` or `createUniqueIdFor()` must add matching
  return types.
- `SuprSendClient::triggerEvent()` and `appendUser()` now return the PSR-7 response instead of
  `null`.
- `SuprSendMessage::create()`, `eventName()`, `data()` and `notifiable()` return `static`
  instead of `self`, so subclasses keep their own type.
- The channel driver closure in `SuprSendServiceProvider` is now declared `static`, so
  Laravel 13's `Manager::extend()` rebinding leaves `$this` unbound rather than binding the
  closure to the `ChannelManager`.
- Migrated `phpunit.xml.dist` to the PHPUnit 12 schema; dev dependency is now
  `phpunit/phpunit ^11.5 || ^12.0`.
- The GitHub Actions matrix now covers PHP 8.3 / 8.4 / 8.5 against Laravel 12 and 13, plus
  `--prefer-lowest` runs.

### Removed

- The placeholder `ExampleTest`.

## 1.0.0 - 201X-XX-XX

- initial release
