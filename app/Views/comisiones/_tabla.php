<?php if (!empty($comisiones)): ?>
    <?php foreach ($comisiones as $c): ?>
        <tr>

            <td>
                <strong>#<?= $c->id ?></strong>
            </td>

            <td>
                <?= esc($c->vendedor_nombre ?? 'N/A') ?>
            </td>

            <td class="text-center rango-fecha">
                <?= date('d/m/Y', strtotime($c->fecha_inicio)) ?>
                <span class="text-muted mx-1">→</span>
                <?= date('d/m/Y', strtotime($c->fecha_fin)) ?>
            </td>

            <td class="text-end">
                $ <?= number_format($c->total_ventas, 2) ?>
            </td>

            <td class="text-end text-success">
                <strong>$ <?= number_format($c->total_comision, 2) ?></strong>
            </td>

            <td class="text-center">
                <span class="badge badge-info">
                    <?= number_format($c->porcentaje_promedio, 2) ?>%
                </span>
            </td>

            <td class="text-center">
                <?php if ($c->estado === 'generado'): ?>
                    <span class="badge badge-success">Generado</span>
                <?php else: ?>
                    <span class="badge badge-warning">Pendiente</span>
                <?php endif; ?>
            </td>

            <td class="text-center">
                <a href="<?= base_url('comisiones/' . $c->id) ?>"
                    class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i>
                </a>
            </td>

        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="8" class="text-center">
            No hay comisiones registradas
        </td>
    </tr>
<?php endif; ?>