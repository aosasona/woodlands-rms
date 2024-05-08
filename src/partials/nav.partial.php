<?php

use Phlo\Extensions\CSRFToken;

?>

<nav class="w-screen fixed top-0 right-0 left-0 ">
  <div class="h-20 bg-brand-purple flex items-center justify-around gap-4" id="parent-nav-links">
    <img alt="Woodlands Logo" />
    <a href="#" data-target="records">Records</a>
    <a href="#" data-target="management">Management</a>

    <form action="/api/logout" method="POST">
      <?php echo CSRFToken::input(field_name: "__csrf_token") ?>
      <input type="submit" name="logout" value="Logout" />
    </form>
  </div>

  <div id="children-nav-links">
    <div data-anchor="records">
    </div>

    <div data-anchor="management">
    </div>
  </div>
</nav>
