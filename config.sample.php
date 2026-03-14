<?php
return [
    'domain' => 'YOUR_DOMAIN',
    'imap' => [
        // Example for cPanel secure IMAP
        'host' => 'localhost',
        'port' => 993,
        'flags' => '/imap/ssl/novalidate-cert',
        'mailbox' => 'INBOX',
        'username' => 'MAIL@YOUR_DOMAIN',
        'password' => 'PASSWORD_MAIL',

        // Example for Gmail IMAP
        'host' => 'imap.gmail.com',
            'port' => 993,
            'flags' => '/imap/ssl/novalidate-cert',
            'mailbox' => 'INBOX',
            'username' => 'MAIL@gmail.com',
            'password' => 'PASSWORD_MAIL',
    ],

    'poll_interval_seconds' => 8,
    'message_preview_length' => 140,
    'timezone' => 'Asia/Jakarta',
];
