<?php

use Phlo\Core\Context;
use Woodlands\Core\Auth;

function _global_init(Context &$context)
{
    if(!preg_match("/sign-in/", $context->uri)) {
        if(!Auth::isLoggedIn()) {
            $context->redirect('/sign-in');
        }
    }
}
