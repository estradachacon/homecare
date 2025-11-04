<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2); 
// CORRECCIÓN: Usamos el método correcto para el total de páginas
$currentPage = $pager->getCurrentPageNumber();
$totalPages = $pager->getPageCount();
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>" class="rounded-lg shadow-sm">
    <ul class="pagination justify-content-center m-0 p-2">

        <!-- Enlace 'Inicio' (<<) y 'Anterior' (<) -->
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&laquo;</span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </li>
        <?php endif ?>

        <!-- Enlaces Numéricos -->
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <!-- Enlace 'Siguiente' (>) y 'Fin' (>>) -->
        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
</nav>

<div class="text-center text-sm mt-2 text-muted">
    Mostrando Página <?= esc($currentPage) ?> de <?= esc($totalPages) ?>
</div>
