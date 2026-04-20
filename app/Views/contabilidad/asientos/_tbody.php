<?php if (!empty($asientos)): ?>
    <?php foreach ($asientos as $a):
        $estadoBadge = ['BORRADOR'=>'warning','APROBADO'=>'success text-white','ANULADO'=>'danger text-white'];
        $tipoBadge   = ['DIARIO'=>'primary text-white','AJUSTE'=>'info','CIERRE'=>'dark text-white','APERTURA'=>'secondary text-white'];
    ?>
    <tr>
        <td class="text-center fw-bold">AST-<?= str_pad($a->numero_asiento, 5, '0', STR_PAD_LEFT) ?></td>
        <td class="text-center"><?= date('d/m/Y', strtotime($a->fecha)) ?></td>
        <td><?= esc(substr($a->descripcion, 0, 60)) . (strlen($a->descripcion) > 60 ? '...' : '') ?></td>
        <td class="text-center">
            <span class="badge bg-<?= $tipoBadge[$a->tipo] ?? 'secondary' ?>" style="font-size:0.7rem"><?= $a->tipo ?></span>
        </td>
        <td class="text-center">
            <span class="badge bg-<?= $estadoBadge[$a->estado] ?? 'secondary' ?>"><?= $a->estado ?></span>
        </td>
        <td class="text-end">$ <?= number_format($a->total_debe, 2) ?></td>
        <td class="text-end">$ <?= number_format($a->total_haber, 2) ?></td>
        <td class="text-center">
            <a href="<?= base_url('contabilidad/asientos/' . $a->id) ?>" class="btn btn-sm btn-outline-info py-0 px-1">
                <i class="fa-solid fa-eye"></i>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="8" class="text-center text-muted py-3">No hay asientos registrados</td></tr>
<?php endif; ?>
