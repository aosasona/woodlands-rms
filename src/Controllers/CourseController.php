<?php

namespace App\Controllers;

use App\ExceptionHandler;
use App\State;
use App\StateType;
use DateTime;
use PDO;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Models\{Course};

final class CourseController
{
    public static function validateRequest()
    {
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
        return $validation;
    }

    public static function create(Context $ctx): void
    {
        try {
            $validation = self::validateRequest();

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

            $modules = $modules ?? [];
            $students = $students ?? [];

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

    public static function update(Context $ctx): void
    {
        try {
            $validation = self::validateRequest();
            $course_id = intval($_POST["course_id"]);
            if (empty($course_id)) {
                throw new AppException("Student ID is required");
            }

            [
                "name" => $name,
                "department" => $departmentId,
                "start_date" => $start_date,
                "description" => $description,
                "modules" => $modules,
                "students" => $students,
            ] = $validation->getValidatedData();

            $students = $students ?? [];
            $modules = $modules ?? [];

            $connection = Connection::getInstance();
            $conn = $connection->getConnection();
            $conn->beginTransaction();

            $course = Course::new($connection)->findById($course_id);
            if (empty($course)) {
                throw new AppException("Course not found");
            }

            $course->name = $name;
            $course->description = $description;
            $course->departmentId = (int)$departmentId;
            $course->startDate = new DateTime($start_date);
            $course->save();

            // Update course mapping
            $remove_modules = $conn->prepare("DELETE FROM `course_modules` WHERE `course_id` = ?");
            $add_modules = $conn->prepare("INSERT INTO `course_modules` (`course_id`, `module_id`) VALUES (:course_id, :module_id)");

            // Unlink modules that are not in the new list
            $current_modules = $connection->query("SELECT `module_id` FROM `course_modules` WHERE `course_id` = ?", [$course_id])->fetchAll(PDO::FETCH_ASSOC);
            foreach ($current_modules as $module) {
                if (!in_array($module, $modules)) {
                    $remove_modules->execute([$course_id]);
                }
            }

            // Add new modules
            foreach ($modules as $module) {
                if (!in_array($module, $current_modules)) {
                    $add_modules->execute(["course_id" => $course_id, "module_id" => $module]);
                }
            }

            // Update student mapping

            $remove_students = $conn->prepare("DELETE FROM `student_courses` WHERE `course_id` = ?");
            $add_students = $conn->prepare("INSERT INTO `student_courses` (`course_id`, `student_id`) VALUES (:course_id, :student_id) ON DUPLICATE KEY UPDATE `course_id` = :course_id, `student_id` = :student_id");

            // Unlink students that are not in the new list
            $current_students = $connection->query("SELECT `student_id` FROM `student_courses` WHERE `course_id` = ?", [$course_id])->fetchAll(PDO::FETCH_ASSOC);
            foreach ($current_students as $student) {
                if (!in_array($student, $students)) {
                    $remove_students->execute([$course_id]);
                }
            }

            // Add new students
            foreach ($students as $student) {
                if (!in_array($student, $current_students)) {
                    $add_students->execute(["course_id" => $course_id, "student_id" => $student]);
                }
            }

            $conn->commit();
            $ctx->redirect("/courses?selected=$course_id");
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) $conn->rollBack();

            $course_id = $_POST["course_id"] ?? 0;
            ExceptionHandler::handle(action: "new_course", exception: $e, context: $ctx, redirect_to: "/courses/edit?id=$course_id");
        }
    }

    public static function delete(Context $ctx, string $course_id): void
    {
        try {
            $course_id = intval($course_id);
            if (empty($course_id)) throw new AppException("Course ID is required");

            $course = Course::new()->findById($course_id);
            if (empty($course)) throw new AppException("Course with ID `{$course_id}` not found");

            $course->delete();
            $ctx->redirect("/courses");
        } catch (\Exception $e) {
            ExceptionHandler::handle(action: "delete_course", exception: $e, context: $ctx, redirect_to: "/courses?selected={$course_id}");
        }
    }

    public static function loadSessionData(Context $ctx, int $id): void
    {
        try {
            if (empty($id)) throw new AppException("Course ID is required");

            $course = Course::new()->findById($id);
            if (empty($course)) throw new AppException("Course with ID `{$id}` not found");

            $conn = Connection::getInstance();
            $modules = $conn->query("SELECT `module_id` FROM `course_modules` WHERE `course_id` = ?", [$course->id])->fetchAll();
            $students = $conn->query("SELECT `student_id` FROM `student_courses` WHERE `course_id` = ?", [$course->id])->fetchAll();

            $data = [
                "name" => $course->name,
                "department" => $course->departmentId,
                "start_date" => $course->startDate->format("Y-m-d"),
                "description" => $course->description,
                "modules" => array_column($modules, "module_id"),
                "students" => array_column($students, "student_id"),
            ];

            State::persist("new_course", $data, StateType::Form);
        } catch (\Exception $e) {
            ExceptionHandler::handle(action: "new_course", exception: $e, context: $ctx, redirect_to: "/courses/new");
        }
    }
}
