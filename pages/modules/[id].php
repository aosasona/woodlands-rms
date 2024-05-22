<?php


use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Database\Connection;
use Woodlands\Core\Models\{Course, Module};


/** @var Context $ctx */

$module_id = preg_replace(pattern: "[^0-9]", replacement: "",  subject: $ctx->getParam("id", ""));
if (empty($module_id)) {
  $ctx->redirect("/courses");
}

$module = Module::new()->findById($module_id);
if (empty($module)) {
  $layout = Layout::start("Not found");

  echo <<<HTML
    <main class="container min-h-screen">
      <h1>Module not found</h1>
      <div class="text-center mt-24">
        <h2 class="text-gray-400 text-lg mb-6">Record not found.</h2>
        <a href="/courses" class="uk-button uk-button-primary">Back to courses</a>
      </div>
    </main>
  HTML;

  $layout->end();
  exit;
}

$parent_courses = Connection::getInstance()->query("SELECT c.* FROM `courses` c JOIN `course_modules` cm ON c.`course_id` = cm.`course_id` WHERE cm.`module_id` = :module_id", ["module_id" => $module_id])->fetchAll(PDO::FETCH_ASSOC);
$students = Connection::getInstance()->query("SELECT s.* FROM `students` s JOIN `student_modules` sm ON s.`student_id` = sm.`student_id` WHERE sm.`module_id` = :module_id", ["module_id" => $module_id])->fetchAll(PDO::FETCH_ASSOC);

$layout = Layout::start("Modules");
?>

<main class="container">
  <h1 class="font-bold">Modules</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Modules", path: "/courses", disabled: true),
    Breadcrumb::crumb(name: ucwords($module->name), path: "/modules/{$module->id}", disabled: true),
  ]); ?>

  <h1 class="mt-4"><?= strtoupper($module->code) ?> - <?= ucwords($module->name) ?></h1>

  <div class="mt-4">
    <h2 class="text-lg font-bold">Description</h2>
    <?= $module->description ?>
  </div>

  <div class="mt-4">
    <h2 class="text-xl font-bold">Students</h2>
    <?php if (empty($students)) : ?>
      <p class="text-gray-400"><i>No students assigned to this module.</i></p>
    <?php else : ?>

      <ul class="list-disc list-inside">
        <?php foreach ($students as $student) : ?>
          <li><a href="/students/<?= $student["student_id"] ?>"><?= ucwords("{$student["first_name"]} {$student["last_name"]}") ?></a></li>
        <?php endforeach; ?>

      </ul>
    <?php endif; ?>
  </div>
</main>

<?php $layout->end(); ?>
