<meta charset="UTF-8">

<h3>
    REPORTE DE VENTAS
</h3>

<table border="0">
    <tr>

        <td><strong>Desde:</strong></td>
        <td><?= date('d/m/Y', strtotime($desde)) ?></td>

        <td><strong>Hasta:</strong></td>
        <td><?= date('d/m/Y', strtotime($hasta)) ?></td>

        <td><strong>Generado:</strong></td>
        <td><?= esc($generado_en) ?></td>

    </tr>
</table>

<br>

<style>
    td {
        white-space: nowrap;
    }
</style>

<table border="1">

    <?php
    $gt_base = 0;
    $gt_iva = 0;
    $gt_valor = 0;
    $gt_ret = 0;
    $gt_total = 0;
    ?>

    <?php if ($nivel === 'dia'): ?>

        <tr style="background:#1f4e79;color:white;font-weight:bold;">
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Total S/IVA</th>
            <th>IVA 13%</th>
            <th>Valor Venta</th>
            <th>1% Ret</th>
            <th>Total</th>
        </tr>

        <?php foreach ($reporte as $fecha => $vendedores): ?>

            <?php foreach ($vendedores as $vendedor => $data): ?>

                <?php

                $base  = $data['base'] ?? 0;
                $iva   = $data['iva'] ?? 0;
                $valor = $data['valor'] ?? 0;
                $ret   = $data['ret'] ?? 0;
                $total = $data['total'] ?? 0;

                $gt_base += $base;
                $gt_iva += $iva;
                $gt_valor += $valor;
                $gt_ret += $ret;
                $gt_total += $total;

                ?>

                <tr>

                    <td><?= date('d/m/Y', strtotime($fecha)) ?></td>
                    <td><?= esc($vendedor) ?></td>

                    <td><?= number_format($base, 2) ?></td>
                    <td><?= number_format($iva, 2) ?></td>
                    <td><?= number_format($valor, 2) ?></td>
                    <td><?= number_format($ret, 2) ?></td>
                    <td><?= number_format($total, 2) ?></td>

                </tr>

            <?php endforeach; ?>

        <?php endforeach; ?>

    <?php endif; ?>


    <?php if ($nivel === 'factura'): ?>

        <tr style="background:#1f4e79;color:white;font-weight:bold;">
            <th>Fecha</th>
            <th>Vendedor</th>
            <th>Factura</th>
            <th>Total S/IVA</th>
            <th>IVA 13%</th>
            <th>Valor Venta</th>
            <th>1% Ret</th>
            <th>Total</th>
        </tr>

        <?php foreach ($reporte as $fecha => $rows): ?>

            <?php foreach ($rows as $row): ?>

                <?php

                $base  = $row['base'];
                $iva   = $row['iva'];
                $valor = $row['valor'];
                $ret   = $row['ret'];
                $total = $row['total'];

                $gt_base += $base;
                $gt_iva += $iva;
                $gt_valor += $valor;
                $gt_ret += $ret;
                $gt_total += $total;

                ?>

                <tr>

                    <td><?= date('d/m/Y', strtotime($fecha)) ?></td>

                    <td><?= esc($row['vendedor']) ?></td>

                    <td><?= esc(substr($row['factura'], -6)) ?></td>

                    <td><?= number_format($base, 2) ?></td>
                    <td><?= number_format($iva, 2) ?></td>
                    <td><?= number_format($valor, 2) ?></td>
                    <td><?= number_format($ret, 2) ?></td>
                    <td><?= number_format($total, 2) ?></td>

                </tr>

            <?php endforeach; ?>

        <?php endforeach; ?>

    <?php endif; ?>


    <tr style="background:#c6e0b4;font-weight:bold;">

        <td colspan="<?= $nivel === 'factura' ? 3 : 2 ?>">GRAN TOTAL</td>

        <td><?= number_format($gt_base, 2) ?></td>
        <td><?= number_format($gt_iva, 2) ?></td>
        <td><?= number_format($gt_valor, 2) ?></td>
        <td><?= number_format($gt_ret, 2) ?></td>
        <td><?= number_format($gt_total, 2) ?></td>

    </tr>

</table>