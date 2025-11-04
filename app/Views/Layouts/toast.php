<?php if (session()->getFlashdata('success')): ?>
<script>
Swal.fire({
  toast: true,
  position: 'top-end',
  icon: 'success',
  title: '<?= esc(session()->getFlashdata('success')) ?>',
  background: '#4caf50',
  color: '#fff',
  showConfirmButton: false,
  timer: 2000,
  timerProgressBar: true
});

</script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<script>
Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'error',
    title: '<?= esc(session()->getFlashdata('error')) ?>',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
</script>
<?php endif; ?>
