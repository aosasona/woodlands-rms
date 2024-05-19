$(function () {
    handleNav();
    handleImagePicker();
    handlePasswordVisibilityToggle();
    handleListFilter();
    editDepartment();

    // Intercept destructive actions
    $("[data-confirm]").on("click", function (e) {
        const message = $(this).data("confirm");
        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    // Handle filtering data (live grep)
    $("[data-filter-name]").on("change", handleFilters);

    // Display appropriate students and tutors based on course details
    $("#course-start-date").on("change", showStudentsInCourseRange).trigger("change");
    $("#course-department").on("change", showTutorsAndStudentsInDepartment).trigger("change");
});

function showStudentsInCourseRange() {
    const value = $(this).val();
    if (!value) return;
    const courseStartYear = new Date(value).getFullYear();

    const studentsList = $("#students-list");
    studentsList.children().each(function () {
        const enrollmentYear = $(this).data("enrollment-year");
        // Only display students that have been enrolled after or in the same year the course would start
        if (enrollmentYear >= courseStartYear) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function showTutorsAndStudentsInDepartment() {
    const departmentId = $(this).val();
    if (!departmentId) return;

    const tutorsList = $("#tutors-list");
    const studentsList = $("#students-list");

    tutorsList.children().each(function () {
        const tutorDepartment = $(this).data("department");
        if (tutorDepartment === departmentId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    studentsList.children().each(function () {
        const studentDepartment = $(this).data("department-id");
        if (studentDepartment === departmentId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

/**
 * @param {string} filterString
 * @returns {Object}
 **/
function parseFilters(filterString) {
    const filters = filterString.split(",");
    const filter = {};

    filters.forEach((f) => {
        const parts = f?.split(":");
        if (!parts || parts.length !== 2) return;
        filter[parts[0]] = parts[1];
    });

    return filter;
}

/**
 * @param {any} filters
 **/
function stringifyFilters(filters) {
    const filterStr = Object.entries(filters)
        .map(([key, value]) => `${key}:${value}`)
        .join(",");

    return filterStr;
}

function handleFilters() {
    const name = $(this).data("filter-name");
    const value = $(this).val();
    const currentFilterStr = new URLSearchParams(window.location.search)?.get("filters");
    const filters = currentFilterStr ? parseFilters(currentFilterStr) : {};
    filters[name] = value;

    const filterStr = stringifyFilters(filters);
    const url = new URL(window.location.href);
    url.searchParams.set("filters", filterStr);
    window.location.href = url.toString();
}

function handleNav() {
    $("[data-nav-link]").on("click mouseenter", function (_) {
        const submenu = $(this).data("nav-link");
        const target = $(`[data-anchor="${submenu}"]`);
        if (!target.length) return;

        target.addClass("flex");
        target.removeClass("hidden");
    });

    $("[data-nav-link]").on("mouseleave", function () {
        const submenu = $(this).data("nav-link");
        const target = $(`[data-anchor="${submenu}"]`);
        if (!target.length) return;

        target.removeClass("flex");
        target.addClass("hidden");
    });
}

function handleImagePicker() {
    const picker = $("[data-image-picker]");
    const pickerId = picker.attr("id");

    if (!picker.length) {
        return;
    }

    if (!pickerId) {
        console.error("Image picker element must have an id attribute");
        return;
    }

    const placeholder = $(`[data-image-picker-placeholder="${pickerId}"]`);
    const preview = $(`[data-image-picker-preview="${pickerId}"]`);
    const reset = $(`[data-image-picker-reset="${pickerId}"]`);

    if (!placeholder.length || !preview.length) {
        console.error("Image picker element must have a placeholder and preview element, one or both are missing for: " + pickerId);
        return;
    }

    if (!reset.length) {
        console.warn("No reset button found for the image picker with ID: " + pickerId);
    } else {
        reset.on("click", function () {
            resetImagePicker(picker, placeholder, preview);
            $(this).hide();
        });
    }

    picker.on("change", function () {
        const files = picker.prop("files");
        if (files.length) {
            if (files[0].size > 1024 * 1024) {
                UIkit.notification({ message: "File size should not exceed 1MB", status: "danger", pos: "top-right" });
                return;
            }

            placeholder.addClass("hidden");
            preview.removeClass("hidden");

            if (reset.length) {
                reset.show();
                reset.css("display", "flex");
            }

            renderImagePreview(files, preview);
        } else {
            placeholder.removeClass("hidden");
            preview.addClass("hidden");

            if (reset.length) {
                reset.hide();
            }
        }
    });
}

function renderImagePreview(files, preview) {
    const reader = new FileReader();
    reader.onload = function (e) {
        preview.attr("src", e.target.result);
    };

    reader.readAsDataURL(files[0]);
}

function resetImagePicker(picker, placeholder, preview) {
    picker.val("");
    placeholder.removeClass("hidden");
    preview.addClass("hidden");
}

function handlePasswordVisibilityToggle() {
    const toggle = $("[data-password-toggle]");
    toggle.on("click", function () {
        const target = $(this).data("password-toggle");
        const password = $(`#${target}`);
        if (password.attr("type") === "password") {
            $(this).text("Hide password");
            password.attr("type", "text");
        } else {
            $(this).text("Show password");
            password.attr("type", "password");
        }
    });
}

function handleListFilter() {
    const searchInput = $("[data-search-input]");
    if (!searchInput.length) return;

    searchInput.on("input", function () {
        const query = $(this).val().toLowerCase();
        const searchTarget = $(this).data("search-target");
        const list = $(`#${searchTarget}`);
        if (!query.length) return list.children().show();

        if (!list.length) {
            return;
        }

        list.children().each(function () {
            const searchableItems = $(this).find("[data-searchable]");
            if (!searchableItems.length) return;

            const itemContainsQuery =
                searchableItems.filter(function () {
                    return $(this).text().toLowerCase().includes(query);
                }).length > 0;

            return itemContainsQuery ? $(this).show() : $(this).hide();
        });
    });
}

function editDepartment() {
    const editButton = $("[data-edit-department]");
    if (!editButton.length) return;
    const staffData = JSON.parse($("#__STAFF_DATA").text() || "[]");

    editButton.on("click", function () {
        const departmentId = $(this).data("edit-department");
        const departmentName = $(`[data-department-name="${departmentId}"]`).text();
        const departmentDescription = $(`[data-department-description="${departmentId}"]`).text();

        const assignedStaff = staffData.filter((staff) => staff.department_id === departmentId);
        const headId = assignedStaff.find((staff) => staff.role?.toLowerCase() == "head of department")?.staff_id;

        const form = $("#departmentForm");
        form.find("[name='department_id']").val(departmentId);
        form.find("[name='name']").val(departmentName);
        form.find("[name='description']").val(departmentDescription);
        form.find("option[value='" + headId + "']").attr("selected", "selected");

        const assignedStaffList = $("#staff-list");
        assignedStaffList.children().each(function () {
            const staffId = $(this).data("staff-id");
            const isAssigned = assignedStaff.find((staff) => staff.staff_id === staffId);
            $(this).find("input[type='checkbox']").prop("checked", !!isAssigned);
        });

        form.find("[data-form-title]").text("Update Department");
        form.find("[name='action']").val("update_department");
    });
}
