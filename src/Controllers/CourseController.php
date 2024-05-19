<?php

namespace App\Controllers;

use App\ExceptionHandler;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;

final class CourseController
{
    public static function create(Context $ctx): void
    {
        try {
            if (!CSRFToken::validate()) {
                throw new AppException("CSRF token validation failed");
            }

            $validator = new Validator([
                "required" => "The :attribute field is required",
                "min" => "The :attribute field must be at least :min characters",
                "date" => "The :attribute field must be a valid date",
                "regex" => "The :attribute field must contain at least one uppercase letter, one lowercase letter, and one number",
            ]);

            $validation = $validator->make($_POST + $_FILES, [
                "action" => "required|in:create,update",
                "name" => "required|min:3|alpha_dash|alpha_spaces",
                "department" => "required|numeric",
                "start_date" => "required|date",
                "end_date" => "required|date|after:" . $_POST["start_date"],
                "description" => "required|min:10",
                "modules" => "required|array",
                "modules.*" => "required|numeric",
                "tutors" => "required|array",
                "tutors.*" => "required|numeric",
                "students" => "required|array",
                "students.*" => "required|numeric",
            ]);
            $validation->validate();

            if ($validation->fails()) {
                throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
            }

            header("Content-Type: application/json");
            echo json_encode($_POST);
            exit;
        } catch (\Exception $e) {
            ExceptionHandler::handle(action: "new_student", exception: $e, context: $ctx, redirect_to: "/students/new");
        }
    }
}
