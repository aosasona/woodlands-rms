<?php
use App\UI\{Breadcrumb, Layout};
use Woodlands\Core\Models\Staff;

$page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT) ?? 1;
$limit = 50;
$staff = Staff::new()
  ->where("staff_id", "!=", "NULL")
  ->paginate(page: $page, perPage: $limit)
  ->withRelations("user", "department")
  ->all();

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

    <tbody>
      <?php foreach ($staff as $staff): ?>
      <tr>
        <td><?= $staff->id ?></td>
        <td><?= ucfirst("{$staff->firstName} {$staff->lastName}") ?></td>
        <td><?= $staff?->user?->email ?? "<i>unknown</i>" ?></td>
        <td><?= $staff->hireDate->format("d/m/Y") ?></td>
        <td><?= $staff->departmentId == null ? "<i>None</i>" : "" ?></td>
        <td><?= $staff->createdAt->format("d/m/Y H:i") ?></td>
        <td class="space-x-4">
          <a href="/staff/<?= $staff->id ?>">View</a>
          <a href="/staff/<?= $staff->id ?>/edit">Edit</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php
$layout->end();
?>
