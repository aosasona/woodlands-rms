<?php

/**
 * @var \Woodlands\Core\Models\Staff[] $staff_members
 * @var \callable $prevValue
 */

$prevTutors = $prevValue("tutors") ?? [];
?>
<div>
  <h2 class="text-xl font-bold mb-4">Assign Tutors</h2>
  <input type="search" class="uk-input" placeholder="Search for staff" aria-label="Search for staff" data-search-input data-search-target="tutors-list" />
  <div class="h-96 border border-t-0 border-brand-grey">
    <ul class="uk-list uk-list-divider uk-overflow-auto" id="tutors-list">
      <?php foreach ($staff_members as $staff) : ?>
        <li class="flex justify-between items-center px-3 pt-2 pb-1 m-0 select-none" data-staff-id="<?= $staff->id ?>" data-department-id="<?= $staff->departmentId ?>">
          <div class="space-x-2">
            <input type="checkbox" class="uk-checkbox" name="tutors[]" value="<?= $staff->id ?>" <?= in_array($staff->id, $prevTutors) ? "selected='selected'" : "" ?> />

            <span data-searchable>
              <?= ucwords("{$staff->firstName} {$staff->lastName} ({$staff->id})") ?>
            </span>

          </div>

          <div class="flex items-center gap-x-2">
            <p class="text-xs text-gray-500" data-searchable><?= $staff->user->email ?></p>
          </div>
        </li>
      <?php endforeach; ?>
  </div>
</div>
