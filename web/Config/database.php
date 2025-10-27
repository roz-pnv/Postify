<?php
return [
    'driver'   => 'mysql',
    'host'     => getenv('MYSQL_HOST') ?: 'db',
    'port'     => getenv('MYSQL_PORT_INTERNAL') ?: '3306',
    'Database' => getenv('MYSQL_DATABASE') ?: 'postify_db',
    'username' => getenv('MYSQL_USER') ?: 'postify_user',
    'password' => getenv('MYSQL_PASSWORD') ?: 'postify_pass',
    'charset'  => 'utf8mb4',
];
