<?php

namespace App\Controllers;

use App\State;
use App\StateType;
use DateTime;
use Phlo\Core\Context;
use Woodlands\Core\Exceptions\AppException;
use Rakit\Validation\Validator;

final class StaffController
{
    public static function create(Context $ctx): void
    {
        try {
            $validator = new Validator([
                "required" => "The :attribute field is required",
                "min" => "The :attribute field must be at least :min characters",
                "date" => "The :attribute field must be a valid date",
                "in" => "The :attribute field must be one of :allowed",
                "regex" => "The :attribute field must contain at least one uppercase letter, one lowercase letter, and one number",
            ]);

            $validation = $validator->make($_POST + $_FILES, [
                "first_name" => "required|min:2",
                "last_name" => "required|min:2",
                "department_id" => "numeric",
                "gender" => "required|in:male,female,others",
                "role" => "alpha_spaces",
                "dob" => "required|date",
                "hire_date" => "required|date",
                "password" => "required|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/",
                "profile_image" => "required|uploaded_file:0,1M,png,jpeg,jpg"
            ]);

            State::persist("new_staff", $_POST, StateType::Form);

            $validation->validate();

            if($validation->fails()) {
                throw new AppException("<ul><li>".implode("</li><li>", $validation->errors()->firstOfAll())."</li></ul>");
            }

            $dob = new DateTime($_POST["dob"]);
            $age = $dob->diff(new DateTime("now"))->y;

            if($age < 18) {
                throw new AppException("Staff must be at least 18 years old to be employed");
            }

            $hire_date = new DateTime($_POST["hire_date"]);
            if($hire_date > new DateTime("now")) {
                throw new AppException("Hire date cannot be in the future");
            }

            // TODO: Create the staff and user records


            $ctx->redirect("/staff");
        } catch (\Exception $e) {
            $message = $e instanceof AppException ? $e->getMessage() : "An error occurred while processing your request";
            State::persist("new_staff", $message, StateType::Error);
            $ctx->redirect("/staff/new");
        }
    }
}
