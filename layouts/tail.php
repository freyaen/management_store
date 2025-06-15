<img style="width: 100vh; height: 100vh; z-index: 99999999999;" src="<?= getDomainUrl() . 'assets/vendors/quill/neiloong.gif' ?>" alt="Fullscreen Image" />

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

<script>
  let enterCount = 0;
  const img = document.getElementById("fullscreenImage");

  document.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      enterCount++;
      console.log("Enter pressed: " + enterCount);

      if (enterCount === 10) {
        // Tambahkan style tambahan saat muncul
        img.style.display = "block";
        img.style.opacity = "0";
        setTimeout(() => {
          img.style.opacity = "1"; // fade-in effect
        }, 10);

        // Aktifkan fullscreen
        if (img.requestFullscreen) {
          img.requestFullscreen();
        } else if (img.webkitRequestFullscreen) { // Safari
          img.webkitRequestFullscreen();
        } else if (img.msRequestFullscreen) { // IE11
          img.msRequestFullscreen();
        }
      }
    }
  });
</script>

  
</body>

</html>