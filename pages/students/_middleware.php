<?php

use Phlo\Core\Context;
use Woodlands\Core\Auth;

function _global_init(Context &$context)
{
    if(!Auth::isLoggedIn()) {
        $context->redirect('/sign-in');
    }
}
