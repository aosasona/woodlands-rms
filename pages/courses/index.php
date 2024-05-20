<?php

use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Course, Module};

$courses = Course::new()->all();

/** @var Module[] */
$modules = array();

/** @var array<int, array{tutors_count:int,students_count:int}> */
$module_counts = array();

$selected_course_id = $_GET["selected"] ?? null;
if (!empty($selected_course_id)) {
  $raw_modules = Module::new()->query("SELECT m.* FROM `modules` m JOIN `course_modules` cm ON m.`module_id` = cm.`module_id` WHERE cm.`course_id` = :course_id", ["course_id" => $selected_course_id]);
  foreach ($raw_modules as $module) {
    $modules[] = Module::from($module);
  }
}

$layout = Layout::start("Courses");
?>


<main class="container">
  <h1 class="font-bold">Courses</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Courses", path: "/courses", disabled: true),
  ]); ?>

  <div class="w-full grid grid-cols-12 gap-6 mt-6">
    <!-- Courses -->
    <section class="col-span-4">
      <input type="search" class="uk-input" placeholder="Search for courses" aria-label="Search for courses" data-search-input data-search-target="course-list" />
      <a href="/courses/new" class="uk-button uk-button-primary mt-4">Create course</a>

      <!-- Courses -->
      <div class="border border-brand-grey h-[576px] overflow-y-auto mt-6">
        <?php if (empty($courses)) : ?>

          <div class="w-full h-full flex items-center justify-center">
            <p class="text-center text-gray-400">No courses found.</p>
          </div>

        <?php else : ?>
          <?php foreach ($courses as $course) : ?>

            <a href="?selected=<?= $course->id ?>" class="w-full inline-block hover:no-underline">
              <div class="w-full px-4 py-3 <?= $course->id == $selected_course_id ? 'bg-brand-purple text-white hover:text-brand-purple hover:bg-purple-100' : 'hover:bg-brand-pink hover:text-black' ?>">
                <?= $course->name ?>
              </div>
            </a>

          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($selected_course_id)) : ?>
        <div class="flex items-center gap-3 mt-3">
          <a href="/coures/new?course_id=<?= $selected_course_id ?>&edit" class="uk-button uk-button-small uk-button-primary">Edit</a>
          <a href="/coures/new?course_id=<?= $selected_course_id ?>&delete" class="uk-button uk-button-small uk-button-danger">Delete</a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Modules -->
    <section class="col-span-8">
      <input type="search" class="uk-input" placeholder="Filter modules" aria-label="Filter modules" data-search-input data-search-target="modules-list" />
      <a href="/modules/new" class="uk-button uk-button-primary mt-4">Create module</a>

      <!-- Modules -->
      <div class="h-[576px] overflow-y-auto mt-6">
        <?php if (empty($modules)) : ?>

          <div class="w-full h-full flex items-center justify-center">
            <p class="text-center text-gray-400">No modules found.</p>
          </div>

        <?php else : ?>
          <ul id="modules-list">
            <?php foreach ($modules as $module) : ?>

              <div class="w-full flex justify-between border border-brand-grey px-4 py-3 mb-4">
                <h3 class="text-lg font-semibold" data-searchable><?= strtoupper($module->code) ?> - <?= ucwords($module->name) ?></h3>
                <a href="/modules/<?= $module->id ?>" class="uk-button uk-button-small uk-button-default">View module</a>
              </div>

            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<?php
$layout->end();
?>
