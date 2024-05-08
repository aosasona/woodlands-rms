<?php

use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Auth;

function post(Context $ctx): void
{
    try {
        $csrf_token = $ctx->bodyOr("__csrf_token", "");
        if(!CSRFToken::validate($csrf_token)) {
            $ctx->redirect("/sign-in");
        }

        Auth::logout();
        $ctx->redirect("/sign-in");
    } catch (Exception $e) {
        $ctx->redirect("/sign-in");
    }
}
