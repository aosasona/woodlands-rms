<?php




use App\Controllers\FileController;
use App\Controllers\StudentController;
use App\State;
use App\UI\Layout;
use Phlo\Core\Context;
use Woodlands\Core\Models\Student;
use Woodlands\Core\Database\Connection;

/** @var Context $ctx */

$student_id = preg_replace(pattern: "[^0-9]", replacement: "",  subject: $ctx->getParam("id", ""));
if (empty($student_id)) {
  $ctx->redirect("/students");
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST["action"]) && $_POST["action"] === "delete") {
  StudentController::delete($ctx);
}

$student = Student::new()
  ->where("student_id", "=", $student_id)
  ->withRelations("user", "department")
  ->one();

$modules = Connection::getInstance()->query("SELECT m.* FROM `modules` m JOIN `student_modules` sm ON m.`module_id` = sm.`module_id` WHERE sm.`student_id` = :student_id", ["student_id" => $student_id])->fetchAll(PDO::FETCH_ASSOC);
$personal_tutor = Connection::getInstance()->query("SELECT s.* FROM `student_tutors` st LEFT JOIN `staff` s ON  s.`staff_id` = st.`staff_id` WHERE st.`student_id` = :student_id", ["student_id" => $student_id])->fetch(PDO::FETCH_ASSOC);

$layout = Layout::start(empty($student) ? "Not found" : "{$student->firstName} {$student->lastName}");
?>

<main class="container min-h-screen">
  <h1>Student details</h1>

  <?php if (empty($student)) : ?>
    <div class="text-center mt-24">
      <h2 class="text-gray-400 text-lg mb-6">Record not found.</h2>
      <a href="/students" class="uk-button uk-button-primary">Back to students</a>
    </div>
</main>
<?php $layout->end();
    return; ?>
<?php endif; ?>

<?php State::renderError("delete_student") ?>

<div class="flex fiex-col lg:flex-row gap-6 mt-8">
  <img src="<?= FileController::getProfilePictureUrl($student->user->id) ?>" alt="<?= $student->firstName . " " . $student->lastName ?>" class="w-64 aspect-square" />

  <div class="text-lg space-y-2">
    <p><b>Name:</b> <?= ucwords("{$student->firstName} {$student->lastName}") ?></p>
    <p><b>Student e-mail address:</b> <a href="mailto:<?= $student->user->email ?>"><?= $student->user->email ?></a></p>
    <p><b>Department:</b> <?= ucwords($student->department->name) ?></p>
    <?php if (!empty($personal_tutor)) : ?>
      <p><b>Personal tutor:</b> <a href="/staff/<?= $personal_tutor['staff_id'] ?>"><?= $personal_tutor ? ucwords("{$personal_tutor["first_name"]} {$personal_tutor["last_name"]}") : "<span class='text-gray-400'><i>Unassigned</i></span>" ?></a></p>
    <?php endif; ?>
    <p>
      <b>Modules:</b>
      <?php if (empty($modules)) : ?>
        <span class="text-gray-400"><i>Unassigned</i></span>
      <?php else : ?>
        <?= implode(", ", array_map(fn ($module) => $module["name"], $modules)) ?>
      <?php endif; ?>
    </p>
    <p><b>Date of birth:</b> <?= $student->dob->format("d/m/Y") ?></p>
    <p><b>Enrolled on:</b> <?= $student->enrolledAt->format("d/m/Y") ?></p>
  </div>
</div>

<div class="flex items-center gap-3 mt-3">
  <a href="/students/edit?id=<?= $student->id ?>" class="uk-button uk-button-small uk-button-primary">Edit</a>
  <form method="post">
    <input type="hidden" name="action" value="delete" />
    <input type="hidden" name="student_id" value="<?= $student->id ?>" />
    <button type="submit" class="uk-button uk-button-small uk-button-danger" data-confirm="Are you sure you want to delete this student?">Delete</button>
  </form>
</div>
</main>

<?php
$layout->end();
?>
