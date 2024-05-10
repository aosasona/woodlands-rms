<?php
use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("Student records");
?>


<main class="container">
  <h1 class="font-bold">Student Records</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Students", path: "/students", disabled: true),
  ]);
?>

  <div class="w-full flex justify-end mt-4">
    <a href="/students/new" class="uk-button uk-button-primary">Create student</a>
  </div>
</main>

<?php
$layout->end();
?>
