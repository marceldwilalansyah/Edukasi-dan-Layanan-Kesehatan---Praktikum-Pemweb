document.addEventListener("DOMContentLoaded", function () {

    const alerts = document.querySelectorAll(".alert");

    alerts.forEach(function (alert) {

        setTimeout(function () {
            alert.style.display = "none";
        }, 5000);

    });

});

// Konfirmasi sebelum aksi berbahaya: <button data-confirm="Yakin?">
document.addEventListener("click", function (event) {

    const tombol = event.target.closest("[data-confirm]");

    if (tombol && !confirm(tombol.getAttribute("data-confirm"))) {
        event.preventDefault();
    }

});
