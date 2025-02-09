<?php

use Phlo\Core\Context;
use Woodlands\Core\Auth;

function _global_init(Context &$context)
{
    if (!Auth::isLoggedIn() && !preg_match("/sign-in/", $context->uri)) {
        $context->redirect('/sign-in');
    }
}
