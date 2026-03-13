<?php
require __DIR__ . '/utils.php';

$alias = random_local_part(10);
json_response([
    'ok' => true,
    'alias' => $alias,
    'email' => alias_email($alias),
]);
