<?php

return [
    'qr_secret' => env('SCAN_QR_SECRET', env('APP_KEY')),
    'token_ttl' => env('SCAN_TOKEN_TTL', 60),
];
