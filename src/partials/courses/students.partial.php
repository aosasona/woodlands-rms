<?php

/**
 * @var \Woodlands\Core\Models\Student[] $students
 * @var \callable $prevValue
 */

$prev_students = $prevValue("students") ?? [];
?>
<div>
  <h2 class="text-xl font-bold mb-4">Assign Students</h2>
  <input type="search" class="uk-input" placeholder="Filter students" aria-label="Search for student" data-search-input data-search-target="students-list" />
  <div class="h-96 border border-t-0 border-brand-grey">
    <ul class="uk-list uk-list-divider uk-overflow-auto" id="students-list">
      <?php foreach ($students as $student) : ?>
        <li class="flex justify-between items-center px-3 pt-2 pb-1 m-0 select-none" data-student-id="<?= $student->id ?>" data-enrollment-year="<?= $student->enrolledAt->format("Y") ?>">
          <label for="student_<?= $student->id ?>" class="space-x-2">
            <input type="checkbox" class="uk-checkbox" id="student_<?= $student->id ?>" name="students[]" value="<?= $student->id ?>" <?= in_array((string)$student->id, $prev_students) ? "checked='checked'" : "" ?> />

            <span data-searchable>
              <?= ucwords("{$student->firstName} {$student->lastName} ({$student->id})") ?>
            </span>
          </label>


          <div class="flex items-center gap-x-2">
            <p class="text-xs text-gray-500" data-searchable><?= $student->user->email ?></p>
          </div>
        </li>
      <?php endforeach; ?>
  </div>
</div>
