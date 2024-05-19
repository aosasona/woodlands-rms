<?php

use App\ExceptionHandler;
use Phlo\Core\Context;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{Department, Staff};

const ACTION_UPDATE = "update_department";
const ACTION_CREATE = "create_department";

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

        if($validation->fails()) {
            throw new AppException("<ul><li>".implode("</li><li>", $validation->errors()->firstOfAll())."</li></ul>");
        }

        $department = match($ctx->bodyOr("action", "")) {
            ACTION_CREATE => Department::new(),
            ACTION_UPDATE => Department::new()->findById(intval($ctx->body["department_id"])),
        };

        if (empty($department)) {
            throw new AppException("The department with ID {$ctx->body['department_id']} does not exist.");
        }

        $department->name = strtolower(trim(htmlspecialchars($ctx->body["name"])));
        $department->description = strtolower(trim(htmlspecialchars($ctx->body["description"])));
        $department->save();


        // Assign all staff to the department
        foreach ($ctx->body["assigned"] as $staff_id) {
            $staff = Staff::new()->findById(intval($staff_id));
            if(empty($staff)) {
                throw new AppException("The staff with ID {$staff_id} is invalid.");
            }


            $staff->departmentId = $department->id;
            if(($staff->role ?? "") == Staff::ROLE_HOD && $staff->departmentId != $department->id) {
                throw new AppException("The head of department cannot be assigned to a new department, you need to select a new head of department to replace {$staff->firstName} {$staff->lastName}.");
            }

            // Assign head of department chosen
            if($staff->id == $ctx->bodyOr("head", "0")) {
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
        var_dump($e->getMessage());
        exit;
        ExceptionHandler::handle(
            context: $ctx,
            exception: $e,
            action:  "new_department",
            redirect_to: "/facilities"
        );
    }


}
