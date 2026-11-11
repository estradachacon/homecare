<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: .75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .chip-ne {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #f0f7ff; border: 1px solid #b6d4fe;
        border-radius: 1rem; padding: .3rem .75rem; margin: 0 .35rem .35rem 0;
        font-size: .85rem;
    }
    .chip-ne.chip-ne-cerrada { background: #fff3cd; border-color: #ffe69c; }
    .chip-ne button { border: none; background: transparent; color: #6c757d; cursor: pointer; padding: 0 0 0 .3rem; line-height: 1; }
    .chip-ne button:hover { color: #dc3545; }
    #tablaProductosNuevos td { vertical-align: middle; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">
                    Corregir vinculación con nota(s) de envío — <strong><?= esc($pedido->numero) ?></strong>
                </h4>
                <a href="<?= base_url('pedidos/' . $pedido->id) ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Cancelar
                </a>
            </div>

            <div class="card-body">

                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Esta herramienta corrige <strong>a qué nota(s) de envío pertenece</strong> esta Nota de Pedido
                    y qué productos de cada una se incluyen, reconstruyéndolos igual que al crear una NP nueva.
                    <strong>No modifica</strong> la factura
                    <?= $pedido->factura_numero ? ('Nº ' . substr($pedido->factura_numero, -6)) : '' ?> ya emitida, ni el cliente.
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Cliente</small>
                        <strong><?= esc($pedido->cliente_nombre ?? '—') ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Factura asociada</small>
                        <strong><?= $pedido->factura_numero ? ('#' . substr($pedido->factura_numero, -6)) : '—' ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Vendedor</small>
                        <strong><?= esc($pedido->vendedor_nombre ?? '—') ?></strong>
                    </div>
                </div>

                <label class="form-label fw-bold">Líneas actuales de esta NP (referencia — se reemplazan al guardar)</label>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                                <tr class="text-muted">
                                    <td><span class="badge bg-light text-dark border me-1"><?= esc($d->producto_codigo) ?></span><?= esc($d->producto_nombre) ?></td>
                                    <td class="text-end"><?= number_format($d->cantidad, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->precio_unitario, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form id="formVinculacion" method="POST"
                    action="<?= base_url('pedidos/' . $pedido->id . '/actualizar-vinculacion') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="consignacion_id" id="hiddenConsignacionId">
                    <input type="hidden" name="consignacion_ids" id="hiddenConsignacionIds">

                    <label class="form-label fw-bold">Notas de envío vinculadas</label>
                    <div id="listaNEsVinculadas" class="mb-2"></div>

                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-md-8">
                            <label class="small text-muted">Agregar otra nota de envío (por número)</label>
                            <select id="selectAgregarNE" class="form-control" style="width:100%"></select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnCargarProductosNE">
                            <i class="fa-solid fa-rotate mr-1"></i> Cargar productos de estas NE
                        </button>
                        <small class="text-muted d-block mt-1">
                            Reconstruye la tabla de abajo con los productos de las NE vinculadas — igual que al crear
                            una NP. Los que ya estén en otra NP activa aparecen deshabilitados.
                        </small>
                    </div>

                    <label class="form-label fw-bold">Productos a incluir en esta NP</label>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered" id="tablaProductosNuevos">
                            <thead class="thead-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end" style="width:140px;">Cantidad</th>
                                    <th class="text-end" style="width:110px;">Precio</th>
                                    <th class="text-end" style="width:110px;">Subtotal</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoProductos">
                                <tr id="filaVacia">
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Agregue y cargue una nota de envío para ver sus productos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning btn-lg" id="btnGuardarVinculacion">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar corrección
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<template id="tplFilaVinc">
    <tr class="fila-producto">
        <td>
            <input type="hidden" name="productos[IDX][producto_id]" class="input-producto-id">
            <input type="hidden" name="productos[IDX][consignacion_id]" class="input-consignacion-id">
            <span class="prod-chip-nombre"></span>
            <div class="ne-lotes-wrap mt-1"></div>
        </td>
        <td>
            <input type="number" name="productos[IDX][cantidad]" class="form-control form-control-sm text-end input-cantidad"
                min="0.01" step="0.01" value="1">
        </td>
        <td>
            <input type="number" name="productos[IDX][precio_unitario]" class="form-control form-control-sm text-end input-precio-value"
                min="0" step="0.01" value="0.00">
        </td>
        <td class="text-end subtotal-texto">$0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-eliminar-fila"><i class="fa-solid fa-trash"></i></button>
        </td>
    </tr>
</template>

<script>
    $(document).ready(function() {

        let filaIdx = 0;
        const PEDIDO_ID = <?= (int)$pedido->id ?>;

        // NE vinculadas actualmente (viene del servidor). Las cerradas se
        // reabrirán al guardar sin importar si se dejan o se quitan — el
        // cierre ya no refleja la realidad en cuanto cambia la vinculación.
        let nesVinculadas = <?= json_encode(array_map(fn($ne) => [
            'id'     => (int)$ne->id,
            'text'   => $ne->numero . ($ne->vendedor_nombre ? (' — ' . $ne->vendedor_nombre) : ''),
            'numero' => $ne->numero,
            'estado' => $ne->estado,
        ], $nesActuales)) ?>;

        const nesCerradasOriginal = nesVinculadas.filter(n => n.estado === 'cerrada');

        function renderNEs() {
            const cont = document.getElementById('listaNEsVinculadas');
            if (!nesVinculadas.length) {
                cont.innerHTML = '<div class="text-muted small">Ninguna — agregue al menos una.</div>';
                return;
            }
            cont.innerHTML = nesVinculadas.map(ne => {
                const claseExtra = ne.estado === 'cerrada' ? ' chip-ne-cerrada' : '';
                const icono = ne.estado === 'cerrada' ? '<i class="fa-solid fa-lock fa-xs mr-1" title="Cerrada"></i>' : '';
                return `<span class="chip-ne${claseExtra}" data-id="${ne.id}">${icono}${ne.text}` +
                    `<button type="button" data-remove="${ne.id}"><i class="fa-solid fa-xmark"></i></button></span>`;
            }).join('');

            document.getElementById('hiddenConsignacionId').value  = nesVinculadas.length ? nesVinculadas[0].id : '';
            // Como string: todo el resto del sistema guarda este JSON como un
            // arreglo de strings y lo busca con JSON_CONTAINS(..., JSON_QUOTE(?))
            // — un arreglo de números aquí nunca matchearía con eso.
            document.getElementById('hiddenConsignacionIds').value = JSON.stringify(nesVinculadas.map(n => String(n.id)));
        }

        document.getElementById('listaNEsVinculadas').addEventListener('click', function(e) {
            const btn = e.target.closest('[data-remove]');
            if (!btn) return;
            const id = parseInt(btn.dataset.remove);
            nesVinculadas = nesVinculadas.filter(n => n.id !== id);
            renderNEs();
        });

        $('#selectAgregarNE').select2({
            language: 'es',
            placeholder: 'Buscar nota de envío por número...',
            minimumInputLength: 1,
            ajax: {
                // modo=reparacion: deja ver NE cerradas / sin aprobar / sin
                // stock pendiente — necesario porque esta herramienta
                // justamente corrige hacia dónde apunta la NP.
                url: '<?= base_url('consignaciones/search-para-pedido') ?>',
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term || '', modo: 'reparacion' }),
                processResults: data => ({ results: data.results }),
            },
        }).on('select2:select', function(e) {
            const data = e.params.data;
            const id = parseInt(data.id);
            if (!nesVinculadas.some(n => n.id === id)) {
                nesVinculadas.push({ id, text: data.text, numero: data.text.split(' — ')[0], estado: 'abierta' });
                renderNEs();
            }
            $(this).val(null).trigger('change');
        });

        renderNEs();

        function calcularSubtotal(fila) {
            const cant   = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
            const precio = parseFloat(fila.querySelector('.input-precio-value').value) || 0;
            fila.querySelector('.subtotal-texto').textContent = '$' + (cant * precio).toFixed(2);
        }

        function agregarFilaDesdeNE(p, neId, neNumero) {
            const tpl  = document.getElementById('tplFilaVinc').content.cloneNode(true);
            const fila = tpl.querySelector('tr');
            const idx  = filaIdx++;
            fila.innerHTML = fila.innerHTML.replaceAll('IDX', idx);
            document.getElementById('filaVacia')?.remove();
            document.getElementById('cuerpoProductos').appendChild(fila);

            const filaEl = document.getElementById('cuerpoProductos').lastElementChild;

            filaEl.querySelector('.input-producto-id').value     = p.producto_id;
            // Se fija explícitamente de qué NE viene esta línea — así se
            // guarda tal cual se decidió aquí, sin que el motor de FIFO lo
            // recalcule después contra otras NP que compitan por la misma NE.
            filaEl.querySelector('.input-consignacion-id').value = neId;
            filaEl.querySelector('.prod-chip-nombre').innerHTML =
                `<span class="badge bg-light text-dark border me-1">${neNumero}</span>${p.producto_text}`;
            filaEl.querySelector('.input-cantidad').value     = p.cantidad > 0 ? p.cantidad : 1;
            filaEl.querySelector('.input-precio-value').value = parseFloat(p.precio_unitario || 0).toFixed(2);

            if (p.lotes && p.lotes.length > 0) {
                filaEl.querySelector('.ne-lotes-wrap').innerHTML = p.lotes.map(l => {
                    const venc = l.fecha_vencimiento ? ` &nbsp;·&nbsp; Vence: ${l.fecha_vencimiento}` : '';
                    return `<span class="badge badge-light border text-secondary small mr-1 mb-1" style="font-weight:400">` +
                        `<i class="fa-solid fa-tag fa-xs mr-1"></i>Lote: <strong>${l.numero_lote}</strong>${venc}` +
                        ` &nbsp;·&nbsp; ${parseFloat(l.cantidad).toFixed(2)} un</span>`;
                }).join('');
            }

            calcularSubtotal(filaEl);
            filaEl.querySelector('.input-cantidad').addEventListener('input', () => calcularSubtotal(filaEl));
            filaEl.querySelector('.input-precio-value').addEventListener('input', () => calcularSubtotal(filaEl));
            filaEl.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
                filaEl.remove();
                if (!document.querySelectorAll('.fila-producto').length) {
                    document.getElementById('cuerpoProductos').innerHTML =
                        '<tr id="filaVacia"><td colspan="5" class="text-center text-muted py-3">Sin productos — cargue una NE.</td></tr>';
                }
            });

            // Agotado: ya se pidió todo lo disponible de este producto en esta
            // NE en otra Nota de Pedido activa. Se muestra muteado en vez de
            // ocultarlo, para que se vea por qué no se puede volver a pedir.
            if (p.agotado) {
                filaEl.classList.add('text-muted', 'bg-light');
                filaEl.querySelectorAll('input').forEach(el => el.disabled = true);
                filaEl.querySelector('.prod-chip-nombre').insertAdjacentHTML('beforeend',
                    ' <span class="badge badge-secondary ml-1"><i class="fa-solid fa-lock fa-xs mr-1"></i>Ya en otra NP</span>');
            }
        }

        document.getElementById('btnCargarProductosNE').addEventListener('click', async function() {
            if (!nesVinculadas.length) {
                Swal.fire('Falta información', 'Agregue al menos una nota de envío primero.', 'warning');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            document.getElementById('cuerpoProductos').innerHTML = '';
            filaIdx = 0;

            for (const ne of nesVinculadas) {
                try {
                    const url = `<?= base_url('consignaciones/productos-para-pedido') ?>/${ne.id}` +
                        `?modo=reparacion&excluir_pedido_id=${PEDIDO_ID}`;
                    const res = await fetch(url).then(r => r.json());

                    if (!res.success) {
                        Swal.fire('Error', `${ne.text}: ${res.message || 'No se pudo cargar.'}`, 'error');
                        continue;
                    }

                    res.productos.forEach(p => agregarFilaDesdeNE(p, ne.id, res.numero));
                } catch {
                    Swal.fire('Error', `Error de conexión al cargar ${ne.text}.`, 'error');
                }
            }

            if (!document.querySelectorAll('.fila-producto').length) {
                document.getElementById('cuerpoProductos').innerHTML =
                    '<tr id="filaVacia"><td colspan="5" class="text-center text-muted py-3">Sin productos en las NE seleccionadas.</td></tr>';
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-rotate mr-1"></i> Cargar productos de estas NE';
        });

        $('#formVinculacion').on('submit', function(e) {
            e.preventDefault();

            if (!nesVinculadas.length) {
                Swal.fire('Falta información', 'Debe quedar al menos una nota de envío vinculada.', 'warning');
                return;
            }
            if (!document.querySelectorAll('.fila-producto:not(.text-muted)').length) {
                Swal.fire('Falta información', 'Debe incluir al menos un producto (los muteados no cuentan).', 'warning');
                return;
            }

            let html = '<p>¿Confirma guardar esta corrección de vinculación?</p>';
            if (nesCerradasOriginal.length > 0) {
                const nombres = nesCerradasOriginal.map(n => n.text).join(', ');
                html += `<div class="alert alert-warning mt-2" style="text-align:left;font-size:13px;">
                    Esto reabrirá la${nesCerradasOriginal.length > 1 ? 's' : ''} siguiente${nesCerradasOriginal.length > 1 ? 's' : ''}
                    nota${nesCerradasOriginal.length > 1 ? 's' : ''} de envío para que se pueda${nesCerradasOriginal.length > 1 ? 'n' : ''}
                    volver a cerrar: <strong>${nombres}</strong>.
                </div>`;
            }

            Swal.fire({
                title: 'Confirmar corrección',
                html: html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107',
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('formVinculacion').submit();
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>
