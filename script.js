/* --- script.js --- */
document.addEventListener("DOMContentLoaded", function() {
    // Aktifkan Tooltip Bootstrap (Hanya akan berjalan setelah HTML selesai di-load)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});