<?php $pager->setSurroundCount(2); ?>

<nav>
<ul class="pagination justify-content-center">

<?php if ($pager->hasPrevious()) : ?>
<li class="page-item">
    <a class="page-link" href="<?= $pager->getFirst() ?>">«</a>
</li>
<li class="page-item">
    <a class="page-link" href="<?= $pager->getPrevious() ?>">‹</a>
</li>
<?php endif ?>

<?php foreach ($pager->links() as $link): ?>
<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
    <a class="page-link" href="<?= $link['uri'] ?>">
        <?= $link['title'] ?>
    </a>
</li>
<?php endforeach ?>

<?php if ($pager->hasNext()) : ?>
<li class="page-item">
    <a class="page-link" href="<?= $pager->getNext() ?>">›</a>
</li>
<li class="page-item">
    <a class="page-link" href="<?= $pager->getLast() ?>">»</a>
</li>
<?php endif ?>

</ul>
</nav>