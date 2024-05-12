<?php

/** @var \Woodlands\Core\Models\Staff[] $staff_members **/

use App\State;
use Phlo\Extensions\CSRFToken;

$prevValue = State::curryPrevFormValue("new_department");
?>
<form method="POST" class="lg:w-4/5">
  <h2 class="text-xl font-bold mb-4">New department</h2>

  <?php echo CSRFToken::input(); ?>
  <input type="hidden" name="action" value="create_department" />

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div>
      <div class="input-group">
        <label for="name">Name</label>
        <input class="uk-input" type="text" id="name" name="name" placeholder="Computing" aria-label="Department name" minlength="2" value="<?= $prevValue("name") ?>" required />
      </div>


      <div class="input-group">
        <label for="head">Head of department</label>
        <select name="head" id="head" class="uk-select">
          <option></option>
          <?php foreach ($staff_members as $staff) : ?>
          <option value="<?= $staff->id ?>" <?= $staff->id == $prevValue("head") ? "selected" : "" ?>><?= ucfirst("{$staff->firstName} {$staff->lastName}") ?></option>
          <?php endforeach; ?>
        </select>
      </div>

    </div>

    <div class="input-group">
      <label for="description">Description</label>
      <textarea class="uk-textarea" id="description" name="description" placeholder="A brief description of the department" aria-label="Department description" rows="8" required><?= $prevValue("description") ?></textarea>
    </div>

  </div>

  <button type="submit" class="uk-button uk-button-primary">Create department</button>
</form>
