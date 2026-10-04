<?php

return [
    'contact_notification_email' => env('CONTACT_NOTIFICATION_EMAIL'),
    'owner_approval_required' => (bool) env('OWNER_APPROVAL_REQUIRED', false),
];
