<?php
use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("Staff records");
?>


<main class="container">
  <h1 class="font-bold">Staff Records</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Staff", path: "/staff", disabled: true),
  ]);
?>

  <div class="w-full flex justify-end mt-4">
    <a href="/staff/new" class="uk-button uk-button-primary">Add a new staff</a>

  </div>
</main>

<?php
$layout->end();
?>
