<?php

use Phlo\Core\Context;
use Woodlands\Core\Auth;
use Woodlands\Core\Models\Enums\UserType;

function _global_init(Context &$ctx)
{
    Auth::requireLogin(allowed: [UserType::Staff]);
}
