<?php

namespace App\Controllers;

use App\State;
use App\StateType;
use Exception;
use Phlo\Core\Context;
use Woodlands\Core\Auth;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\Enums\UserType;
use Phlo\Extensions\CSRFToken;

final class AuthController
{
    public static function login(Context $ctx): never
    {
        try {
            $email = $ctx->bodyOr("email", "");
            $password = $ctx->bodyOr("password", "");
            $remember_me = $ctx->bodyOr("remember_me", "") == "on";

            Auth::login($email, $password, $remember_me, [UserType::Staff]);
            $ctx->redirect("/courses");
        } catch (Exception $e) {
            $message = $e instanceof AppException ? $e->getMessage() : "An error occurred";
            State::persist("signin", $message, StateType::Error);
            $ctx->redirect("/sign-in");
        }
    }

    public static function logout(Context $ctx): never
    {
        try {
            $csrf_token = $ctx->bodyOr("__logout_csrf_token", "");
            if (!CSRFToken::validate($csrf_token, "__logout_csrf_token")) {
                $ctx->redirect("/sign-in");
            }

            Auth::logout();
            $ctx->redirect("/sign-in");
        } catch (Exception $e) {
            $ctx->redirect("/sign-in");
        }
    }
}
