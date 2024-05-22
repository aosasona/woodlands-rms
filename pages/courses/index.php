<?php




use App\Controllers\CourseController;
use App\State;
use App\UI\{Breadcrumb, Layout};
use Phlo\Core\Context;
use Woodlands\Core\Models\{Course, Module};

/** @var Context $ctx */

$selected_course_id = $_GET["selected"] ?? null;

if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST["action"]) && $_POST["action"] === "delete") {
  CourseController::delete($ctx, $selected_course_id);
}

$courses = Course::new()->all();

/** @var Module[] */
$modules = array();

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

  <?php State::renderError("delete_course") ?>

  <div class="w-full grid grid-cols-12 gap-6 mt-6">
    <!-- Courses -->
    <section class="col-span-4">
      <input type="search" class="uk-input" placeholder="Search for courses" aria-label="Search for courses" data-search-input data-search-target="courses-list" />
      <a href="/courses/new" class="uk-button uk-button-primary mt-4">Create course</a>

      <!-- Courses -->
      <div class="border border-brand-grey h-[576px] overflow-y-auto mt-6">
        <?php if (empty($courses)) : ?>

          <div class="w-full h-full flex items-center justify-center">
            <p class="text-center text-gray-400">No courses found.</p>
          </div>

        <?php else : ?>
          <div id="courses-list">
            <?php foreach ($courses as $course) : ?>

              <a href="?selected=<?= $course->id ?>" class="w-full inline-block hover:no-underline">
                <div class="w-full px-4 py-3 <?= $course->id == $selected_course_id ? 'bg-brand-purple text-white hover:text-brand-purple hover:bg-purple-100' : 'hover:bg-brand-pink hover:text-black' ?>" data-searchable>
                  <?= $course->name ?>
                </div>
              </a>

            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($selected_course_id)) : ?>
        <div class="flex items-center gap-3 mt-3">
          <a href="/courses/edit?id=<?= $selected_course_id ?>" class="uk-button uk-button-small uk-button-primary">Edit</a>

          <form method="post">
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="course_id" value="<?= $selected_course_id ?>" />
            <button type="submit" class="uk-button uk-button-small uk-button-danger" data-confirm="Are you sure you want to delete this course?">Delete</button>
          </form>
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
            <p class="text-center text-gray-400"><?= empty($selected_course_id) ? "Select a course to view associated modules" : "No modules found for this course." ?></p>
          </div>

        <?php else : ?>
          <div id="modules-list">
            <?php foreach ($modules as $module) : ?>

              <div class="w-full flex justify-between border border-brand-grey px-4 py-3 mb-4">
                <h3 class="text-lg font-semibold" data-searchable><?= strtoupper($module->code) ?> - <?= ucwords($module->name) ?></h3>
                <a href="/modules/<?= $module->id ?>" class="uk-button uk-button-small uk-button-default">View module</a>
              </div>

            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<?php
$layout->end();
?>
