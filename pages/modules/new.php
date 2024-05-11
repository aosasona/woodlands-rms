<?php

use App\UI\{Breadcrumb, Layout};

$layout = Layout::start("New module");

$breadcrumbs = [
    Breadcrumb::crumb(name: "Management", disabled: true),
    Breadcrumb::crumb(name: "Modules", path: "/modules"),
    Breadcrumb::crumb(name: "Create new module", path: "/modules/new", disabled: true),
  ];
?>


<main class="container">
  <h1 class="font-bold">New modules</h1>

  <?php Breadcrumb::render($breadcrumbs); ?>

  <form method="POST" class="max-w-2xl mt-6">
    <div class="input-group">
      <label for="name">Module name</label>
      <input class="uk-input" type="text" id="name" name="name" placeholder="Module name" aria-label="Module name" required />
    </div>

    <div class="input-group">
      <label for="description">Description</label>
      <textarea class="uk-textarea" rows="10" id="description" name="description" placeholder="Module description" aria-label="Module description"></textarea>
    </div>
  </form>

</main>

<?php
$layout->end();
?>
