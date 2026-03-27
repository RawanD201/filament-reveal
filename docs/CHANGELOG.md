# Changelog

All notable changes to `filament-reveal` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-02-06

### Added
- 🎉 Initial stable release
- 🔐 **Security Features:**
  - Encrypted tokens with configurable expiry
  - Obfuscated route endpoints using HMAC
  - Random column IDs on each render
  - Rate limiting (10 requests/minute per user)
  - Multi-guard authentication support
  - Trait-based protection (`HasRevealableColumns`)
  - Column whitelist (`$revealableColumns`)
  - Custom authorization per column
  - IP address logging
  - Comprehensive audit logging

- 📢 **Event System:**
  - `ColumnRevealed` - Successful reveal tracking
  - `ColumnRevealFailed` - General failure tracking
  - `UnauthorizedRevealAttempt` - Security critical events

- 🌍 **Internationalization:**
  - English (en) translations
  - Arabic (ar) translations
  - Easy-to-extend translation system

- ⚙️ **Configuration:**
  - Token expiry settings
  - Rate limit configuration
  - Audit logging toggles
  - IP logging controls
  - Customizable middleware
  - Default mask types
  - Color customization

- 🎨 **UI Features:**
  - Multiple mask types (bullet, asterisk, hash, custom)
  - Color-coded revealed text
  - Click-to-copy functionality
  - Loading states
  - Optional password authentication modal
  - Responsive design
  - Dark mode support

### Security
- No sensitive data in HTML
- Multi-layer authorization checks
- Rate limiting prevents brute force
- Comprehensive audit trail

### Fixed
- Multi-guard authentication support (removed `auth` middleware conflict)
- Color classes now properly documented with Tailwind safelist
- Translations properly namespaced

---

## [Unreleased]

### Planned
- Caching support for revealed data
- Database audit trail table (optional)
- Filament policy integration
- Bulk reveal actions
- Excel/CSV export handling
- More language translations
- Automated test suite

