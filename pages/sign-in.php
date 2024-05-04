<?php
use App\UI\Layout;

$layout = Layout::start("Sign In")
?>

<main>
  <div></div>
  <div>
    <form action="/api/sign-in" method="post">
    </form>
  </div>
</main>

<?php
$layout->end();
?>
