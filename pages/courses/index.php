<?php
use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("Courses");
?>


<main class="container">
  <h1 class="font-bold">Courses</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Courses", path: "/courses", disabled: true),
  ]); ?>

  <div class="w-full grid grid-cols-12 gap-4 mt-6">
    <!-- Courses -->
    <section class="col-span-4">
      <input type="search" class="uk-input" placeholder="Search for courses" aria-label="Search for courses" data-search-input data-search-target="course-list" />
      <a href="/courses/new" class="uk-button uk-button-primary mt-4">Create course</a>
    </section>

    <!-- Modules -->
    <section class="col-span-8">
      <input type="search" class="uk-input" placeholder="Filter modules" aria-label="Filter modules" data-search-input data-search-target="modules-list" />
      <a href="/modules/new" class="uk-button uk-button-primary mt-4">Create module</a>
    </section>
  </div>
</main>

<?php
$layout->end();
?>
