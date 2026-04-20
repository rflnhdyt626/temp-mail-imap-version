<?php
require __DIR__ . '/utils.php';

$domain = $_GET['domain'] ?? '';
$alias = random_local_part();
json_response([
    'ok' => true,
    'alias' => $alias,
    'email' => alias_email($alias, $domain),
]);
