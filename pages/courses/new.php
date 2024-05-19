<?php

use Phlo\Extensions\CSRFToken;


use App\State;
use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Department, Staff, Student, Module};


$breadcrumbs = [
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Courses", path: "/courses"),
    Breadcrumb::crumb(name: "Create new course", path: "/courses/new", disabled: true),
];

$prevValue = State::curryPrevFormValue("new_course");

$departments = Department::new()->all();
$staff_members = Staff::new()->literalWhere("`staff`.`department_id` IS NOT NULL")->with("user")->all();
$students = Student::new()->literalWhere("`students`.`student_id` IS NOT NULL")->with("user")->all();
$modules = Module::new()->all();

$layout = Layout::start("New course");
?>


<main class="container">
    <h1 class="font-bold">New course</h1>

    <?php Breadcrumb::render($breadcrumbs); ?>

    <form method="POST" class="max-w-2xl mt-6">
        <h2 class="text-xl font-bold mb-4" data-form-title>New department</h2>

        <?php State::renderError("new_department") ?>

        <?= CSRFToken::input(field_name: CSRFToken::DEFAULT_FIELD_NAME) ?>

        <input type="hidden" name="action" value="create" id="courseFormAction" />

        <ul class="uk-subnav uk-subnav-primary mt-4" uk-switcher>
            <li><a href="#">Details</a></li>
            <li><a href="#">Modules</a></li>
            <li><a href="#">Tutors</a></li>
            <li><a href="#">Students</a></li>
        </ul>

        <ul class="uk-switcher mt-5">
            <li><?php require_once __DIR__ . "/../../src/partials/courses/details.partial.php" ?></li>
            <li><?php require_once __DIR__ . "/../../src/partials/courses/modules.partial.php" ?></li>
            <li><?php require_once __DIR__ . "/../../src/partials/courses/tutors.partial.php" ?></li>
            <li><?php require_once __DIR__ . "/../../src/partials/courses/students.partial.php" ?></li>
        </ul>

        <button type="submit" class="uk-button uk-button-primary mt-8">Save</button>

    </form>

</main>

<?php
$layout->end();
?>
