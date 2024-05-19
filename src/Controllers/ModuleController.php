<?php

namespace App\Controllers;

use App\ExceptionHandler;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{Module};

final class ModuleController
{
    public static function create(Context $ctx): void
    {
        try {
            if (!CSRFToken::validate()) {
                throw new AppException("Failed to verify request's authenticity");
            }

            $validator = new Validator([
                "required" => "The :attribute field is required",
                "min" => "The :attribute field must be at least :min characters",
                "regex" => "The :attribute field must be in the format of XY[Z...]1234[5...] e.g CSY1065",
            ]);

            $validation = $validator->make($ctx->body, [
                "name" => "alpha_spaces|required|min:2",
                "code" => "required|min:2|regex:/^[A-Z]{2,}[0-9]{4,}$/",
                "description" => "min:2|max:8096",
            ]);
            $validation->validate();

            if($validation->fails()) {
                throw new AppException("<ul><li>".implode("</li><li>", $validation->errors()->firstOfAll())."</li></ul>");
            }

            $module = Module::new();
            $module->name = strtolower(htmlspecialchars(trim($ctx->bodyOr("name", ""))));
            $module->code = strtolower(htmlspecialchars(trim($ctx->bodyOr("code", ""))));
            $module->description = htmlspecialchars(trim($ctx->bodyOr("description", "")));
            $module->save();

            $ctx->redirect("/courses");

        } catch (\Exception $e) {
            ExceptionHandler::handle(context: $ctx, exception: $e, action:  "new_module", redirect_to: "/modules/new");
        }
    }
}
