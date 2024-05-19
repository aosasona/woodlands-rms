<?php

use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Auth;

function _global_init(Context $ctx): void
{
    try {
        if(!Auth::isLoggedIn()) {
            $ctx->redirect('/sign-in');
        }

        if (!CSRFToken::validate(cleanup: false) && $ctx->method !== "GET") {
            $ctx->status(403)->json(["error" => "Invalid CSRF token."]);
        }
    } catch (Exception $e) {
        $ctx->status(500)->json(["error" => "An error occurred while verifying request."]);
    }
}
