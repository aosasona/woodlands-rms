<?php
/**
 * @var \callable $prevValue
 * @var \Woodlands\Core\Models\Department[] $departments
 */
?>

<div class="input-group">
  <label for="name">Course name</label>
  <input class="uk-input" type="text" id="name" name="name" placeholder="Course name" aria-label="Course name" value="<?= $prevValue('name') ?>" required />
</div>

<div class="input-group">
  <label for="department">Department</label>
  <select name="department" id="department" class="uk-select">
    <option></option>
    <?php foreach ($departments as $department) : ?>
    <option value="<?= $department->id ?>"><?= ucfirst($department->name) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="grid grid-cols-2 gap-x-4">
  <div class="input-group">
    <label for="start_date">Start date</label>
    <input class="uk-input" type="date" id="start_date" name="start_date" placeholder="Course start date" aria-label="Course start date" value="<?= $prevValue('start_date') ?>" required />
  </div>
  <div class="input-group">
    <label for="end_date">End date</label>
    <input class="uk-input" type="date" id="end_date" name="end_date" placeholder="Course end date" aria-label="Course end date" value="<?= $prevValue('end_date') ?>" required />
  </div>
</div>

<div class="input-group">
  <label for="description">Description</label>
  <textarea class="uk-textarea" rows="8" id="description" name="description" placeholder="Course description" aria-label="Course description"><?= $prevValue("description") ?></textarea>
</div>
