<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        min-height: 40px;
        z-index: 1050;
        pointer-events: none;
    }

    .toast {
        pointer-events: auto;
        min-width: 250px;
    }

    .select2-container {
        z-index: 1060;
    }

    .toast.show {
        opacity: 1;
        transition: opacity 0.5s ease-in-out;
    }

    #payment-cart {
        position: fixed;
        bottom: 20px;
        right: 20px;
        max-width: 320px;
        z-index: 1050;
        /* menor que select2 */
    }

    .payment-cart {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 320px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
        z-index: 2000;
        overflow: hidden;
        transition: all .3s ease;
    }

    .payment-cart.minimized .cart-body {
        display: none;
    }

    .cart-header {
        background: #198754;
        color: #fff;
        padding: 14px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-total {
        font-size: 1.4rem;
        font-weight: bold;
    }

    .cart-count {
        background: #fff;
        color: #198754;
        border-radius: 50%;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .cart-body {
        max-height: 400px;
        overflow-y: auto;
    }

    .cart-footer {
        padding: 10px;
        border-top: 1px solid #eee;
    }

    .package-card {
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .package-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .15);
    }

    .package-footer {
        background: #f8f9fa;
        padding: 10px;
        border-top: 1px solid #eee;
        font-size: .9rem;
    }

    .package-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: #198754;
    }

    .package-discount {
        color: #dc3545;
        font-size: .85rem;
    }

    #cart-items .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
<link rel="stylesheet" href="<?= base_url('backend/assets/css/newpackage.css') ?>">

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class=" header-title mb-0">Registrar remuneración por tipo de cuenta</h5>
                <a href="<?= base_url('packages/return') ?>" class="btn btn-light btn-sm">Volver</a>
            </div>
            <div class="card-body">
                <form id="formPaquete" enctype="multipart/form-data">
                    <div class="row g-3 mt-1 mb-1">
                        <div class="col-md-6">
                            <label for="seller_id" class="form-label">Vendedor</label>
                            <select id="seller_id" name="seller_id" class="form-select" style="width: 100%;" required>
                                <option value=""></option>
                            </select>
                            <small class="form-text text-muted">Escribí para buscar un vendedor.</small>
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="p-2 w-50 rounded shadow-sm text-center"
                                style="background-color: #f8f9fa; border: 1px solid #dee2e6; height: 80px;">
                                <small class="text-muted d-block fw-bold">
                                    <strong>Seleccione una cuenta</strong>
                                </small>

                                <div id="account-selector">
                                    <select name="cuenta_asignada"
                                        class="form-control select2-account">
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12">
                            <input type="hidden" name="user_id" value="<?= session('id') ?>">
                            <input type="hidden" id="payment-type" value="account">
                        </div>
                    </div>

                    <div class="text-end">
                        <div id="packages-container" class="row g-3">
                            <!-- Aquí se renderizan las tarjetas -->
                        </div>
                        <hr>
                        <hr>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Floating Cart -->
<div id="payment-cart" class="payment-cart minimized">

    <!-- Header (burbuja) -->
    <div class="cart-header" id="cartToggle">
        <div>
            <strong>Total</strong>
            <div class="cart-total">$0.00</div>
        </div>
        <div class="cart-count">0</div>
    </div>

    <!-- Body (carrito expandido) -->
    <div class="cart-body">
        <ul id="cart-items" class="list-group list-group-flush"></ul>

        <div class="cart-footer">
            <button id="btnPay" class="btn btn-success w-100">
                Pagar remuneración
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        function renderCartItems() {
            const list = document.getElementById('cart-items');
            list.innerHTML = '';

            cart.items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between';

                li.innerHTML = `
                <div>
                    <strong>${item.code}</strong><br>
                    <small>$${parseFloat(item.amount).toFixed(2)}</small>
                </div>
                <button class="btn btn-sm btn-danger">
                    &times;
                </button>
            `;

                li.querySelector('button')
                    .addEventListener('click', () => removeFromCart(item.id));

                list.appendChild(li);
            });
        }

        function recalcCart() {
            cart.total = cart.items.reduce((sum, i) => sum + parseFloat(i.amount), 0);

            document.querySelector('.cart-total').innerText =
                `$${cart.total.toFixed(2)}`;

            document.querySelector('.cart-count').innerText =
                cart.items.length;

            renderCartItems();
        }

        function removeFromCart(id) {
            cart.items = cart.items.filter(i => i.id !== id);
            recalcCart();
        }

        function addToCart(pkg) {

            // evitar duplicados
            if (cart.items.find(i => i.id === pkg.id)) return;

            cart.items.push(pkg);
            recalcCart();
        }

        // Evitar que scroll cambie los números
        document.querySelectorAll('input[type=number]').forEach(input => {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            });
        });

        /* -----------------------------------------------------------
         * SELECT2 – Vendedores
         * ----------------------------------------------------------- */
        $('#seller_id').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#formPaquete'), // 👈 CLAVE
            placeholder: '🔍 Buscar vendedor...',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            language: {
                inputTooShort: function(args) {
                    let remaining = args.minimum - args.input.length;
                    return `Por favor ingrese ${remaining} caracter${remaining === 1 ? '' : 'es'} o más`;
                },
                searching: function() {
                    return "Buscando...";
                },
                noResults: function() {
                    return "No se encontraron resultados";
                }
            },
            ajax: {
                url: '<?= base_url('sellers-search') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: function(data, params) {
                    let results = data || [];
                    return {
                        results
                    };
                },
                cache: true
            }
        });
    });
</script>
<script>
    /* ==========================================================
     *  GLOBAL CART STATE
     * ========================================================== */
    let cart = {
        items: [],
        total: 0
    };

    function formatFechaSV(fecha) {
        if (!fecha) return '—';

        const date = new Date(fecha);

        return new Intl.DateTimeFormat('es-SV', {
            weekday: 'long',
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            timeZone: 'America/El_Salvador'
        }).format(date);
    }

    function renderPackages(packages) {
        const container = document.getElementById('packages-container');
        container.innerHTML = '';

        if (!packages.length) {
            container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    No hay paquetes pendientes para este vendedor.
                </div>
            </div>
        `;
            return;
        }

        packages.forEach(pkg => {

            const monto = parseFloat(pkg.monto);
            const pendiente = parseFloat(pkg.flete_pendiente || 0);
            const netAmount = monto - pendiente;

            const imageUrl = pkg.foto ?
                `/upload/paquetes/${pkg.foto}` :
                `/upload/no-image.png`;

            const col = document.createElement('div');
            col.className = 'col-md-3 mb-3';

            col.innerHTML = `
            <div class="card package-card h-100 shadow-sm">
                <div class="package-image-wrapper">
                    <img src="${imageUrl}" 
                        class="card-img-top"
                        alt="Paquete ${pkg.id}">
                </div>
                <div class="card-body">
                    <h6 class="mb-1">Paquete #${pkg.id}</h6>
                    <h6 class="mb-1">Cliente: ${pkg.cliente}</h6>
                    <h6 class="mb-1">
                        Fecha entregado: ${formatFechaSV(pkg.fecha_pack_entregado)}
                    </h6>


                    <hr>

                    <div>Valor Paq: $</div>
                    <div class="package-amount fw-bold">
                        ${monto.toFixed(2)}
                    </div>

                    ${
                        pendiente > 0
                        ? `<div class="text-danger small">
                            Descuento pendiente: -$${pendiente.toFixed(2)}
                           </div>`
                        : ''
                    }
                </div>

                <div class="package-footer d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                    <strong>Total a pagar:</strong>
                    <span class="fw-bold text-success">
                        $${netAmount.toFixed(2)}
                    </span>
                </div>

                <div class="p-2">
                    <button type="button" class="btn btn-outline-success w-100 btn-add">
                        Agregar al pago
                    </button>
            </div>
        `;

            col.querySelector('.btn-add').addEventListener('click', () => {
                addToCart({
                    id: pkg.id,
                    code: `PK-${pkg.id}`,
                    amount: netAmount,
                    photo: pkg.foto ?
                        `/upload/paquetes/${pkg.foto}` : `/upload/no-image.png`
                });
            });

            container.appendChild(col);
        });
    }


    /* ==========================================================
     *  LOAD PACKAGES BY SELLER
     * ========================================================== */
    $('#seller_id').on('change', function() {

        const sellerId = $(this).val();
        document.getElementById('packages-container').innerHTML = '';

        if (!sellerId) return;

        fetch(`<?= site_url('payments/packages-by-seller') ?>/${sellerId}`)
            .then(res => res.json())
            .then(data => {
                renderPackages(data);
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudieron cargar los paquetes', 'error');
            });
    });

    /* ==========================================================
     *  CART FUNCTIONS
     * ========================================================== */
    function renderCartItems() {
        const list = document.getElementById('cart-items');
        list.innerHTML = '';

        cart.items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item p-2';

            li.innerHTML = `
            <div class="d-flex align-items-center">
                
                <!-- FOTO -->
                <div style="
                    width:45px;
                    height:45px;
                    flex-shrink:0;
                    border-radius:6px;
                    overflow:hidden;
                    border:1px solid #ddd;
                    margin-right:10px;
                ">
                    <img src="${item.photo}"
                         style="width:100%; height:100%; object-fit:cover;">
                </div>

                <!-- INFO -->
                <div class="flex-grow-1">
                    <strong>${item.code}</strong><br>
                    <small class="text-muted">
                        $${parseFloat(item.amount).toFixed(2)}
                    </small>
                </div>

                <!-- BOTÓN -->
                <button class="btn btn-sm btn-outline-danger ms-2">
                    &times;
                </button>
            </div>
        `;

            li.querySelector('button')
                .addEventListener('click', () => removeFromCart(item.id));

            list.appendChild(li);
        });
    }

    function recalcCart() {
        cart.total = cart.items.reduce((sum, i) => sum + parseFloat(i.amount), 0);

        document.querySelector('.cart-total').innerText =
            `$${cart.total.toFixed(2)}`;

        document.querySelector('.cart-count').innerText =
            cart.items.length;

        renderCartItems();
    }

    function removeFromCart(id) {
        cart.items = cart.items.filter(i => i.id !== id);
        recalcCart();
    }

    function addToCart(pkg) {
        if (cart.items.find(i => i.id === pkg.id)) return;
        cart.items.push(pkg);
        recalcCart();
    }

    /* ==========================================================
     *  DOM READY
     * ========================================================== */
    document.addEventListener('DOMContentLoaded', async () => {

        /* ------------------------------------------------------
         *  CART TOGGLE
         * ------------------------------------------------------ */
        document.getElementById('cartToggle')
            .addEventListener('click', () => {
                document.getElementById('payment-cart')
                    .classList.toggle('minimized');
            });

        /* ------------------------------------------------------
         *  PAY BUTTON
         * ------------------------------------------------------ */
        document.getElementById('btnPay')
            .addEventListener('click', () => {

                if (!$('#seller_id').val()) {
                    Swal.fire('Atención', 'Seleccione un vendedor', 'warning');
                    return;
                }

                if (cart.items.length === 0) {
                    Swal.fire('Atención', 'No hay paquetes seleccionados', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Confirmar pago',
                    html: `
                    <strong>Total:</strong> $${cart.total.toFixed(2)}<br>
                    <strong>Paquetes:</strong> ${cart.items.length}
                `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Pagar'
                }).then(result => {

                    if (!result.isConfirmed) return;

                    const cuentaAsignada = $('.select2-account').val();

                    fetch('<?= site_url("payments/pay-seller-byaccount") ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                seller_id: $('#seller_id').val(),
                                cuenta_id: cuentaAsignada,
                                packages: cart.items
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {

                                Swal.fire('Pago realizado', '', 'success');

                                // Limpiar carrito
                                cart = {
                                    items: [],
                                    total: 0
                                };
                                recalcCart();

                                // Recargar paquetes del vendedor actual
                                const sellerId = $('#seller_id').val();

                                fetch(`<?= site_url('payments/packages-by-seller') ?>/${sellerId}`)
                                    .then(res => res.json())
                                    .then(packages => {
                                        renderPackages(packages);
                                    });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                });
            });


    });
</script>
<script>
    $(document).ready(function() {

        // Inicializar Select2 para selección de cuentas
        $('.select2-account').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cuenta...',
            allowClear: true,
            minimumInputLength: 1,
            language: {
                inputTooShort: function() {
                    return 'Ingrese 1 o más caracteres';
                }
            },
            ajax: {
                url: "<?= base_url('accounts-list') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name
                        }))
                    };
                }
            }
        });

        // 🟢 Obtener desde el servidor la cuenta con ID 1
        $.ajax({
            url: "<?= base_url('accounts-list') ?>",
            data: {
                q: "efectivo"
            }, // cualquier valor, el backend lo ignora si devuelves siempre la lista
            dataType: "json",
            success: function(data) {

                // buscar cuenta ID = 1
                const cuenta = data.find(item => item.id == 1);

                if (!cuenta) return; // si no existe, no ponemos nada

                // Colocar como selección inicial en todos los select2
                $('.select2-account').each(function() {
                    let option = new Option(cuenta.name, cuenta.id, true, true);
                    $(this).append(option).trigger('change');
                });
            }
        });

    });
</script>

<?= $this->endSection() ?>