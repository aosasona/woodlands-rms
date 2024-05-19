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
  ]);
?>

  <div class="w-full flex justify-end mt-4">
    <a href="/courses/new" class="uk-button uk-button-primary">Create course</a>
  </div>
</main>

<?php
$layout->end();
?>
