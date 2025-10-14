<?php
return [
    'default' => 'file',
    'path' => __DIR__ . '/../../../data/logs/app.log',
    'level' => getenv('APP_DEBUG') === 'true' ? 'debug' : 'error',
    'max_files' => 10,
];
