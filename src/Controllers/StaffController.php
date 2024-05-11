<?php

namespace App\Controllers;

use DateTime;
use Phlo\Core\Context;
use Woodlands\Core\Exceptions\AppException;

final class StaffController
{
    public static function create(Context $ctx): void
    {
        try {
            $first_name = $ctx->bodyOr("first_name");
            $last_name = $ctx->bodyOr("last_name");
            $department_id = $ctx->bodyOr("department_id");
            $gender = $ctx->bodyOr("gender");
            $role = $ctx->bodyOr("role");
            $dob = $ctx->bodyOr("dob");
            $hire_date = $ctx->bodyOr("hire_date");
            $password = $ctx->bodyOr("password");
            // $profile_picture = $_FILES["profile_image"];

            var_dump($_FILES);
            // var_dump($profile_picture);
            echo "<br><br>";
            return;

            if(empty($first_name) || strlen($first_name) < 2) {
                throw new AppException("First name must be at least 2 characters long");
            }

            if(empty($last_name) || strlen($last_name) < 2) {
                throw new AppException("Last name must be at least 2 characters long");
            }

            if(!empty($department_id) && !is_numeric($department_id)) {
                throw new AppException("Invalid department ID");
            }

            if(!in_array(needle: $gender, haystack: ["male", "female", "others"])) {
                throw new AppException("Unknown gender provided");
            }

            if(!empty($role) && !preg_match("/^[a-zA-Z\s]+$/", $role)) {
                throw new AppException("Role must contain only alphabets and spaces");
            }

            $dob = new DateTime($dob);
            $age = $dob->diff(new DateTime("now"))->y;

            if($age < 18) {
                throw new AppException("Staff must be at least 18 years old to be employed");
            }

            $hire_date = new DateTime($hire_date);
            if($hire_date > new DateTime("now")) {
                throw new AppException("Hire date cannot be in the future");
            }

            if(!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", $password)) {
                throw new AppException("Password must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number");
            }

            // TODO: Create the staff and user records

        } catch (\Exception $e) {
            // TODO: handle
            $ctx->status(400)->json(["error" => $e->getMessage()]);
        }
    }
}
