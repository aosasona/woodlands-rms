<?php

/**
 * @var \Woodlands\Core\Models\Modules[] $modules
 * @var \callable $prevValue
 */

$prevModules = $prevValue("modules") ?? [];
?>
<div>
  <h2 class="text-xl font-bold mb-4">Add Modules</h2>
  <input type="search" class="uk-input" placeholder="Filter modules" aria-label="Search for module" data-search-input data-search-target="modules-list" />
  <div class="h-96 border border-t-0 border-brand-grey">
    <ul class="uk-list uk-list-divider uk-overflow-auto" id="modules-list">
      <?php foreach ($modules as $module) : ?>
        <li class="flex justify-between items-center px-3 pt-2 pb-1 m-0 select-none" data-module-id="<?= $module->id ?>">
          <div class="space-x-2">
            <input type="checkbox" class="uk-checkbox" name="modules[]" value="<?= $module->id ?>" <?= in_array($module->id, $prevModules) ? "selected='selected'" : "" ?> />

            <span data-searchable>
              <?= strtoupper($module->code) . " - " . ucwords("{$module->name}") ?>
            </span>
        </li>
      <?php endforeach; ?>
  </div>
</div>
