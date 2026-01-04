<?= $this->extend('Layouts/mainbody_welcome') ?>
<?= $this->section('content') ?>
<div class="heroe bienvenida-encomiendas">
    <h1>¡Bienvenido a tu aliado logístico! 🚀</h1>
    <h2>Servicios de Encomienda Rápidos y Seguros</h2>
    <p>Estamos comprometidos a conectar tus envíos con sus destinos de manera eficiente y confiable.</p>
</div>
</header>

<section class="py-5">
    <div class="container">
        <?php if ($images): ?>
            <div id="welcomeCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= base_url('upload/content/' . $img->image) ?>"
                                class="d-block w-100"
                                style="object-fit: contain; max-height: 400px;"
                                alt="<?= esc($img->caption) ?>">
                            <?php if ($img->caption): ?>
                                <div class="carousel-caption d-none d-md-block">
                                    <h5><?= esc($img->caption) ?></h5>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#welcomeCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#welcomeCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        <?php else: ?>
            <p class="text-center">No hay imágenes para mostrar en el carrusel.</p>
        <?php endif; ?>
    </div>
</section>

<div class="further">
    <section class="social-cards">
        <h1>Nuestras redes sociales</h1>

        <div class="social-grid">
            <a href="https://www.facebook.com/fc.encomiendas.2025"
                target="_blank" class="social-card facebook">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
            </a>

            <a href="https://www.instagram.com/fc_encomiendas/"
                target="_blank" class="social-card instagram">
                <i class="fab fa-instagram"></i>
                <span>Instagram</span>
            </a>

            <a href="https://www.tiktok.com/@fcencomiendas"
                target="_blank" class="social-card tiktok">
                <i class="fab fa-tiktok"></i>
                <span>TikTok</span>
            </a>
        </div>
    </section>

</div>
<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->
<footer class="main-footer">
    <div class="environment">
        <h2>Nuestra Visión</h2>
        <p>
            En <strong>FC Encomiendas</strong> aspiramos a ser la empresa líder en servicios de encomiendas en El
            Salvador,
            brindando entregas rápidas, seguras y confiables. Buscamos conectar a personas y negocios con
            eficiencia,
            compromiso y confianza, superando siempre las expectativas de nuestros clientes.
        </p>
    </div>
    <div class="copyrights">
        <p>&reg; <?= date('Y') ?> FC Encomiendas.</p>
    </div>
</footer>
<?= $this->endSection() ?>