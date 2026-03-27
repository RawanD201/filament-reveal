# Filament Reveal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)
[![Total Downloads](https://img.shields.io/packagist/dt/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)
[![License](https://img.shields.io/packagist/l/rawand201/filament-reveal.svg?style=flat-square)](https://packagist.org/packages/rawand201/filament-reveal)

[![CI](https://github.com/rawand201/filament-reveal/actions/workflows/ci.yml/badge.svg)](https://github.com/rawand201/filament-reveal/actions/workflows/ci.yml)

A secure, production-ready Filament package for safely revealing sensitive data — API keys, tokens, emails, and secrets — on demand in both **tables** and **infolists**, with optional password authentication, rate limiting, audit logging, and full dark mode support.

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
| PHP | ^8.2 |
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

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Compatibility

- **PHP**: ^8.2
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

