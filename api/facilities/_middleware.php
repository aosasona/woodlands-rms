<?php

use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Auth;
use Woodlands\Core\Models\Enums\UserType;

function _global_init(Context $ctx): void
{
    try {
        Auth::requireLogin([UserType::Staff]);

        if ($ctx->method !== "GET") {
            if (!CSRFToken::validate(cleanup: false)) {
                $ctx->status(403)->json(["error" => "Invalid CSRF token."]);
            }
        }
    } catch (Exception $e) {
        $ctx->status(500)->json(["error" => "An error occurred while verifying request."]);
    }
}
