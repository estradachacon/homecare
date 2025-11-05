<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h4 class="mb-3">Nuevo Vendedor</h4>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('sellers') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="seller" class="form-label">Nombre del Vendedor</label>
            <input type="text" name="seller" id="seller" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tel_seller" class="form-label">Teléfono</label>
            <input type="text" name="tel_seller" id="tel_seller" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="<?= base_url('sellers') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?= $this->endSection() ?>
