<?php

namespace App\Controllers;

use App\ExceptionHandler;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Database\Connection;
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
                "tutors" => "array|required",
                "tutors.*" => "numeric|required",
            ]);

            $validation->validate();

            if ($validation->fails()) {
                throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
            }

            ["name" => $name, "code" => $code, "description" => $description, "tutors" => $tutors] = $validation->getValidatedData();

            // Check if module already exists
            $module = Module::new()->where("code", "=", $code)->or("name",  "=", strtolower($name))->one();
            if ($module) {
                throw new AppException("Module with the same name or code already exists");
            }

            $conn_instance = Connection::getInstance();
            $conn = $conn_instance->getConnection();
            $conn->beginTransaction();

            // Create module
            $module = Module::new();
            $module->name = strtolower(htmlspecialchars(trim($name)));
            $module->code = strtolower(htmlspecialchars(trim($code)));
            $module->description = htmlspecialchars(trim($description));
            $module->save();

            // Attach tutors to module
            $tutor_stmt = $conn->prepare("INSERT INTO `module_tutors` (`module_id`, `staff_id`) VALUES (:module_id, :staff_id)");
            foreach ($tutors as $tutor) {
                $tutor_stmt->execute(["module_id" => $module->id, "staff_id" => $tutor]);
            }

            $conn->commit();

            $ctx->redirect("/courses");
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                try {
                    $conn->rollBack();
                } catch (\Exception $e) {
                    ExceptionHandler::handle(context: $ctx, exception: $e, action: "new_module", redirect_to: "/modules/new");
                }
            }

            ExceptionHandler::handle(context: $ctx, exception: $e, action: "new_module", redirect_to: "/modules/new");
        }
    }
}
