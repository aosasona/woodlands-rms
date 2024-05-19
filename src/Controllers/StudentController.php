<?php

namespace App\Controllers;

use App\ExceptionHandler;
use App\UploadType;
use App\CountriesList;
use DateTime;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{User, Student};
use Woodlands\Core\Models\Enums\{Gender, UserType};
use Rakit\Validation\Validator;

final class StudentController
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
                "in" => "The :attribute field must be a valid variant",
                "regex" => "The :attribute field must contain at least one uppercase letter, one lowercase letter, and one number",
            ]);

            $validation = $validator->make($_POST + $_FILES, [
                "action" => "required|in:create,update",
                "first_name" => "required|min:2|alpha_dash",
                "last_name" => "required|min:2|alpha_dash",
                "nationality" => "required|in:" . (CountriesList::asCommaSeparatedString()),
                "department" => "required|numeric",
                "course" => "numeric",
                "gender" => "required|in:male,female,others",
                "dob" => "required|date|before:today",
                "enrolled_at" => "required|date|before:today",
                "profile_image" => "required|uploaded_file:0,1M,png,jpeg,jpg"
            ]);
            $validation->validate();

            if ($validation->fails()) {
                throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
            }

            $student = Student::new();
            $student->firstName = strtolower(trim($_POST["first_name"]));
            $student->lastName = strtolower(trim($_POST["last_name"]));
            $student->dob = new DateTime($_POST["dob"]);
            $student->departmentId = intval($_POST["department"]);
            $student->nationality = $_POST["nationality"];
            $student->gender = Gender::from($_POST["gender"]);
            $student->enrolledAt = new DateTime($_POST["enrolled_at"]);

            if (!empty($_POST["course"])) {
                $current_course = null;
                $should_handle_course = true;

                // Create or update course mapping
                // If the course is set, assign the student to the course
                $query = match ($_POST["action"]) {
                    "create" => "INSERT INTO `student_courses` (`course_id`, `student_id`) VALUES (?, ?)",
                    "update" => "UPDATE `student_courses` SET `course_id` = ? WHERE `student_id` = ?"
                };

                // Make sure the course is not already assigned to the student, if it is, skip this op
                if ($_POST["action"] === "update") {
                    $current_course = Connection::getInstance()->query("SELECT `course_id` FROM `student_courses` WHERE `student_id` = ?", [$student->id])->fetchColumn();
                    if ($current_course === $_POST["course"]) {
                        $should_handle_course = false;
                    }
                }

                if ($should_handle_course) {
                    Connection::getInstance()->execute($query, [$_POST["course"], $student->id]);
                }
            }

            // Create user record
            $user = User::new();
            $user->email = $student->generateEmail();
            $user->setPassword($student->generateDefaultPassword());
            $user->type = UserType::Student;
            $user->save();

            $student->userId = $user->id;
            $student->save();

            $file_name = FileController::fileNameFor($user->id, pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION), UploadType::ProfileImage);
            FileController::saveFile($file_name, $_FILES["profile_image"]["tmp_name"], UploadType::ProfileImage);

            $ctx->redirect("/students");
        } catch (\Exception $e) {
            ExceptionHandler::handle(action: "new_student", exception: $e, context: $ctx, redirect_to: "/students/new");
        }
    }
}
