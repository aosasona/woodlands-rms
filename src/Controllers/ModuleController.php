<?php

namespace App\Controllers;

use App\ExceptionHandler;
use Phlo\Core\Context;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{Module};

final class ModuleController
{
    public static function createModule(Context $ctx): void
    {
        try {
            $validator = new Validator();
            $validation = $validator->make($ctx->body, [
                "name" => "required|min:2",
                "description" => "required",
            ]);
            $validation->validate();

            if($validation->fails()) {
                throw new AppException("<ul><li>".implode("</li><li>", $validation->errors()->firstOfAll())."</li></ul>");
            }

            $module = Module::new();
            $module->name = strtolower(htmlspecialchars(trim($ctx->bodyOr("name", ""))));
            $module->description = strtolower(htmlspecialchars(trim($ctx->bodyOr("description", ""))));
            $module->save();

            $ctx->redirect("/modules");

        } catch (\Exception $e) {
            ExceptionHandler::handle(
                context: $ctx,
                exception: $e,
                action:  "new_module",
                redirect_to: "/modules/new"
            );
        }
    }
}
