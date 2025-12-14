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
</style>
<link rel="stylesheet" href="<?= base_url('backend/assets/css/newpackage.css') ?>">

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class=" header-title mb-0">Registrar remuneración</h5>
                <a href="<?= base_url('pending-packages') ?>" class="btn btn-light btn-sm">Volver</a>
            </div>
            <div class="card-body">
                <form id="formPaquete" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Select del vendedor -->
                        <div class="col-md-6">
                            <label for="seller_id" class="form-label">Vendedor</label>
                            <select id="seller_id" name="seller_id" class="form-select" style="width: 100%;" required>
                                <option value=""></option>
                            </select>
                            <small class="form-text text-muted">Escribí para buscar o crear un nuevo vendedor.</small>
                        </div>
                        <!-- Usuario -->
                        <div class="col-md-12">
                            <input type="hidden" name="user_id" value="<?= session('id') ?>">
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

<!-- Contenedor de toast -->
<div class="toast-container" aria-live="polite" aria-atomic="true">
    <div id="successToast" class="toast text-white bg-success" data-delay="2800">
        <div class="toast-header bg-success text-white">
            <strong class="mr-auto">Éxito</strong>
            <small>Ahora</small>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body">
            Remuneración registrada correctamente.
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

        $('#formCreateSeller').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= base_url('sellers/create-ajax') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#modalCreateSeller').modal('hide');
                        const option = new Option(response.data.text, response.data.id, true, true);
                        $('#seller_id').append(option).trigger('change');
                        Swal.fire('Éxito', 'Vendedor creado correctamente.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear.', 'error');
                    }
                },
                error: () => Swal.fire('Error', 'Error de petición.', 'error')
            });
        });

        /* -----------------------------------------------------------
         * AJAX – Envío del formulario con barra de progreso
         * ----------------------------------------------------------- */

        const form = document.getElementById("formPaquete");

        form.addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(form);

            // SweetAlert de progreso
            Swal.fire({
                title: "Subiendo paquete...",
                html: `
            <div class="progress" style="height: 22px;">
                <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%">0%</div>
            </div>
        `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            let xhr = new XMLHttpRequest();

            // Progreso de subida
            xhr.upload.addEventListener("progress", function(e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);

                    let bar = document.getElementById("uploadProgress");
                    bar.style.width = percent + "%";
                    bar.textContent = percent + "%";
                }
            });

            // Respuesta del servidor
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {

                    Swal.close();

                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);

                        if (response.status === "success") {
                            Swal.close();
                            // Redirige al mismo view pero con query param
                            window.location.href = "<?= base_url('/packages/new') ?>?created=1";

                        } else {
                            Swal.fire("Error", response.message, "error");
                        }

                    } else {
                        Swal.fire("Error", "Hubo un problema en el servidor.", "error");
                    }
                }
            };

            xhr.open("POST", "<?= base_url('packages/store') ?>");
            xhr.send(formData);
        });
    });
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('created') === '1') {
            $('#successToast').toast('show');
        }
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

                    <hr>

                    <div>Monto base: $</div>
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
                    <button class="btn btn-outline-success w-100 btn-add">
                        Agregar al pago
                    </button>
                </div>
            </div>
        `;

            col.querySelector('.btn-add').addEventListener('click', () => {
                addToCart({
                    id: pkg.id,
                    code: `PK-${pkg.id}`,
                    amount: netAmount
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
            li.className = 'list-group-item d-flex justify-content-between align-items-center';

            li.innerHTML = `
            <div>
                <strong>${item.code}</strong><br>
                <small>$${parseFloat(item.amount).toFixed(2)}</small>
            </div>
            <button class="btn btn-sm btn-danger">&times;</button>
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
         *  CASHIER SESSION CHECK
         * ------------------------------------------------------ */
        const res = await fetch('<?= site_url("cashier/session/status") ?>');
        const data = await res.json();

        if (!data.hasOpenSession) {

            const amount = parseFloat(data.initial_amount).toFixed(2);

            Swal.fire({
                title: 'Apertura de caja',
                text: `La caja se abrirá con un monto inicial de $${amount}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Iniciar turno',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false
            }).then(result => {

                if (result.isConfirmed) {
                    fetch('<?= site_url("cashier/open") ?>', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) location.reload();
                            else Swal.fire('Error', data.message, 'error');
                        });
                } else {
                    window.location.href = '<?= site_url("dashboard") ?>';
                }
            });

            return;
        }

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

                    fetch('<?= site_url("payments/pay-seller") ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                seller_id: $('#seller_id').val(),
                                packages: cart.items
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Pago realizado', '', 'success');
                                cart = {
                                    items: [],
                                    total: 0
                                };
                                recalcCart();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                });
            });

        
    });
</script>


<?= $this->endSection() ?>