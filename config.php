<?php
// Edit these settings. With a default XAMPP install the values below work as they are.
return [
    'site_name' => 'CMFI',
    'debug'     => true,   // shows the real database error on screen. Set to false when the site is live.
    'mysql'     => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'church',   // created automatically if the user is allowed to
        'user' => 'root',
        'pass' => '',
    ],
    'uploads'   => __DIR__ . '/public/uploads',
];
