# Filament Reveal

<p align="center">
  <img src="https://raw.githubusercontent.com/RawanD201/filament-reveal/main/docs/filament_reveal.jpeg" alt="Filament Reveal" width="520" />
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)
[![Total Downloads](https://img.shields.io/packagist/dt/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)
[![License](https://img.shields.io/packagist/l/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)

[![CI](https://github.com/rawand201/filament-reveal/actions/workflows/ci.yml/badge.svg)](https://github.com/rawand201/filament-reveal/actions/workflows/ci.yml)

A secure, production-ready Filament package for safely revealing sensitive data — API keys, tokens, emails, and secrets — on demand in both **tables** and **infolists**, with optional password authentication, rate limiting, audit logging, and full dark mode support.

### Demo screencasts

**Main demo** — mask, toggle, reveal, copy:

<img src="https://raw.githubusercontent.com/RawanD201/filament-reveal/main/docs/screencasts/main-demo.gif" alt="Main demo screencast" width="1000" />

**Authentication** — `requiresAuthentication()`, password modal:

<img src="https://raw.githubusercontent.com/RawanD201/filament-reveal/main/docs/screencasts/authenticate.gif" alt="Authentication screencast" width="1000" />

**Infolist** — `RevealEntry` on a view page:

<img src="https://raw.githubusercontent.com/RawanD201/filament-reveal/main/docs/screencasts/infolist.gif" alt="Infolist screencast" width="1000" />

---

## Features

- **Hidden by default** — sensitive values are masked with bullets, asterisks, or a custom string
- **Tables & Infolists** — `RevealColumn` for tables, `RevealEntry` for infolist view pages
- **Click to reveal** — eye-icon toggle fetches the real value via an encrypted AJAX request
- **Password authentication** — optionally require the user to enter their password before revealing
- **Click to copy** — click the revealed value to copy it to the clipboard
- **Security-first tokens** — each token is AES-256 encrypted, session-bound, user-bound, and expires in 5 minutes
- **Rate limiting** — configurable per-user request throttle (default 10/min)
- **Audit logging** — every reveal attempt (success or failure) is logged with user, model, IP, and timestamp
- **Laravel events** — fire `ColumnRevealed`, `ColumnRevealFailed`, and `UnauthorizedRevealAttempt` for security monitoring
- **Column whitelist** — only columns you explicitly allow can ever be revealed
- **Custom authorization** — override `authorizeRevealColumn()` per model for fine-grained access control
- **Multi-language** — ships with English and Arabic; easily extensible
- **Dark mode** — full Filament dark mode support
- **Zero build step** — no Vite/npm required in your project

---

## Requirements

| | Version |
|---|---|
| PHP | ^8.2 (8.2 / 8.3 / 8.4 / 8.5) |
| Laravel | 10.x / 11.x / 12.x / 13.x |
| Filament | ^4.0 \| ^5.0 |

---

## Installation

```bash
composer require rawand201/filament-reveal
```

The service provider is auto-discovered. No manual registration needed.

### Publish assets

```bash
php artisan filament:assets
```

### Publish the config (optional)

```bash
php artisan vendor:publish --tag="filament-reveal-config"
```

### Publish translations (optional)

```bash
php artisan vendor:publish --tag="filament-reveal-translations"
```

---

## Quick Start

### 1 — Add the trait to your model

```php
use Rawand\FilamentReveal\Concerns\HasRevealableColumns;

class User extends Authenticatable
{
    use HasRevealableColumns;

    // Whitelist of columns that are allowed to be revealed
    protected array $revealableColumns = [
        'api_token',
        'email',
    ];
}
```

### 2 — Add `RevealColumn` to your Filament table

```php
use Rawand\FilamentReveal\Columns\RevealColumn;

public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('name'),

        RevealColumn::make('email'),

        RevealColumn::make('api_token')
            ->maskAsterisk()
            ->revealedColor('success')
            ->requiresAuthentication(),
    ]);
}
```

### 3 — Add `RevealEntry` to your Filament infolist (optional)

```php
use Rawand\FilamentReveal\Entries\RevealEntry;

public static function infolist(Schema $schema): Schema
{
    return $schema->components([
        RevealEntry::make('email'),

        RevealEntry::make('api_token')
            ->maskAsterisk()
            ->revealedColor('success')
            ->requiresAuthentication(),
    ]);
}
```

That's it. Values show `••••••••` by default and reveal when the eye icon is clicked.

---

## Column & Entry API

`RevealColumn` and `RevealEntry` share the same API.

### Mask

Control how the value is hidden before it is revealed.

```php
->maskBullet()       // ••••••••  (default)
->maskAsterisk()     // ********
->maskHash()         // ########
->mask('xxxx-xxxx') // custom string
```

Set the default globally in `config/filament-reveal.php`:

```php
'default_mask' => 'bullet', // 'bullet' | 'asterisk' | 'hash' | 'your-custom-string'
```

### Revealed color

The color applied to the value after it is revealed.

```php
->revealedColor('primary')  // blue (default)
->revealedColor('success')  // green
->revealedColor('warning')  // yellow
->revealedColor('danger')   // red
->revealedColor('info')     // light blue
->revealedColor('gray')     // gray
```

### Password authentication

Require the user to enter their account password before the value is revealed. A modal appears, collects the password, verifies it server-side, and only then fetches the real value.

```php
->requiresAuthentication()

// Or pass a boolean/closure
->requiresAuthentication(fn () => auth()->user()->isAdmin())
```

Enable for all columns/entries by default in config:

```php
'require_authentication' => true,
```

---

## Model — `HasRevealableColumns` Trait

### `$revealableColumns`

Explicit whitelist. Only columns listed here can ever be revealed, regardless of what the frontend requests.

```php
protected array $revealableColumns = [
    'api_token',
    'secret_key',
    'email',
];
```

### `authorizeRevealColumn()`

Override to add your own authorization logic. Return `false` to block the reveal.

```php
public function authorizeRevealColumn(string $column, ?Authenticatable $user = null): bool
{
    // Only super-admins can reveal secret keys
    if ($column === 'secret_key') {
        return $user?->hasRole('super-admin') ?? false;
    }

    // Users can reveal their own data; admins can reveal anyone's
    return $this->id === $user?->id || $user?->hasRole('admin');
}
```

### `getRevealableColumnValue()`

Override to transform the value before it is sent to the browser.

```php
public function getRevealableColumnValue(string $column): mixed
{
    $value = parent::getRevealableColumnValue($column);

    if ($column === 'api_token') {
        return 'Bearer ' . $value;
    }

    return $value;
}
```

---

## Configuration

Full reference for `config/filament-reveal.php`:

```php
return [
    // Color of the revealed value
    'revealed_color' => 'primary', // primary | success | warning | danger | info | gray

    // Default mask style
    'default_mask' => 'bullet', // bullet | asterisk | hash | custom-string

    // Eye icon size
    'icon_size' => 'md', // sm | md | lg

    // Allow searching masked columns (disabled for security)
    'searchable' => false,

    // Require password before revealing (global default)
    'require_authentication' => false,

    // Token validity window in seconds
    'token_expiry' => 300, // 5 minutes

    // Per-user rate limiting
    'rate_limit' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    // Log successful reveals to Laravel's log channel
    'audit_logging' => true,

    // Log failed / unauthorized reveal attempts
    'log_failed_attempts' => true,

    // Include the client IP in log entries and events
    'log_ip_address' => true,

    // Middleware applied to the reveal endpoint
    'middleware' => [
        'web',
    ],
];
```

---

## Security Model

### How tokens work

When a table or infolist is rendered, each `RevealColumn` / `RevealEntry` cell receives a short-lived encrypted token instead of the actual value. The token is generated server-side and contains:

| Field | Content |
|---|---|
| `r` | Record ID |
| `c` | Column name |
| `m` | Model class |
| `t` | Issued-at timestamp |
| `s` | HMAC of the current session ID |
| `u` | Authenticated user ID |

The token is encrypted with AES-256-CBC using your `APP_KEY`. Even though it is visible in the page source, its contents cannot be read or tampered with without the key.

### What happens when the eye icon is clicked

1. Alpine.js POSTs the token to the obfuscated endpoint (`/x-fr-{hash}`)
2. The server verifies:
   - The user is authenticated (all configured guards are checked)
   - The token decrypts successfully
   - The token has not expired (default 5 min)
   - The token's session fingerprint matches the current session
   - The token's user ID matches the authenticated user
   - The column is in the model's `$revealableColumns` whitelist
   - `authorizeRevealColumn()` returns `true`
3. The rate limiter is checked (default 10 requests/minute per user)
4. The real value is returned and rendered in the browser
5. A `ColumnRevealed` event is fired and the attempt is logged

### Defence layers

| Layer | What it prevents |
|---|---|
| AES-256 encrypted token | Token contents cannot be read or forged |
| Session binding | A token stolen from devtools cannot be used in a different browser |
| User binding | Admin A cannot use a token generated for Admin B |
| 5-minute expiry | Captured tokens become worthless quickly |
| Column whitelist | Cannot reveal columns not explicitly allowed on the model |
| `authorizeRevealColumn()` | Per-record, per-column, per-user access control |
| CSRF token required | Cross-site request forgery blocked |
| Rate limiting | Brute force and enumeration attacks throttled |
| Obfuscated endpoint | Endpoint URL is not guessable without the `APP_KEY` |
| Audit logging | Every attempt is traceable |

---

## Laravel Events

Listen to these events for security monitoring, alerting, or additional logging.

### `ColumnRevealed` — successful reveal

```php
use Rawand\FilamentReveal\Events\ColumnRevealed;

Event::listen(ColumnRevealed::class, function (ColumnRevealed $event) {
    // $event->user         — the authenticated user
    // $event->modelClass   — e.g. "App\Models\User"
    // $event->recordId     — e.g. "42"
    // $event->columnName   — e.g. "api_token"
    // $event->ipAddress    — client IP (if log_ip_address is true)
    // $event->metadata     — ['user_agent' => '...']
});
```

### `UnauthorizedRevealAttempt` — authorization failed

```php
use Rawand\FilamentReveal\Events\UnauthorizedRevealAttempt;

Event::listen(UnauthorizedRevealAttempt::class, function ($event) {
    Log::critical('Unauthorized reveal attempt', [
        'user_id' => $event->user->id,
        'column'  => $event->columnName,
        'ip'      => $event->ipAddress,
    ]);
});
```

### `ColumnRevealFailed` — token invalid, expired, or rate-limited

```php
use Rawand\FilamentReveal\Events\ColumnRevealFailed;

Event::listen(ColumnRevealFailed::class, function ($event) {
    Log::warning('Reveal failed', ['reason' => $event->reason]);
});
```

---

## Advanced Examples

### Role-based column access

```php
public function authorizeRevealColumn(string $column, ?Authenticatable $user = null): bool
{
    return match ($column) {
        'secret_key' => $user?->hasRole('super-admin'),
        'api_token'  => $user?->hasAnyRole(['admin', 'developer']),
        default      => $user !== null,
    };
}
```

### Own-record-only access

```php
public function authorizeRevealColumn(string $column, ?Authenticatable $user = null): bool
{
    return $this->getKey() === $user?->getKey()
        || $user?->hasRole('admin');
}
```

### Environment-conditional authentication

```php
RevealColumn::make('api_token')
    ->requiresAuthentication(fn () => app()->isProduction())
```

---

## Translations

The package ships with **English** (`en`) and **Arabic** (`ar`). Add your own by publishing and creating a new locale file:

```bash
php artisan vendor:publish --tag="filament-reveal-translations"
```

This creates `lang/vendor/filament-reveal/{locale}/reveal-column.php`. Copy an existing file and translate the values.

---

## Frequently Asked Questions

**Does the actual value ever appear in the page HTML?**
No. Only an encrypted token is embedded in the page. The real value is fetched via a separate authenticated AJAX request, only when the user explicitly clicks the eye icon.

**Can I use it outside a Filament Resource?**
Yes — any Filament table that uses `InteractsWithTable` supports `RevealColumn`, and any infolist that uses `InteractsWithInfolists` supports `RevealEntry`. This includes pages, relation managers, and widgets.

**What if my app uses a non-default auth guard?**
The controller and the authentication modal both iterate over all guards defined in `config/auth.guards`, so custom guards work automatically.

**Does it work with Filament multi-tenancy?**
Yes. Add your tenancy check inside `authorizeRevealColumn()` to restrict reveals to the current tenant's records.

**What happens if `APP_KEY` rotates?**
All existing tokens become invalid immediately (they fail decryption). New tokens are issued on the next page load. No data is lost — only the short-lived reveal tokens are affected.

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Compatibility

- **PHP**: ^8.2 (8.2, 8.3, 8.4, 8.5)
- **Laravel**: 10.x / 11.x / 12.x / 13.x
- **Filament**: ^4.0 \| ^5.0

## Support

- **Bug reports & feature requests**: please use GitHub Issues.
- **Security reports**: please follow [SECURITY](SECURITY.md) (do not disclose publicly).

## Upgrade guide

- **Patch/minor releases** (e.g. `1.0.x` → `1.1.x`): update via Composer as usual.
- **Major releases** (e.g. `1.x` → `2.x`): check [CHANGELOG](CHANGELOG.md) for breaking changes and upgrade notes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Code of Conduct

Please see [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
