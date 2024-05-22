<?php



use App\Controllers\{FileController, StaffController};
use App\State;
use App\UI\Layout;
use Phlo\Core\Context;
use Woodlands\Core\Models\Staff;
use Woodlands\Core\Database\Connection;

/** @var Context $ctx */

$staff_id = preg_replace(pattern: "[^0-9]", replacement: "",  subject: $ctx->getParam("id", ""));
if (empty($staff_id)) {
  $ctx->redirect("/staff");
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST["action"]) && $_POST["action"] === "delete") {
  StaffController::delete($ctx);
}

$staff = Staff::new()
  ->where("staff_id", "=", $staff_id)
  ->withRelations("user", "department")
  ->one();

$modules = Connection::getInstance()->query("SELECT m.* FROM `modules` m JOIN `module_tutors` mt ON m.`module_id` = mt.`module_id` WHERE mt.`staff_id` = :staff_id", ["staff_id" => $staff_id])->fetchAll(PDO::FETCH_ASSOC);

$layout = Layout::start(empty($staff) ? "Not found" : "{$staff->firstName} {$staff->lastName}");
?>

<main class="container min-h-screen">
  <h1>Staff details</h1>

  <?php if (empty($staff)) : ?>
    <div class="text-center mt-24">
      <h2 class="text-gray-400 text-lg mb-6">Record not found.</h2>
      <a href="/staff" class="uk-button uk-button-primary">Back to staff</a>
    </div>
</main>
<?php
    $layout->end();
    return;
?>
<?php endif; ?>

<?php State::renderError("delete_staff") ?>

<div class="flex fiex-col lg:flex-row gap-6 mt-8">
  <img src="<?= FileController::getProfilePictureUrl($staff->user->id) ?>" alt="<?= $staff->firstName . " " . $staff->lastName ?>" class="w-64 aspect-square" />

  <div class="text-lg space-y-2">
    <p><b>Name:</b> <?= ucwords("{$staff->firstName} {$staff->lastName}") ?></p>
    <p><b>Staff e-mail address:</b> <a href="mailto:<?= $staff->user->email ?>"><?= $staff->user->email ?></a></p>
    <p><b>Department:</b> <?= ucwords($staff?->department?->name ?? "Unassigned") ?></p>
    <p>
      <b>Modules:</b>
      <?php if (empty($modules)) : ?>
        <span class="text-gray-400"><i>Unassigned</i></span>
      <?php else : ?>
        <?= implode(", ", array_map(fn ($module) => $module["name"], $modules)) ?>
      <?php endif; ?>
    </p>
    <p><b>Date of birth:</b> <?= $staff->dob->format("d/m/Y") ?></p>
  </div>
</div>

<div class="flex items-center gap-3 mt-3">
  <a href="/staff/edit?id=<?= $staff->id ?>" class="uk-button uk-button-small uk-button-primary">Edit</a>
  <form method="post">
    <input type="hidden" name="action" value="delete" />
    <input type="hidden" name="staff_id" value="<?= $staff->id ?>" />
    <button type="submit" class="uk-button uk-button-small uk-button-danger" data-confirm="Are you sure you want to delete this staff?">Delete</button>
  </form>
</div>
</main>

<?php
$layout->end();
?>
