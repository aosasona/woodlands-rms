<?php

namespace App\Controllers;

use App\ExceptionHandler;
use DateTime;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Models\{Course};

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
                "name" => "required|min:3|alpha_spaces",
                "department" => "required|numeric",
                "start_date" => "required|date",
                "description" => "required|min:10",
                "modules" => "array",
                "modules.*" => "numeric",
                "students" => "array",
                "students.*" => "numeric",
            ]);
            $validation->validate();

            if ($validation->fails()) {
                throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
            }

            ["name" => $name, "department" => $department, "start_date" => $start_date, "description" => $description, "modules" => $modules, "students" => $students] = $validation->getValidatedData();

            $conn_instance = Connection::getInstance();
            $conn = $conn_instance->getConnection();
            $conn->beginTransaction();

            // Create course record
            $course = Course::new($conn_instance);
            $course->name = $name;
            $course->description = $description;
            $course->departmentId = (int)$department;
            $course->startDate = new DateTime($start_date);
            $course->save();

            // Attach modules to course
            $modules_stmt = $conn->prepare("INSERT INTO `course_modules` (`course_id`, `module_id`) VALUES (:course_id, :module_id)");
            foreach ($modules as $module) {
                $modules_stmt->execute(["course_id" => $course->id, "module_id" => $module]);
            }

            // Assign student to course
            $students_stmt = $conn->prepare("INSERT IGNORE INTO `student_courses` (`course_id`, `student_id`) VALUES (:course_id, :student_id)");
            foreach ($students as $student) {
                $students_stmt->execute(["course_id" => $course->id, "student_id" => $student]);
            }

            $conn->commit();

            $ctx->redirect("/courses");
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                try {
                    $conn->rollBack();
                } catch (\Exception $e) {
                    ExceptionHandler::handle(action: "new_course", exception: $e, context: $ctx, redirect_to: "/courses/new");
                }
            }

            ExceptionHandler::handle(action: "new_course", exception: $e, context: $ctx, redirect_to: "/courses/new");
        }
    }
}
