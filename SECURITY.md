# Security Policy

## 🔒 Security Features

This package implements multiple layers of security to protect sensitive data:

### 1. **Trait-Based Protection**
- Only models using `HasRevealableColumns` trait can have revealable columns
- Explicit opt-in prevents accidental exposure

### 2. **Column Whitelisting**
- Columns must be explicitly listed in `$revealableColumns` array
- No wildcard or automatic revelation

### 3. **Custom Authorization**
- `authorizeRevealColumn()` method for fine-grained control
- Per-column, per-user authorization logic

### 4. **Encrypted Tokens**
- All parameters (model, record ID, column name) encrypted
- Uses Laravel's encryption with app key
- Configurable expiry time (default 5 minutes)

### 5. **Obfuscated Endpoints**
- Route URLs hashed using HMAC with app key
- Changes per application, not predictable
- Format: `/x-frc-{16-char-hash}`

### 6. **Rate Limiting**
- Configurable per-user request limits
- Prevents brute force attacks
- Default: 10 requests per minute

### 7. **Audit Logging**
- All reveal attempts logged with:
  - User ID
  - IP address
  - Timestamp
  - Model and column
  - Success/failure status

### 8. **Event System**
- Real-time notifications of security events
- `UnauthorizedRevealAttempt` for critical alerts
- Integration with monitoring systems

### 9. **No Data in HTML**
- Sensitive data never sent to frontend
- Only encrypted tokens in markup
- Data fetched on-demand via secure API

### 10. **Random Column IDs**
- Column IDs randomized on each render
- Prevents tracking or targeting specific columns

## 🚨 Reporting Security Vulnerabilities

**Please DO NOT report security vulnerabilities publicly.**

If you discover a security issue, please use **GitHub Security Advisories** for private disclosure.
If you can’t use advisories, email: **rawandrasool@proton.me**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will:
1. Acknowledge receipt within 24 hours
2. Investigate and confirm within 72 hours
3. Release a fix as soon as possible
4. Credit you in the changelog (if desired)

## 🛡️ Security Best Practices

### ✅ DO

1. **Enable audit logging in production**
```php
'audit_logging' => true,
'log_ip_address' => true,
```

2. **Implement custom authorization**
```php
public function authorizeRevealColumn(string $column, $user): bool
{
    return $user->hasRole('admin');
}
```

3. **Use strong rate limits**
```php
'rate_limit' => [
    'max_attempts' => 5,
    'decay_minutes' => 5,
],
```

4. **Monitor security events**
```php
Event::listen(UnauthorizedRevealAttempt::class, SecurityListener::class);
```

5. **Keep tokens short-lived**
```php
'token_expiry' => 300, // 5 minutes
```

### ❌ DON'T

1. **Don't reveal sensitive columns without authorization**
```php
// BAD
public function authorizeRevealColumn(string $column, $user): bool
{
    return true; // Allows anyone!
}
```

2. **Don't disable rate limiting**
```php
// BAD
'rate_limit' => null, // No protection!
```

3. **Don't ignore security events**
```php
// BAD - At least log unauthorized attempts
Event::listen(UnauthorizedRevealAttempt::class, function($e) {
    // Do nothing - security blind spot!
});
```

4. **Don't make tokens too long-lived**
```php
// BAD
'token_expiry' => 86400, // 24 hours - too long!
```

5. **Don't expose revealable columns to unauthenticated users**
```php
// BAD
'middleware' => ['web'], // Missing 'auth'!
```

## 🔍 Security Checklist

Before deploying to production:

- [ ] Audit logging enabled
- [ ] IP logging enabled
- [ ] Rate limiting configured
- [ ] Custom authorization implemented
- [ ] Security events monitored
- [ ] Token expiry set appropriately
- [ ] Middleware includes `auth`
- [ ] Only necessary columns whitelisted
- [ ] SSL/TLS enabled (HTTPS)
- [ ] App key is strong and secret

## 📊 Monitoring

Set up monitoring for:

1. **Failed Reveal Attempts**
   - Alert on multiple failures from same IP
   - Track patterns of unauthorized access

2. **Rate Limit Hits**
   - May indicate attack attempts
   - Review and adjust limits if needed

3. **Token Expiry Errors**
   - May indicate slow networks
   - Consider increasing expiry if legitimate

4. **Authorization Failures**
   - Review authorization logic
   - May indicate privilege escalation attempts

## 🆕 Updates

- Keep package updated to latest version
- Subscribe to security advisories
- Review changelogs for security fixes

## 📜 Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## 🏆 Security Hall of Fame

We appreciate responsible disclosure. Security researchers who report valid vulnerabilities will be credited here (with permission).

## 📞 Contact

- Private disclosure: GitHub Security Advisories
- Email (fallback): rawandrasool@proton.me
