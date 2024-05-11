<?php

use App\Controllers\AuthController;
use Phlo\Core\Context;

function post(Context $ctx): void
{
    AuthController::logout($ctx);
}
