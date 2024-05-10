<?php

use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("Student records");
?>


<main class="container">
  <h1 class="font-bold">New student</h1>
  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Students", path: "/students"),
    Breadcrumb::crumb(name: "Create new record", path: "/students/new", disabled: true),
  ]);
?>

</main>

<?php
$layout->end();
?>
