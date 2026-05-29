<?php

return [
    'host' => 'ftp.your-domain.com',
    'port' => 21,
    'username' => 'your-ftp-username',
    'password' => 'your-ftp-password',
    'remote_path' => '/public_html',
    'use_ssl' => true,
    'passive' => true,
    'timeout' => 90,
    'exclude' => [
        '.git',
        '.gitignore',
        'docs',
        'tmp',
        'scripts/deploy-godaddy-ftp.php',
        'scripts/deploy.local.php',
        'scripts/deploy.local.example.php',
    ],
];
