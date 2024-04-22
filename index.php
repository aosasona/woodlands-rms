<?php

declare(strict_types=1);

declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use App\Error\Handler;
use Phlo\Core\{Router, Rule, RuleType};

try {
    set_error_handler([Handler::class, "handleError"]);
    set_exception_handler([Handler::class, "handleException"]);

    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    } catch(Exception) {
    }

    Router::new()
        ->addRule(Rule::new("api")->setRuleType(RuleType::API)->setTarget(__DIR__ . "/api"))
        ->addRule(Rule::new("public")->setRuleType(RuleType::STATIC)->setTarget(__DIR__ . "/public"))
        ->addRule(Rule::new("sticky")->setRuleType(RuleType::STICKY)->setTarget(__DIR__ . "/pages/index.html"))
        ->addRule(Rule::new("")->setRuleType(RuleType::STATIC)->setTarget(__DIR__ . "/pages"))
        ->serve();
} catch (Exception | Error $e) {
    if (isset($_ENV["APP_ENV"]) && ($_ENV["APP_ENV"] === "development" || $_ENV["APP_ENV"] === "dev")) {
        Handler::displayError($e);
    }

    echo "Internal Server Error";
}
