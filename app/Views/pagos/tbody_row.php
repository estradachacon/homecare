<?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light toggleFacturas"
                                            data-id="<?= $pago->id ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>

                                    <td>
                                        <?= esc($pago->cliente_nombre ?? 'Sin cliente') ?>
                                        <div class="text-right">
                                            <small class="text-muted">
                                                Vendedor: <?= esc($pago->vendedor ?? 'Sin vendedor') ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($pago->fecha_emision)) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($pago->hora_emision)) ?>
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        $condicion = $pago->condicion_operacion ?? 1;

                                        if ($condicion == 1) {
                                            echo '<span class="badge bg-success text-white">Contado</span>';
                                        } elseif ($condicion == 2) {
                                            echo '<span class="badge bg-warning text-white">Crédito</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary text-white">N/D</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($pago->total_pagar, 2) ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($pago->saldo, 2) ?>
                                    </td>

                                    <td class="text-center">

                                        <?php if (($pago->anulada ?? 0) == 1): ?>

                                            <span class="badge bg-danger text-white">
                                                Anulado
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-success text-white">
                                                Activa
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('pagos/' . $pago->id) ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="facturas-row d-none" id="facturas-<?= $pago->id ?>">
                                    <td colspan="7" class="bg-light p-3">

                                        <div class="facturas-container small text-muted">
                                            Cargando facturas...
                                        </div>

                                    </td>
                                </tr>
                            <?php endforeach; ?>