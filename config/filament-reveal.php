<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Revealed Text Color
    |--------------------------------------------------------------------------
    |
    | The color to use for revealed text. You can use any Tailwind color class.
    | Examples: 'primary', 'success', 'warning', 'danger', 'info', 'gray'
    |
    */
    'revealed_color' => 'primary',

    /*
    |--------------------------------------------------------------------------
    | Default Mask Type
    |--------------------------------------------------------------------------
    |
    | The default mask type to use for masking hidden content.
    | Options: 'bullet', 'asterisk', 'hash', or a custom string
    |
    | Predefined masks:
    | - 'bullet'   => '••••••••'
    | - 'asterisk' => '********'
    | - 'hash'     => '########'
    |
    */
    'default_mask' => 'bullet',

    /*
    |--------------------------------------------------------------------------
    | Icon Size
    |--------------------------------------------------------------------------
    |
    | Size of the toggle icon.
    | Options: 'sm' (16px), 'md' (20px), 'lg' (24px)
    |
    */
    'icon_size' => 'md',

    /*
    |--------------------------------------------------------------------------
    | Searchable by Default
    |--------------------------------------------------------------------------
    |
    | Whether columns should be searchable by default.
    | For security, this is disabled by default to prevent searching masked data.
    | You can enable it per column using ->searchable(true)
    |
    */
    'searchable' => false,

    /*
    |--------------------------------------------------------------------------
    | Require Authentication
    |--------------------------------------------------------------------------
    |
    | Whether to require password authentication before revealing data.
    | When enabled, users must enter their password in a modal before seeing the data.
    | You can override this per column using ->requiresAuthentication(true/false)
    |
    */
    'require_authentication' => false,

    /*
    |--------------------------------------------------------------------------
    | Token Expiry (seconds)
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) the encrypted tokens are valid.
    | Shorter times = more secure, but tokens may expire before user clicks.
    | Recommended: 300 (5 minutes) for production
    |
    */
    'token_expiry' => 300,

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limit for reveal requests per user per minute.
    | This prevents brute force attacks and excessive API calls.
    | Set to null to disable rate limiting (not recommended).
    |
    */
    'rate_limit' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable audit logging of all reveal attempts.
    | When enabled, all reveal attempts (successful and failed) are logged.
    | Logs include: user_id, model, record_id, column, timestamp, IP address
    |
    */
    'audit_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Failed Attempt Logging
    |--------------------------------------------------------------------------
    |
    | Log failed authorization attempts separately for security monitoring.
    | Useful for detecting potential security breaches.
    |
    */
    'log_failed_attempts' => true,

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Additional middleware to apply to the reveal route.
    | Note: Do NOT add 'auth' middleware here as Filament uses separate guards.
    | The controller checks authentication across all guards automatically.
    |
    */
    'middleware' => [
        'web',
        // Don't add 'auth' - it conflicts with Filament's multi-guard auth
        // 'throttle:10,1', // Additional rate limiting
        // '2fa.verify',    // Two-factor authentication
        // 'ip.whitelist',  // IP whitelist
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Logging
    |--------------------------------------------------------------------------
    |
    | Log IP addresses with reveal attempts for security auditing.
    | Helps track unauthorized access patterns.
    |
    */
    'log_ip_address' => true,

];
