<?php

use Phlo\Extensions\CSRFToken;



use App\Controllers\ModuleController;
use App\State;
use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\Staff;

/** @var \Phlo\Core\Context $ctx **/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  ModuleController::create($ctx);
  exit;
}

$prev = State::curryPrevFormValue("new_module");

$staff_members = Staff::new()->literalWhere("`staff`.`department_id` IS NOT NULL")->withRelations("user", "department")->all();

$breadcrumbs = [
  Breadcrumb::crumb(name: "Management", disabled: true),
  Breadcrumb::crumb(name: "Modules", path: "/modules"),
  Breadcrumb::crumb(name: "Create new module", path: "/modules/new", disabled: true),
];

$layout = Layout::start("New module");
?>


<main class="container">
  <h1 class="font-bold">New modules</h1>

  <?php Breadcrumb::render($breadcrumbs); ?>

  <form method="POST" class="mt-6">
    <?php State::renderError("new_module") ?>

    <?= CSRFToken::input(field_name: CSRFToken::DEFAULT_FIELD_NAME) ?>

    <div class="w-5/6 grid grid-cols-9 gap-6">
      <section class="col-span-4">
        <div class="input-group">
          <label for="name">Name</label>
          <input class="uk-input" type="text" id="name" name="name" placeholder="Module name" aria-label="Module name" value="<?= $prev('name') ?>" required />
        </div>

        <div class="input-group">
          <label for="code">Code</label>
          <input class="uk-input" type="text" id="code" name="code" placeholder="Module code e.g CS10056" aria-label="Module code" value="<?= $prev('code') ?>" required />
        </div>

        <div class="input-group">
          <label for="description">Description</label>
          <textarea class="uk-textarea" rows="10" id="description" name="description" placeholder="Module description" aria-label="Module description"><?= $prev('description') ?></textarea>
        </div>
      </section>

      <section class="col-span-5 -mt-6">
        <?php require_once __DIR__ . "/../../src/partials/courses/tutors.partial.php" ?>
        <div class="flex justify-end">
          <button type="submit" class="uk-button uk-button-primary mt-8">Save</button>
        </div>
      </section>
    </div>

  </form>

</main>

<?php
$layout->end();
?>
