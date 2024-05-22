<?php

use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Department, Staff};

$departments = Department::new()->all();

$staff_members = Staff::new()->literalWhere("`staff_id` IS NOT NULL");

$filters = ["hired_in" => null, "department" => null];
if (!empty($_GET["filters"])) {
  $parts = explode(",", $_GET["filters"]);
  foreach ($parts as $part) {
    $key = explode(":", $part)[0];
    $value = explode(":", $part)[1] ?? null;
    $filters[$key] = htmlspecialchars(trim($value));
  }
}

if (!empty($filters["department"])) {
  $staff_members = $staff_members->and("`staff`.`department_id`", "=", $filters["department"]);
}

if (!empty($filters["hired_in"])) {
  $year = preg_replace("/[^0-9]/", "", $filters["hired_in"]);
  $staff_members = $staff_members->and("`staff`.`hired_on`", "LIKE", $year . "%");
}

/**
 * @var \Woodlands\Core\Models\Staff[]
 */
$staff_members = $staff_members
  ->withRelations("user", "department")
  ->all();
// ->paginate(page: $page, perPage: $limit)

$layout = Layout::start("Staff records");
?>


<main class="container">
  <h1 class="font-bold">Staff Records</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Staff", path: "/staff", disabled: true),
  ]);
  ?>

  <div class="w-full flex justify-end mt-4">
    <a href="/staff/new" class="uk-button uk-button-primary">Add a new staff</a>
  </div>

  <div class="w-full grid grid-cols-9 gap-4 items-center mt-4">
    <div class="input-group col-span-3">
      <label for="filter_department">Department</label>
      <select name="filter_department" id="filter_department" class="uk-select" data-filter-name="department">
        <option value="">Filter by department</option>
        <?php foreach ($departments as $department) : ?>
          <option value="<?= $department->id ?>" <?= $filters["department"] == $department->id ? "selected" : "" ?>><?= ucwords($department->name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="input-group col-span-2">
      <label for="filter_year_hired">Filter by year hired</label>
      <input type="number" name="filter_year_hired" id="filter_year_hired" class="uk-input" placeholder="Filter by year hired" aria-label="Filter by year hired" value="<?= $filters['hired_in'] ?>" data-filter-name="hired_in" min="2000" max="<?= date("Y") ?>" />
    </div>

    <div class="input-group col-span-4 w-full">
      <label for="staff_search">Filter by other details</label>
      <div class="flex items-center gap-4">
        <input type="search" class="uk-input" placeholder="Search for staff" aria-label="Search for staff" id="staff_search" data-search-input data-search-target="staff-list" />
        <?php if (!empty($_GET["filters"])) : ?>
          <a href="/staff" class="block text-sm p-0 m-0" uk-tooltip="title: Clear filters;"><span uk-icon="icon: close"></span></a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <table class="w-full table-auto mt-8 records">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>E-mail address</th>
        <th>Hired on</th>
        <th>Department</th>
        <th>Created on</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody id="staff-list">
      <?php if (empty($staff_members)) : ?>
        <tr>
          <td colspan="7" class="text-center">No staff members found</td>
        </tr>
      <?php endif; ?>

      <?php foreach ($staff_members as $staff) : ?>
        <tr>
          <td data-searchable><?= $staff->id ?></td>
          <td data-searchable><?= ucwords("{$staff->firstName} {$staff->lastName}") ?></td>
          <td data-searchable><?= $staff?->user?->email ?? "<i>unknown</i>" ?></td>
          <td><?= $staff->hireDate->format("d/m/Y") ?></td>
          <td><?= $staff->departmentId == null ? "<i>None</i>" : "<a href='/staff?filters=department:$staff->departmentId'>" . ucwords($staff->department->name) . "</a>" ?></td>
          <td><?= $staff->createdAt->format("d/m/Y H:i") ?></td>
          <td class="space-x-4">
            <a href="/staff/<?= $staff->id ?>">View</a>
            <a href="/staff/edit?id=<?= $staff->id ?>">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php
$layout->end();
?>
