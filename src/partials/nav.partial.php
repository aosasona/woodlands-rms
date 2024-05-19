<?php

use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Auth;

$user = Auth::user();
$email = $user?->email ?? "unknown";
?>

<nav class="w-screen fixed top-0 right-0 left-0 bg-brand-purple z-50">
  <div class="h-20 container mx-auto flex items-center justify-center gap-x-40" id="parent-nav-links">
    <img src="/public/images/logo.jpg" class="h-14 aspect-square" alt="Woodlands Logo" />

    <div data-nav-link="records">
      <a href="#">Records</a>

      <div data-anchor="records" class="hidden">
        <a href="/students">Student records</a>
        <a href="/staff">Staff records</a>
        <a href="/facilities">Facilities</a>
      </div>
    </div>

    <div data-nav-link="management">
      <a href="#">Management</a>

      <div data-anchor="management" class="hidden">
        <a href="/attendance">Attendance</a>
        <a href="/courses">Course management</a>
        <a href="/modules">Module management</a>
      </div>
    </div>

    <form action="/api/logout" method="POST">
      <?php echo CSRFToken::input(field_name: "__logout_csrf_token") ?>
      <div uk-tooltip="<?= "Signed in as {$email}" ?>">
        <input type="submit" name="logout" value="Logout" />
      </div>
    </form>

  </div>
</nav>
