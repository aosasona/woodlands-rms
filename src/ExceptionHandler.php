<?php

namespace App;

use Exception;
use App\State;
use Phlo\Core\Context;
use Woodlands\Core\Exceptions\AppException;

final class ExceptionHandler
{
    public static function handle(string $action, Exception $exception, ?Context $context = null, ?string $redirect_to = null): never
    {
        $message = match (get_class($exception)) {
            AppException::class => $exception->getMessage(),
            default => "An error occurred. Please try again later."
        };

        State::persist($action, $message, StateType::Error);

        $redirect_to = $redirect_to ?? $_SERVER["REQUEST_URI"];

        // If we provided a context, use that as our form data and redirect
        if ($context !== null) {
            State::persist($action, $context->body, StateType::Form);
            $context->redirect($redirect_to);
        }

        header("Location: {$redirect_to}", true, 302);
        exit;
    }
}
