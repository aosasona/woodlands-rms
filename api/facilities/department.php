<?php

use App\ExceptionHandler;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Rakit\Validation\Validator;
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{Department, Staff};

const ACTION_UPDATE = "update_department";
const ACTION_CREATE = "create_department";

function get(Context $ctx): void
{
    try {
        if (!CSRFToken::validate(token: $_GET["csrf_token"] ?? "", field_name: "__delete_dep_csrf")) {
            throw new AppException("Unable to verify request. Please try again.");
        }

        if (isset($_GET["delete"])) {
            $id = intval($_GET["delete"]);
            $department = Department::new()->findById($id);
            if (empty($department)) {
                throw new AppException("The department with ID {$id} does not exist.");
            }

            // NOTE: Our database is setup to handle updates but to be safe, we unlink all courses, staff and students manually

            // Unlink all courses
            Connection::getInstance()->execute("UPDATE `courses` SET `department_id` = NULL WHERE `department_id` = ?", [$department->id]);

            // Unlink all staff
            Connection::getInstance()->execute("UPDATE `staff` SET `department_id` = NULL WHERE `department_id` = ?", [$department->id]);

            // Unlink all students
            Connection::getInstance()->execute("UPDATE `students` SET `department_id` = NULL WHERE `department_id` = ?", [$department->id]);

            $department->delete();
            $ctx->redirect("/facilities");
        }

        $ctx->redirect("/facilities");
    } catch (Exception $e) {
        ExceptionHandler::handle(
            context: $ctx,
            exception: $e,
            action: "new_department",
            redirect_to: "/facilities"
        );
    }
}

function post(Context $ctx): never
{
    try {
        $validator  = new Validator();
        $validation = $validator->make($ctx->body, [
            "action" => "required|in:create_department,update_department",
            "department_id" => "required_if:action,update_department|numeric",
            "name" => "required|min:2",
            "description" => "required|max:8096",
            "head" => "required|numeric",
            "assigned" => "array",
            "assigned.*" => "numeric"
        ]);
        $validation->validate();

        if ($validation->fails()) {
            throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
        }

        $department = match ($ctx->bodyOr("action", "")) {
            ACTION_CREATE => Department::new(),
            ACTION_UPDATE => Department::new()->findById(intval($ctx->body["department_id"])),
        };

        if (empty($department)) {
            throw new AppException("The department with ID {$ctx->body['department_id']} does not exist.");
        }

        $department->name = strtolower(trim(htmlspecialchars($ctx->body["name"])));
        $department->description = strtolower(trim(htmlspecialchars($ctx->body["description"])));
        $department->save();


        if (!in_array($ctx->body["head"], $ctx->body["assigned"])) {
            throw new AppException("The selected head of department must be assigned to the department!");
        }

        // Assign all staff to the department
        foreach ($ctx->body["assigned"] as $staff_id) {
            $staff = Staff::new()->findById(intval($staff_id));
            if (empty($staff)) {
                throw new AppException("The staff with ID {$staff_id} is invalid.");
            }


            $staff->departmentId = $department->id;
            if (($staff->role ?? "") == Staff::ROLE_HOD && $staff->departmentId != $department->id) {
                throw new AppException("The head of department cannot be assigned to a new department, you need to select a new head of department to replace {$staff->firstName} {$staff->lastName}.");
            }

            // Assign head of department chosen
            if ($staff->id == $ctx->bodyOr("head", "0")) {
                $staff->role = Staff::ROLE_HOD;
            }

            $staff->save();
        }

        // Cleanup if we are updating the department - we need to remove staff that are no longer assigned to the department
        if ($ctx->bodyOr("action", "") == ACTION_UPDATE) {
            Department::new()->exec("UPDATE `staff` SET `department_id` = NULL WHERE department_id = ? AND `staff_id` NOT IN (?)", [$department->id, implode(",", $ctx->body["assigned"] ?? [])]);
        }

        $ctx->redirect("/facilities");
    } catch (Exception $e) {
        ExceptionHandler::handle(
            context: $ctx,
            exception: $e,
            action: "new_department",
            redirect_to: "/facilities"
        );
    }
}
