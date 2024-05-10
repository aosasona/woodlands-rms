<?php

declare(strict_types=1);

declare(strict_types=1);

require_once __DIR__."/vendor/autoload.php";

use Woodlands\Core\FaultHandler as Handler;
use Phlo\Core\{Router, Rule, RuleType};

try {
    set_error_handler([Handler::class, "handleError"]);
    set_exception_handler([Handler::class, "handleException"]);

    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    } catch(Exception) {
    }

    // Initialize seesson
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    Router::new()
        ->addRule(Rule::new("api")->setRuleType(RuleType::API)->setTarget(__DIR__ . "/api"))
        ->addRule(Rule::new("public")->setRuleType(RuleType::STATIC)->setTarget(__DIR__ . "/public"))
        ->addRule(Rule::new("")->setRuleType(RuleType::STATIC)->setTarget(__DIR__ . "/pages"))
        ->serve();
} catch (Exception | Error $e) {
    if (isset($_ENV["APP_ENV"]) && ($_ENV["APP_ENV"] === "development" || $_ENV["APP_ENV"] === "dev")) {
        Handler::displayError($e);
    }

    echo "<p style=\"font-family: Monospace;padding:2rem 1rem;\">Internal Server Error</p>";
}
