<?php

namespace App\Controllers;

use App\ExceptionHandler;
use App\State;
use App\StateType;
use App\UploadType;
use DateTime;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{User, Staff};
use Woodlands\Core\Models\Enums\{Gender, UserType};
use Rakit\Validation\Validator;

final class StaffController
{
    public static function validateRequest(bool $is_create): array
    {
        if (!CSRFToken::validate()) {
            throw new AppException("CSRF token validation failed");
        }

        $validator = new Validator([
            "required" => "The :attribute field is required",
            "min" => "The :attribute field must be at least :min characters",
            "date" => "The :attribute field must be a valid date",
            "in" => "The :attribute field must be one of :allowed",
            "regex" => "The :attribute field must contain at least one uppercase letter, one lowercase letter, and one number",
        ]);

        $fields = [
            "first_name" => "required|min:2",
            "last_name" => "required|min:2",
            "department_id" => "numeric",
            "gender" => "required|in:male,female,others",
            "role" => "alpha_spaces",
            "dob" => "required|date",
            "hire_date" => "required|date",
        ];

        if ($is_create) {
            $fields["password"] = "required|min:6";
            $fields["profile_image"] = "required|uploaded_file:0,1M,png,jpeg,jpg";
        }

        $validation = $validator->make($_POST + $_FILES, $fields);

        $validation->validate();

        if ($validation->fails()) {
            throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
        }

        return $validation->getValidatedData();
    }

    public static function create(Context $ctx): void
    {
        try {
            self::validateRequest(is_create: true);

            $dob = new DateTime($_POST["dob"]);
            $age = $dob->diff(new DateTime("now"))->y;

            if ($age < 18) {
                throw new AppException("Staff must be at least 18 years old to be employed");
            }

            $hire_date = new DateTime($_POST["hire_date"]);
            if ($hire_date > new DateTime("now")) {
                throw new AppException("Hire date cannot be in the future");
            }

            $department_id = (int) $_POST["department_id"] ?: null;

            $staff = Staff::new();
            $staff->firstName = strtolower(htmlspecialchars($_POST["first_name"]));
            $staff->lastName = strtolower(htmlspecialchars($_POST["last_name"]));
            $staff->role = strtolower($_POST["role"]) ?? null;
            $staff->departmentId = $department_id;
            $staff->gender = Gender::tryFrom($_POST["gender"]);
            $staff->dob = $dob;
            $staff->hireDate = $hire_date;


            $user = User::new();
            $user->email = $staff->generateEmail(true);
            if ($user->where("email_address", "=", $user->email)->one() !== null) {
                $user->email = $staff->generateEmail(withUniqueSuffix: true);
            }
            $user->setPassword($_POST["password"]);
            $user->type = UserType::Staff;
            $user->save();

            $staff->userId = $user->id;
            $staff->save();

            $file_name = FileController::fileNameFor($user->id, pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION), UploadType::ProfileImage);
            FileController::saveFile($file_name, $_FILES["profile_image"]["tmp_name"], UploadType::ProfileImage);

            $ctx->redirect("/staff");
        } catch (\Exception $e) {
            $message = $e instanceof AppException ? $e->getMessage() : "An error occurred while processing your request";
            State::persist("new_staff", $message, StateType::Error);
            $ctx->redirect("/staff/new");
        }
    }

    public static function update(Context $ctx): void
    {
        try {
            $data = self::validateRequest(is_create: false);

            [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "dob" => $dob,
                "role" => $role,
                "hire_date" => $hire_date,
                "gender" => $gender,
            ] = $data;

            $staff_id = intval($_POST["staff_id"]);
            if (empty($staff_id)) {
                throw new AppException("Student ID is required");
            }

            $dob = new DateTime($dob);
            $age = $dob->diff(new DateTime("now"))->y;

            if ($age < 18) {
                throw new AppException("Staff must be at least 18 years old to be employed");
            }

            $hire_date = new DateTime($hire_date);
            if ($hire_date > new DateTime("now")) {
                throw new AppException("Hire date cannot be in the future");
            }

            $department_id = (int) $_POST["department_id"] ?: null;

            $connection = Connection::getInstance();
            $conn = $connection->getConnection();
            $conn->beginTransaction();

            $staff = Staff::new($connection)->where("staff_id", "=", $staff_id)->one();
            if (empty($staff)) {
                throw new AppException("Student not found");
            }

            // Update staff details
            $staff->firstName = strtolower(htmlspecialchars($first_name));
            $staff->lastName = strtolower(htmlspecialchars($last_name));
            $staff->role = strtolower($role) ?? null;
            $staff->departmentId = $department_id;
            $staff->gender = Gender::tryFrom($gender);
            $staff->dob = $dob;
            $staff->hireDate = $hire_date;
            $staff->save();

            $conn->commit();

            $ctx->redirect("/staff");
        } catch (\Exception $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }

            $staff_id = $_POST["staff_id"] ?? 0;
            ExceptionHandler::handle(action: "new_staff", exception: $e, context: $ctx, redirect_to: "/staff/edit?id=$staff_id");
        }
    }


    public static function loadSessionData(Context $ctx, int $id): void
    {
        try {
            $staff = Staff::new()->where("staff_id", "=", $id)->withRelations("department")->one();
            if (empty($staff)) {
                throw new AppException("Staff not found");
            }

            $data = [
                "first_name" => $staff->firstName,
                "last_name" => $staff->lastName,
                "department_id" => $staff?->departmentId ?? null,
                "gender" => $staff->gender,
                "role" => $staff->role,
                "dob" => $staff->dob->format("Y-m-d"),
                "hire_date" => $staff->hireDate->format("Y-m-d"),
            ];

            State::persist("new_staff", $data, StateType::Form);
        } catch (\Exception $e) {
            ExceptionHandler::handle(action: "new_staff", exception: $e, context: $ctx, redirect_to: "/staff/new");
        }
    }

    public static function delete(Context $ctx): void
    {
        try {
            $staff_id = intval($_POST["staff_id"]);
            if (empty($staff_id)) throw new AppException("Staff ID is required");

            $staff = Staff::new()->findById($staff_id);
            if (empty($staff)) throw new AppException("Staff with ID `{$staff_id}` not found");

            $staff->delete();
            $ctx->redirect("/staff");
        } catch (\Exception $e) {
            $sid = $_POST["staff_id"] ?? "";
            ExceptionHandler::handle(action: "delete_staff", exception: $e, context: $ctx, redirect_to: "/staff/$sid");
        }
    }
}
