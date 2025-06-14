<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_GET['success'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        text: '<?= htmlspecialchars($_GET["success"]) ?>',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php unset($_GET['success']); endif; ?>
<script>
function confirmDelete(id, route = '') {
    Swal.fire({
        text: "This will delete the product permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            let deleteRoute = route ? route : "delete.php?id=";
            window.location.href =  deleteRoute + id;
        }
    });
}
</script>

<script src="<?= $base ?>/assets/vendors/bundle.js"></script>
<script src="<?= $base ?>/assets/vendors/charts/apex/apexcharts.min.js"></script>
<script src="<?= $base ?>/assets/vendors/datepicker/daterangepicker.js"></script>
<script src="<?= $base ?>/assets/vendors/dataTable/datatables.min.js"></script>
<script src="<?= $base ?>/assets/js/app.min.js"></script>
</body>

</html>