<?php

namespace App;

use App\StateType;

final class State
{
    private static function getKey(string $key, StateType $type): string
    {
        return str_ends_with($key, ".{$type->value}") ? $key : "{$key}.{$type->value}";
    }

    public static function persist(string $key, mixed $value, StateType $type): void
    {
        $key = self::getKey($key, $type);
        $_SESSION[$key] = $value;
    }

    public static function renderError(string $key): void
    {
        $key = self::getKey($key, StateType::Error);
        if (isset($_SESSION[$key])) {
            echo <<<HTML
              <div class="uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p>$_SESSION[$key]</p>
              </div>
            HTML;

            unset($_SESSION[$key]);
        }
    }

    public static function prevFormValue(string $key, string $field): mixed
    {
        $key = self::getKey($key, StateType::Form);

        if (isset($_SESSION[$key][$field])) {
            $value = $_SESSION[$key][$field];
            unset($_SESSION[$key][$field]);
            return $value;
        }

        return null;
    }
}
