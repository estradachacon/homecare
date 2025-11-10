<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
            <h1 class="header-title">Bienvenido, <?= esc(session('user_name')) ?> 👋</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 mb-2"></div>
                <h5>Selecciona una opción del menú lateral para comenzar.</h5>
            </div>
        </div>
    </div>
</div>
    <?= $this->endSection() ?>