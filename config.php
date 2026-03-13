<?php
return [
    'domain' => 'gprimes.net',
    'imap' => [
        'host' => 'imap.gmail.com',
        'port' => 993,
        'flags' => '/imap/ssl/novalidate-cert',
        'mailbox' => 'INBOX',
        'username' => 'danendragustaq@gmail.com', // Removed 'recent:' which is POP3-only
        'password' => 'vaku roev fxbp zlpc', // Your App Password
    ],
    'poll_interval_seconds' => 20,
    'message_preview_length' => 140,
    'timezone' => 'Asia/Jakarta',
];