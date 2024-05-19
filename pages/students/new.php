<?php

use App\UI\{Breadcrumb, Layout};
use App\State;
// use App\Controllers\StaffController;
use Woodlands\Core\Models\{Department, Staff};
use Woodlands\Core\Models\Enums\Gender;

$departments = Department::new()->all();
$staff = Staff::new()->all();

function prevValue(string $field)
{
    return State::prevFormValue("new_student", $field);
}

$layout = Layout::start("Student records");
?>


<main class="container">
  <h1 class="font-bold">New student</h1>
  <?php
  Breadcrumb::render([
    Breadcrumb::crumb(name: "Records", disabled: true),
    Breadcrumb::crumb(name: "Students", path: "/students"),
    Breadcrumb::crumb(name: "Create new record", path: "/students/new", disabled: true),
  ]); ?>

  <form method="POST" class="max-w-3xl 2xl:max-w-2xl mt-6" enctype="multipart/form-data">
    <?php State::renderError("new_student") ?>

    <div class="w-full flex gap-6">

      <!-- Profile image -->
      <div class="mb-6">
        <label for="profile-image" class="relative">
          <div class="bg-gray-200 w-40 aspect-square flex items-center justify-center">
            <p class="text-gray-500 text-xs text-center p-5 select-none" data-image-picker-placeholder="profile-image">Click to upload an image</p>

            <img src="" alt="Profile image" class="hidden object-cover aspect-square" data-image-picker-preview="profile-image" />
          </div>

          <!-- Remove image -->
          <button data-image-picker-reset="profile-image"><span uk-icon="icon: close"></span></button>
        </label>

        <input type="file" id="profile-image" name="profile_image" accept="image/png, image/jpeg" class="hidden" data-image-picker />

      </div>

      <div class="w-full space-y-4">
        <!-- Name -->
        <div class="w-full grid grid-cols-2 gap-4">
          <div class="input-group">
            <label for="first_name">First name</label>
            <input class="uk-input" type="text" id="first-name" name="first_name" placeholder="John" aria-label="First name" minlength="2" value="<?= prevValue("first_name") ?>" required />
          </div>

          <div class="input-group">
            <label for="last_name">Last name</label>
            <input class="uk-input" type="text" id="last-name" name="last_name" placeholder="Doe" aria-label="Last name" minlength="2" value="<?= prevValue("last_name") ?>" required />
          </div>
        </div>

        <!-- Department -->
        <div class="input-group">
          <label for="department-id">Department</label>
          <select name="department_id" id="department-id" class="uk-select">
            <option></option>
            <?php foreach ($departments as $department) : ?>
              <option value="<?= $department->id ?>"><?= $department->name ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Gender -->
        <div class="input-group !mt-6">
          <label>Gender</label>
          <div class="space-x-4">
            <?php foreach(Gender::cases() as $gender): ?>
              <label class="!text-black">
                <input class="uk-radio" type="radio" name="gender" value="<?= $gender->value ?>" <?php (prevValue('gender') == $gender->value) ? "checked=\"checked\"" : "" ?> required /> <?= $gender->name ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Date of birth -->
        <div class="input-group">
          <label for="dob">Date of birth</label>
          <input type="date" name="dob" class="uk-input" placeholder="Date of birth" value="<?= prevValue("dob") ?>" required />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input class="uk-input" type="password" id="password" name="password" placeholder="******" aria-label="Password" />
          <div class="text-right mt-2">
            <a href="#" onclick="javascript:void(0)" class="text-xs" data-password-toggle="password">Show password</a>
          </div>
        </div>
      </div>

    </div>

    <div class="flex justify-end mt-6">
      <button type="submit" class="uk-button uk-button-primary">Save</button>
    </div>

  </form>
</main>

<?php
$layout->end();
?>
