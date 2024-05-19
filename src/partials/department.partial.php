<?php

/** @var array<\Woodlands\Core\Models\Staff> $staff_members **/

use App\State;
use Phlo\Extensions\CSRFToken;
use Woodlands\Core\Models\Department;

$departments = Department::new()->all();

$prevValue = State::curryPrevFormValue("new_department");
?>
<form method="POST" action="/api/facilities/department" class="lg:w-4/5" id="departmentForm">
  <h2 class="text-xl font-bold mb-4" data-form-title>New department</h2>

  <?php State::renderError("new_department") ?>

  <?= CSRFToken::input(field_name: "__csrf_token") ?>

  <input type="hidden" name="action" value="create_department" id="departmentFormAction" />
  <input type="hidden" name="department_id" value="" id="departmentFormHead" />

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
          <?php foreach ($staff_members as $department) : ?>
          <option value="<?= $department->id ?>" <?= $department->id == $prevValue("head") ? "selected" : "" ?>><?= ucwords("{$department->firstName} {$department->lastName} ({$department->user->email})") ?></option>
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
          <?php foreach ($staff_members as $department) : ?>
          <li class="flex justify-between items-center px-3 pt-2 pb-1 m-0 select-none" data-staff-id="<?= $department->id ?>">
            <div class="space-x-2">
              <input type="checkbox" class="uk-checkbox" name="assigned[]" value="<?= $department->id ?>" />

              <span data-searchable>
                <?= ucwords("{$department->firstName} {$department->lastName} ({$department->id})") ?>
              </span>

            </div>

            <div class="flex items-center gap-x-2">
              <p class="text-xs text-gray-500" data-searchable><?= $department->user->email ?></p>
              <?php if (!empty($department->departmentId)) : ?>
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

  <button type="submit" class="uk-button uk-button-primary">Save</button>
</form>

<section>
  <table class="w-full table-auto mt-8 records">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Created on</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($departments as $department): ?>
      <tr>
        <td data-department-id="<?= $department->id ?>"><?= $department->id ?></td>
        <td data-department-name="<?= $department->id ?>"><?= ucwords($department->name) ?></td>
        <td><?= $department->createdAt->format("d/m/Y H:i") ?></td>
        <td class="space-x-4">
          <a href="/staff?department=<?= $department->id ?>">View all staff</a>
          <a href="#" uk-toggle="target: #<?= 'dept-desc-'.$department->id ?>">Show full description</a>
          <a href="#" onclick="javascript:void(0)" data-edit-department="<?= $department->id ?>">Edit</a>

          <div id="<?= 'dept-desc-'.$department->id ?>" uk-modal>
            <div class="uk-modal-dialog">
              <button class="uk-modal-close-default" type="button" uk-close></button>
              <div class="uk-modal-header">
                <h2 class="uk-modal-title"><?= ucwords($department->name) ?></h2>
              </div>
              <div class="uk-modal-body max-h-96 overflow-y-auto">
                <p data-department-description="<?= $department->id ?>"><?= ucwords($department->description) ?></p>
              </div>
              <div class="uk-modal-footer uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close" type="button">Close</button>
              </div>
            </div>
          </div>
        </td>

      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="hidden" id="__STAFF_DATA">
    <?= json_encode($staff_members) ?>
  </div>

</section>
