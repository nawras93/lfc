<?php

// A blank or non-numeric SCAN_TOKEN_TTL casts to 0, which would expire every QR
// the instant it is issued and silently kill scanning. Fall back to the default.
$tokenTtl = (int) env('SCAN_TOKEN_TTL', 60);

return [
    'qr_secret' => env('SCAN_QR_SECRET', env('APP_KEY')),
    'token_ttl' => $tokenTtl > 0 ? $tokenTtl : 60,
];
