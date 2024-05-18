<?php

use App\ExceptionHandler;
use Phlo\Core\Context;
use Rakit\Validation\Validator;
use Woodlands\Core\Exceptions\AppException;
use Woodlands\Core\Models\{Department, Staff};

function post(Context $ctx): never
{
    try {
        $ctx->json($ctx->bodyOr("assigned"));

        $validator  = new Validator();

        $validation = $validator->make($ctx->body, [
          "name" => "required|min:2",
          "description" => "required|max:8096",
          "head" => "required|numeric"
        ]);
        $validation->validate();

        if($validation->fails()) {
            throw new AppException("<ul><li>".implode("</li><li>", $validation->errors()->firstOfAll())."</li></ul>");
        }

        $department = Department::new();
        $department->name = trim(htmlspecialchars($ctx->body["name"]));
        $department->description = trim(htmlspecialchars($ctx->body["description"]));
        $department->save();

        if($staff = Staff::new()->findById($ctx->bodyOr("head", "0"))) {
            $staff->role = Staff::ROLE_HOD;
            $staff->save();
        } else {
            throw new AppException("The head of department is invalid.");
        }
    } catch (Exception $e) {
        ExceptionHandler::handle(
            context: $ctx,
            exception: $e,
            action:  "new_department",
            redirect_to: "/facilities"
        );
    }


}
