<?php

namespace App\Controllers;

use App\ExceptionHandler;
use App\State;
use App\StateType;
use App\UploadType;
use App\CountriesList;
use DateTime;
use Phlo\Core\Context;
use Phlo\Extensions\CSRFToken;
use Phlo\Extensions\CSRFTokenException;
use Rakit\Validation\Validation;
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
			$validation = self::validateRequest();

			[
				"first_name" => $first_name,
				"last_name" => $last_name,
				"dob" => $dob,
				"personal_tutor" => $personal_tutor,
				"department" => $department,
				"nationality" => $nationality,
				"enrolled_at" => $enrolled_at,
				"gender" => $gender
			] = $validation->getValidatedData();

			$connection = Connection::getInstance();
			$conn = $connection->getConnection();
			$conn->beginTransaction();

			$student = Student::new($connection);
			$student->firstName = strtolower(trim($first_name));
			$student->lastName = strtolower(trim($last_name));
			$student->dob = new DateTime($dob);
			$student->departmentId = intval($department);
			$student->nationality = strtoupper($nationality);
			$student->gender = Gender::from($gender);
			$student->enrolledAt = new DateTime($enrolled_at);


			// Create user record
			$user = User::new($connection);
			$user->email = $student->generateEmail();
			$user->setPassword($student->generateDefaultPassword());
			$user->type = UserType::Student;
			$user->save();

			$student->userId = $user->id;
			$student->save();

			if (!empty($_POST["course"])) {
				$connection->execute("INSERT INTO `student_courses` (`course_id`, `student_id`) VALUES (?, ?)", [$_POST["course"], $student->id]);
			}

			// Insert personal tutor
			$connection->execute("INSERT INTO `student_tutors` (`student_id`, `staff_id`, `assigned_on`) VALUES (?, ?, NOW())", [$student->id, $personal_tutor]);

			$file_name = FileController::fileNameFor($user->id, pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION), UploadType::ProfileImage);
			FileController::saveFile($file_name, $_FILES["profile_image"]["tmp_name"], UploadType::ProfileImage);

			$conn->commit();
			$ctx->redirect("/students");
		} catch (\Exception $e) {
			if (isset($conn) && $conn->inTransaction()) {
				$conn->rollBack();
			}

			ExceptionHandler::handle(action: "new_student", exception: $e, context: $ctx, redirect_to: "/students/new");
		}
	}

	public static function update(Context $ctx): void
	{
		try {
			$validation = self::validateRequest();

			[
				"first_name" => $first_name,
				"last_name" => $last_name,
				"dob" => $dob,
				"personal_tutor" => $personal_tutor,
				"department" => $department,
				"nationality" => $nationality,
				"enrolled_at" => $enrolled_at,
				"gender" => $gender
			] = $validation->getValidatedData();

			$student_id = intval($_POST["student_id"]);
			if (empty($student_id)) {
				throw new AppException("Student ID is required");
			}

			$connection = Connection::getInstance();
			$conn = $connection->getConnection();
			$conn->beginTransaction();

			$student = Student::new($connection)->where("student_id", "=", $student_id)->one();
			if (empty($student)) {
				throw new AppException("Student not found");
			}

			$student->firstName = strtolower(trim($first_name));
			$student->lastName = strtolower(trim($last_name));
			$student->dob = new DateTime($dob);
			$student->departmentId = intval($department);
			$student->nationality = strtoupper($nationality);
			$student->gender = Gender::from($gender);
			$student->enrolledAt = new DateTime($enrolled_at);
			$student->save();

			// Update course mapping
			// If the course is set, assign the student to the course
			if (!empty($_POST["course"])) {
				// Make sure the course is not already assigned to the student, if it is, skip this op
				$current_course_id = Connection::getInstance()->query("SELECT `course_id` FROM `student_courses` WHERE `student_id` = ?", [$student->id])->fetchColumn();
				if ($current_course_id != $_POST["course"]) {
					$connection->execute("UPDATE `student_courses` SET `course_id` = ? WHERE `student_id` = ?", [$_POST["course"], $student->id]);
				}
			}

			// Update or ceate personal tutor mapping
			Connection::getInstance()->execute("INSERT INTO `student_tutors` (`student_id`, `staff_id`, `assigned_on`) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `staff_id` = ?", [$student->id, $personal_tutor, $personal_tutor]);

			// Handle profile image if present
			if (!empty($_FILES["profile_image"]["name"])) {
				$file_name = FileController::fileNameFor($student->userId, pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION), UploadType::ProfileImage);
				FileController::saveFile($file_name, $_FILES["profile_image"]["tmp_name"], UploadType::ProfileImage);
			}

			$conn->commit();
			$ctx->redirect("/students/$student_id");
		} catch (\Exception $e) {
			if (isset($conn) && $conn->inTransaction()) {
				$conn->rollBack();
			}

			$student_id = $_POST["student_id"] ?? 0;
			ExceptionHandler::handle(action: "new_student", exception: $e, context: $ctx, redirect_to: "/students/edit?id=$student_id");
		}
	}

	public static function loadSessionData(Context $ctx, int $id): void
	{
		try {
			$student = Student::new()->where("student_id", "=", $id)->withRelations("department")->one();
			if (empty($student)) {
				throw new AppException("Student not found");
			}

			$conn = Connection::getInstance();
			$course_id = $conn->query("SELECT `course_id` FROM `student_courses` WHERE `student_id` = ?", [$student->id])->fetchColumn();
			$personal_tutor_id = $conn->query("SELECT `staff_id` FROM `student_tutors` WHERE `student_id` = ?", [$student->id])->fetchColumn();

			$data = [
				"first_name" => $student->firstName,
				"last_name" => $student->lastName,
				"nationality" => $student->nationality,
				"department" => $student->department->id,
				"course" => $course_id,
				"personal_tutor" => $personal_tutor_id ?? null,
				"gender" => $student->gender->value,
				"dob" => $student->dob->format("Y-m-d"),
				"enrolled_at" => $student->enrolledAt->format("Y-m-d"),
			];

			State::persist("new_student", $data, StateType::Form);
		} catch (\Exception $e) {
			ExceptionHandler::handle(action: "new_student", exception: $e, context: $ctx, redirect_to: "/students/new");
		}
	}

	public static function delete(Context $ctx): void
	{
		try {
			$student_id = intval($_POST["student_id"]);
			if (empty($student_id)) throw new AppException("Student ID is required");

			$student = Student::new()->findById($student_id);
			$student->delete();

			$ctx->redirect("/students");
		} catch (\Exception $e) {
			$sid = $_POST["student_id"] ?? "";
			ExceptionHandler::handle(action: "delete_student", exception: $e, context: $ctx, redirect_to: "/students/{$sid}");
		}
	}


	/**
	 * @return Validation
	 * @throws AppException
	 * @throws CSRFTokenException
	 */
	private static function validateRequest(): Validation
	{
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
			"personal_tutor" => "required|numeric",
			"gender" => "required|in:male,female,others",
			"dob" => "required|date|before:today",
			"enrolled_at" => "required|date|before:today",
			"profile_image" => "uploaded_file:0,1M,png,jpeg,jpg"
		]);
		$validation->validate();

		if ($validation->fails()) {
			throw new AppException("<ul><li>" . implode("</li><li>", $validation->errors()->firstOfAll()) . "</li></ul>");
		}
		return $validation;
	}
}
