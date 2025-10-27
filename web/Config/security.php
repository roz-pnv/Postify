<?php
return [
    'jwt_secret' => getenv('JWT_SECRET') ?: 'default_secret',
    'jwt_expiry' => getenv('JWT_EXPIRE') ?: 3600,
];
