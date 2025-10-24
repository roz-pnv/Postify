<?php
use App\Http\Controllers\AuthController;

return [
    ['POST', '/api/register', [AuthController::class, 'register']],
    ['POST', '/api/login', [AuthController::class, 'login']],
];
