<?php
use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("Modules");
?>


<main class="container">
  <h1 class="font-bold">Modules</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Modules", path: "/modules", disabled: true),
  ]);
?>

  <div class="w-full flex justify-end mt-4">
    <a href="/modules/new" class="uk-button uk-button-primary">Create module</a>
  </div>
</main>

<?php
$layout->end();
?>
