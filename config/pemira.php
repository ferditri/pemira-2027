<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Emails (Multi-Admin via .env)
    |--------------------------------------------------------------------------
    | Comma-separated list of Google emails that automatically receive admin
    | dashboard & Telescope access upon login.
    */
    'admin_emails' => array_values(array_filter(array_map(
        fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('PEMIRA_ADMIN_EMAILS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Voter Email Domains & Local Testing Bypass
    |--------------------------------------------------------------------------
    | Comma-separated list of allowed email domains for regular voters unless
    | AUTH_ALLOW_ANY_EMAIL=true AND APP_ENV is not production.
    */
    'allowed_email_domains' => array_values(array_filter(array_map(
        fn (string $domain) => ltrim(strtolower(trim($domain)), '@'),
        explode(',', (string) env('PEMIRA_ALLOWED_EMAIL_DOMAINS', env('PEMIRA_ALLOWED_EMAIL_DOMAIN', 'student.upnjatim.ac.id')))
    ))),

    'auth_allow_any_email' => filter_var(env('AUTH_ALLOW_ANY_EMAIL', false), FILTER_VALIDATE_BOOLEAN)
        && env('APP_ENV', 'local') !== 'production',

    /*
    |--------------------------------------------------------------------------
    | Trusted Reverse Proxies
    |--------------------------------------------------------------------------
    | Comma-separated proxy IPs/CIDRs (e.g. "127.0.0.1" for local Nginx/Apache).
    | Avoid "*" unless the app port is strictly firewalled to a single proxy.
    */
    'trusted_proxies' => env('TRUSTED_PROXIES', '127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Support Contact & Landing Links
    |--------------------------------------------------------------------------
    */
    'support_whatsapp' => env('PEMIRA_SUPPORT_WHATSAPP', '62859183994546'),
    'tutorial_video_id' => env('PEMIRA_TUTORIAL_VIDEO_ID', '_Zpm7Rbca1A'),
    'instagram_url' => env('PEMIRA_INSTAGRAM_URL', 'https://www.instagram.com/pemirafasilkom/'),
    'youtube_url' => env('PEMIRA_YOUTUBE_URL', 'https://www.youtube.com/@pemirafasilkomupnvjt'),
];
