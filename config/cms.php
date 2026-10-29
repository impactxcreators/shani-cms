<?php

return [
    // The admin panel path (without leading slash). Set via FILAMENT_PATH in .env.
    // Kept non-obvious for security, e.g. "admin8869".
    "filament_path" => env("FILAMENT_PATH", "admin"),

    // Web push (deal alerts). Keys live in .env; never commit them.
    "push" => [
        "subject" => env("VAPID_SUBJECT", "mailto:admin@example.com"),
        "public_key" => env("VAPID_PUBLIC_KEY", ""),
        "private_key" => env("VAPID_PRIVATE_KEY", ""),
    ],
];
