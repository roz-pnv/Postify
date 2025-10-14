<?php

use App\core\Bootstrap;

require_once __DIR__ . '/../app/vendor/autoload.php';

$db = Bootstrap::getDatabase()->getConnection();
