<?php

use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Department, Staff};

$staff_members = Staff::new()->all();

$layout = Layout::start("Facilities");
?>

<main class="container">
  <h1 class="font-bold">Facilities</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Facilities", path: "/facilities", disabled: true),
  ]); ?>

  <ul class="uk-subnav uk-subnav-primary mt-4" uk-switcher>
      <li><a href="#">Departments</a></li>
      <li><a href="#">Classrooms</a></li>
  </ul>

  <ul class="uk-switcher mt-5">
      <li><?php require_once __DIR__ ."/../src/partials/department.partial.php" ?><li>
      <li><?php require_once __DIR__ ."/../src/partials/classroom.partial.php" ?><li>
  </ul>
</main>

<?php
$layout->end();
?>
