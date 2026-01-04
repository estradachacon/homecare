<?= $this->extend('Layouts/mainbody_welcome') ?>
<?= $this->section('content') ?>

<!-- HERO / Introducción -->
<section class="py-5 text-dark" style="background: url('<?= base_url('assets/images/quienes_somos.jpg') ?>') no-repeat center center / cover;">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Quiénes Somos</h1>
        <p class="lead mt-3" style="max-width: 700px; margin:auto;">
            En <strong>FC Encomiendas</strong> nos dedicamos a brindar un servicio de encomiendas confiable, rápido y seguro.
            Conectamos personas y negocios en todo El Salvador, superando siempre las expectativas de nuestros clientes.
        </p>
    </div>
</section>

<!-- Valores / ventajas -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Nuestros Valores</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm text-center p-3">
                    <i class="fas fa-shipping-fast fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Entrega Rápida</h5>
                    <p class="card-text">Nos aseguramos de que tus paquetes lleguen a tiempo, sin importar la distancia.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm text-center p-3">
                    <i class="fas fa-lock fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Seguridad</h5>
                    <p class="card-text">Tus encomiendas están protegidas en todo momento. Confiabilidad y cuidado garantizados.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm text-center p-3">
                    <i class="fas fa-users fa-3x mb-3 text-warning"></i>
                    <h5 class="card-title">Compromiso con el Cliente</h5>
                    <p class="card-text">Nuestra prioridad es tu satisfacción. Atención personalizada en cada envío.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Historia o Misión -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="<?= base_url('img/mision_vision_valores.jpg') ?>" class="img-fluid rounded shadow" alt="Misión y visión">
            </div>
            <div class="col-md-6">
                <h3>Nuestra Misión</h3>
                <p>Ofrecer un servicio de encomiendas que combine rapidez, seguridad y confianza, conectando personas y negocios
                    de manera eficiente y profesional.</p>
                <h3>Nuestra Visión</h3>
                <p>Ser líderes en logística de encomiendas en El Salvador, reconocidos por nuestra confiabilidad y excelencia
                    en el servicio al cliente.</p>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
