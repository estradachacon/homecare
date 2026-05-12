<?php if (!empty($asientos)): ?>
    <?php foreach ($asientos as $a):
        $estadoBadge = [
            'BORRADOR'=>'warning',
            'APROBADO'=>'success text-white',
            'ANULADO'=>'danger text-white'
            ];
        $tipoBadge   = [
            'DIARIO'=>'primary text-white',
            'AJUSTE'=>'info',
            'CIERRE'=>'dark text-white',
            'APERTURA'=>'secondary text-white',
            'VENTA'=>'success text-white'];
    ?>
    <?php
        $docLinks = [
            'factura' => ['url' => 'facturas/',   'label' => 'FAC',  'badge' => 'primary'],
            'pago'    => ['url' => 'payments/',    'label' => 'PAGO', 'badge' => 'info'],
        ];
    ?>
    <tr>
        <td class="text-center fw-bold">AST-<?= str_pad($a->numero_asiento, 5, '0', STR_PAD_LEFT) ?></td>
        <td class="text-center"><?= date('d/m/Y', strtotime($a->fecha)) ?></td>
        <td>
            <?= esc(substr($a->descripcion, 0, 55)) . (strlen($a->descripcion) > 55 ? '…' : '') ?>
            <?php if (!empty($a->reversa_de)): ?>
                <span class="badge badge-danger ml-1" style="font-size:0.65rem;" title="Reversión del asiento AST-<?= str_pad($a->reversa_de, 5, '0', STR_PAD_LEFT) ?>">
                    REVERSA
                </span>
            <?php endif; ?>
            <?php if (!empty($a->documento_tipo) && !empty($a->documento_id) && isset($docLinks[$a->documento_tipo])): ?>
                <?php $dl = $docLinks[$a->documento_tipo]; ?>
                <a href="<?= base_url($dl['url'] . $a->documento_id) ?>"
                   class="badge badge-<?= $dl['badge'] ?> ml-1" style="font-size:0.65rem;"
                   title="Ver <?= $dl['label'] ?> origen" target="_blank">
                    <?= $dl['label'] ?> #<?= $a->documento_id ?>
                </a>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <?php if (!empty($a->tipo_partida_nombre)): ?>
                <span class="badge bg-<?= $tipoBadge[$a->tipo] ?? 'secondary text-white' ?>" style="font-size:0.7rem"
                      title="Tipo sistema: <?= esc($a->tipo) ?>">
                    <?= esc($a->tipo_partida_nombre) ?>
                </span>
                <?php if (!empty($a->numero_partida)): ?>
                    <span class="badge bg-light text-dark border" style="font-size:0.7rem">
                        #<?= str_pad($a->numero_partida, 4, '0', STR_PAD_LEFT) ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge bg-<?= $tipoBadge[$a->tipo] ?? 'secondary text-white' ?>" style="font-size:0.7rem"><?= $a->tipo ?></span>
            <?php endif; ?>
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
