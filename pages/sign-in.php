<?php

use Phlo\Core\Context;
use App\UI\Layout;
use Woodlands\Core\Auth;
use Woodlands\Core\Models\Enums\UserType;
use Woodlands\Core\Exceptions\AppException;

/** @var Context $ctx **/

if(Auth::isLoggedIn()) {
    $ctx->redirect("/");
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $ctx->bodyOr("email", "");
        $password = $ctx->bodyOr("password", "");
        $remember_me = $ctx->bodyOr("remember_me", "") == "on";

        $user = Auth::login($email, $password, $remember_me, [UserType::Staff]);
        $ctx->redirect("/");
    } catch (Exception $e) {
        $message = $e instanceof AppException ? $e->getMessage() : "An error occurred";
        $_SESSION["signin.error"] =  $message;
        $ctx->redirect("/sign-in");
    }
}

$err = $_SESSION["signin.error"] ?? null;
unset($_SESSION["signin.error"]);

$layout = Layout::start("Sign In");
?>

<main class="grid grid-cols-1 lg:grid-cols-2 min-h-screen w-full">
  <div class="bg-brand-pink w-full h-full hidden lg:block"></div>
  <div class="w-full lg:w-4/6 mx-auto my-auto px-8 lg:px-2">
    <form method="post" class="flex flex-col gap-y-4">
        <h1 class="text-3xl font-bold">Sign In</h1>

      <?php if($err): ?>
        <div class="uk-alert-danger" uk-alert>
          <a class="uk-alert-close" uk-close></a>
          <p><?= $err ?></p>
        </div>
      <?php endif; ?>

        <input class="uk-input" type="text" name="email" placeholder="E-mail address" aria-label="E-mail address" required />

        <input class="uk-input" type="password" name="password" placeholder="Password" aria-label="Password" required />

        <div class="flex w-full justify-between items-center">
          <div class="flex items-center space-x-2 -ml-2">
              <input class="uk-toggle-switch uk-toggle-switch-secondary" name="remember_me" id="toggle-switch" type="checkbox" />
              <label class="uk-form-label" for="toggle-switch">Remember me</label>
          </div>

          <a href="#">Forgot password?</a>
        </div>

      <div class="flex w-full justify-end mt-6">
        <button class="uk-button" type="submit">Sign In</button>
      </div>
    </form>
  </div>
</main>

<?php
$layout->end();
?>
