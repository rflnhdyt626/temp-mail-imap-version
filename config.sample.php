<?php
return [
    'domain' => 'gprimes.net',
    'imap' => [
        // Example for cPanel secure IMAP
        'host' => 'localhost',
        'port' => 993,
        'flags' => '/imap/ssl/novalidate-cert',
        'mailbox' => 'INBOX',
        'username' => 'catchall@gprimes.net',
        'password' => 'CHANGE_ME',
    ],
    'poll_interval_seconds' => 8,
    'message_preview_length' => 140,
    'timezone' => 'Asia/Jakarta',
];
