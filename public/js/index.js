$(function () {
    handleNav();
    handleImagePicker();
    handlePasswordVisibilityToggle();
})

function handleNav() {
    $("[data-nav-link]").on("click mouseenter", function (_) {
        const submenu = $(this).data("nav-link");
        const target = $(`[data-anchor="${submenu}"]`);
        if (!target.length) return;

        target.addClass("flex");
        target.removeClass("hidden");
    })

    $("[data-nav-link]").on("mouseleave", function () {
        const submenu = $(this).data("nav-link");
        const target = $(`[data-anchor="${submenu}"]`);
        if (!target.length) return;

        target.removeClass("flex");
        target.addClass("hidden");
    })
}

function handleImagePicker() {
    const picker = $("[data-image-picker]")
    const pickerId = picker.attr("id")
    if (!pickerId) {
        console.error("Image picker element must have an id attribute")
        return;
    }

    const placeholder = $(`[data-image-picker-placeholder="${pickerId}"]`)
    const preview = $(`[data-image-picker-preview="${pickerId}"]`)
    const reset = $(`[data-image-picker-reset="${pickerId}"]`)

    if (!placeholder.length || !preview.length) {
        console.error("Image picker element must have a placeholder and preview element, one or both are missing for: " + pickerId)
        return;
    }

    if (!reset.length) {
        console.warn("No reset button found for the image picker with ID: " + pickerId)
    } else {
        reset.on("click", function () {
            resetImagePicker(picker, placeholder, preview);
            $(this).hide();
        })
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
                reset.show()
                reset.css("display", "flex");
            }

            renderImagePreview(files, preview);
        } else {
            placeholder.removeClass("hidden");
            preview.addClass("hidden");

            if (reset.length) { reset.hide(); }
        }
    })
}

function renderImagePreview(files, preview) {
    const reader = new FileReader();
    reader.onload = function (e) {
        preview.attr("src", e.target.result);
    }

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
    })
}
