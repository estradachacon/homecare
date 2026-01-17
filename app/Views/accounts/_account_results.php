<div class="d-none d-md-block">
    <?= view('accounts/_account_table', [
        'accounts' => $accounts,
        'pager'   => $pager,
        'q'       => $q
    ]) ?>
</div>

<div class="d-block d-md-none">
    <?= view('accounts/_account_cards', [
        'accounts' => $accounts,
        'pager'   => $pager,
        'q'       => $q
    ]) ?>
</div>
