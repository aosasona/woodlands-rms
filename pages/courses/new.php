<?php

use Phlo\Extensions\CSRFToken;


use App\State;
use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Department, Student};

$layout = Layout::start("New course");

$breadcrumbs = [
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Courses", path: "/courses"),
    Breadcrumb::crumb(name: "Create new course", path: "/courses/new", disabled: true),
  ];

$departments = Department::new()->all();
?>


<main class="container">
  <h1 class="font-bold">New course</h1>

  <?php Breadcrumb::render($breadcrumbs); ?>

  <form method="POST" class="max-w-2xl mt-6">
    <h2 class="text-xl font-bold mb-4" data-form-title>New department</h2>

    <?php State::renderError("new_department") ?>

    <?= CSRFToken::input(field_name: "__csrf_token") ?>

    <input type="hidden" name="action" value="create_course" id="courseFormAction" />
    <div class="input-group">
      <label for="name">Course name</label>
      <input class="uk-input" type="text" id="name" name="name" placeholder="Course name" aria-label="Course name" required />
    </div>

    <div class="input-group">
      <label for="department">Department</label>
      <select name="department" id="department" class="uk-select">
        <option></option>
        <?php foreach ($departments as $department) : ?>
        <option value="<?= $department->id ?>"><?= ucfirst($department->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="input-group">
      <label for="description">Description</label>
      <textarea class="uk-textarea" rows="10" id="description" name="description" placeholder="Course description" aria-label="Course description"></textarea>
    </div>
  </form>

</main>

<?php
$layout->end();
?>
