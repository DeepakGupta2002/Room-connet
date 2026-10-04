<?php

return [
    'contact_notification_email' => env('CONTACT_NOTIFICATION_EMAIL'),
    'owner_approval_required' => (bool) env('OWNER_APPROVAL_REQUIRED', false),
    'free_daily_contact_unlock_limit' => (int) env('FREE_DAILY_CONTACT_UNLOCK_LIMIT', 5),
    'donor_daily_contact_unlock_limit' => (int) env('DONOR_DAILY_CONTACT_UNLOCK_LIMIT', 10),
    'donor_access_duration_days' => (int) env('DONOR_ACCESS_DURATION_DAYS', 30),
    'contact_unlock_access_duration_days' => (int) env('CONTACT_UNLOCK_ACCESS_DURATION_DAYS', 1),
    'contact_unlock_requires_login' => (bool) env('CONTACT_UNLOCK_REQUIRES_LOGIN', true),
    'donation_required' => (bool) env('DONATION_REQUIRED', false),
    'donation_provider' => env('DONATION_PAYMENT_PROVIDER', 'manual'),
];
