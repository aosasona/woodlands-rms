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

      <div class="input-group">
        <label for="description">Description</label>
        <textarea class="uk-textarea" id="description" name="description" placeholder="A brief description of the department" aria-label="Department description" rows="8" maxlength="8096" required><?= $prevValue("description") ?></textarea>
      </div>
    </div>

    <div>
      <h2 class="text-xl font-bold mb-4">Assign Staff</h2>
      <input type="search" class="uk-input" placeholder="Search for staff" aria-label="Search for staff" data-search-input data-search-target="staff-list" />
      <div class="h-96 border border-t-0 border-brand-grey">
        <ul class="uk-list uk-list-divider uk-overflow-auto" id="staff-list">
          <?php foreach ($staff_members as $staff) : ?>
          <li class="flex justify-between items-center px-3 pt-2 pb-1 m-0 select-none">
            <div class="space-x-2">
              <input type="checkbox" class="uk-checkbox" name="assigned[]" value="<?= $staff->id ?>" />

              <span data-searchable>
                <?= ucfirst("{$staff->firstName} {$staff->lastName} ({$staff->id})") ?>
              </span>

            </div>

            <div class="flex items-center gap-x-2">
              <p class="text-xs text-gray-500" data-searchable><?= $staff->user->email ?></p>
              <?php if (!empty($staff->departmentId)) : ?>
                <div uk-tooltip="title: This staff is already assigned to a department; pos: top-right" class="text-xs text-brand-grey">
                  <span uk-icon="warning" class="text-brand-notice text-sm"></span>
                </div>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
      </div>
    </div>

  </div>

  <button type="submit" class="uk-button uk-button-primary">Create department</button>
</form>
