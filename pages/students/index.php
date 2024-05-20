<?php

use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\{Student, Department};

$departments = Department::new()->all();

$students = Student::new()->literalWhere("`student_id` IS NOT NULL");

$filters = ["enrolled_at" => null, "department" => null];
if (!empty($_GET["filters"])) {
  $parts = explode(",", $_GET["filters"]);
  foreach ($parts as $part) {
    $key = explode(":", $part)[0];
    $value = explode(":", $part)[1] ?? null;
    $filters[$key] = htmlspecialchars(trim($value));
  }
}

if (!empty($filters["department"])) {
  $students = $students->and("`students`.`department_id`", "=", $filters["department"]);
}

if (!empty($filters["enrolled_at"])) {
  $year = preg_replace("/[^0-9]/", "", $filters["enrolled_at"]);
  $students = $students->and("`students`.`enrolled_at`", "LIKE", $year . "%");
}

/**
 * @var \Woodlands\Core\Models\Student[]
 */
$students = $students->withRelations("user", "department")->all();

$layout = Layout::start("Student records");
?>


<main class="container">
  <h1 class="font-bold">Student Records</h1>

  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Students", path: "/students", disabled: true),
  ]);
  ?>

  <div class="w-full flex justify-end mt-4">
    <a href="/students/new" class="uk-button uk-button-primary">Create student</a>
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
      <label for="enrolled_at">Filter by year enrolled</label>
      <input type="number" name="enrolled_at" id="enrolled_at" class="uk-input" placeholder="Filter by year enrolled" aria-label="Filter by year enrolled" value="<?= $filters['enrolled_at'] ?>" data-filter-name="enrolled_at" min="2000" max="<?= date("Y") ?>" />
    </div>

    <div class="input-group col-span-4 w-full">
      <label for="student_search">Filter by other details</label>
      <div class="flex items-center gap-4">
        <input type="search" class="uk-input" placeholder="Filter by name, email..." aria-label="Search for students" id="students_search" data-search-input data-search-target="students-list" />
        <?php if (!empty($_GET["filters"])) : ?>
          <a href="/students" class="block text-sm p-0 m-0" uk-tooltip="title: Clear filters;"><span uk-icon="icon: close"></span></a>
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
        <th>Department</th>
        <th>Enrollment date</th>
        <th>Created on</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody id="students-list">
      <?php if (empty($students)) : ?>
        <tr>
          <td colspan="7" class="text-center">No students found</td>
        </tr>
      <?php endif; ?>

      <?php foreach ($students as $student) : ?>
        <tr>
          <td data-searchable><?= $student->id ?></td>
          <td data-searchable><?= ucwords("{$student->firstName} {$student->lastName}") ?></td>
          <td data-searchable><?= $student?->user?->email ?? "<i>unknown</i>" ?></td>
          <td><?= $student->departmentId == null ? "<i>None</i>" : "<a href='/students?filters=department:$student->departmentId'>" . ucwords($student->department->name) . "</a>" ?></td>
          <td><?= $student->enrolledAt->format("d/m/Y") ?></td>
          <td><?= $student->createdAt->format("d/m/Y H:i") ?></td>
          <td class="space-x-4">
            <a href="/students/<?= $student->id ?>">View</a>
            <a href="/students/edit?id=<?= $student->id ?>">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php
$layout->end();
?>
