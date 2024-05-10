$(function () {
    handleNav();
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
