<?php

use App\UI\Layout;

$layout = Layout::start("Staff records");
?>

<main class="container h-[80vh] flex flex-col gap-4 items-center justify-center">
  <h1>Page not found!</h1>
  <p>Sorry, the page you are looking for does not exist or has been moved.</p>
  <a href="/" class="uk-button uk-button-primary">Go home</a>
</main>

<?php
$layout->end();
?>
