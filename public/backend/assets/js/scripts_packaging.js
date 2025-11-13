// Calcular flete pendiente en tiempo real
document.addEventListener('input', function () {
	const total = parseFloat(document.querySelector('[name="flete_total"]').value) || 0;
	const pagado = parseFloat(document.querySelector('[name="flete_pagado"]').value) || 0;
	document.querySelector('[name="flete_pendiente"]').value = (total - pagado).toFixed(2);
});


document.addEventListener('DOMContentLoaded', function () {
    
    // ===========================================
    // 1. Referencias de Elementos (GENERALES)
    // ===========================================

    // Elementos de Control Principal
    const tipoServicio = document.getElementById('tipo_servicio');
    const tipoEntrega = document.getElementById('tipo_entrega');

    // Contenedores
    const retiroContainer = document.getElementById('retiro_paquete_container');
    const tipoEntregaContainer = document.getElementById('tipo_entrega_container');
    const destinoContainer = document.getElementById('destino_container');
    const fechaEntregaContainer = document.getElementById('fecha_entrega_container');

    // Contenedores de Punto Fijo
    const puntoFijoSelectContainer = document.getElementById('punto_fijo_container');
    const fechaPuntoFijoContainer = document.getElementById('fecha_punto_fijo_container');

    // Campos de Input/Select
    const retiroInput = document.getElementById('retiro_paquete');
    const destinoInput = document.getElementById('destino_input');
    const puntoFijoSelect = document.getElementById('puntofijo_select');
    const fechaPuntoFijoInput = document.getElementById('fecha_entrega_puntofijo');
    const fechaEntregaOriginal = document.querySelector('[name="fecha_entrega"]'); // Campo de Fecha de Entrega general

    // ===========================================
    // 2. Funciones de Manipulación de DOM
    // ===========================================

    function mostrarCampo(el) {
        if (!el) return;
        el.querySelectorAll('input, select, textarea').forEach(field => {
            field.disabled = false;
        });
        el.style.display = 'block';
        setTimeout(() => el.classList.add('show'), 10);
    }

    function ocultarCampo(el) {
        if (!el) return;
        el.classList.remove('show');

        // Usamos un timeout para que la animación termine antes de ocultar
        setTimeout(() => {
            el.style.display = 'none';

            el.querySelectorAll('input, select, textarea').forEach(field => {
                const isSelect2 = $(field).hasClass('select2') || $(field).data('select2');

                // 1. Limpiar/Resetear valor
                if (isSelect2) {
                    $(field).val(null).trigger('change');
                } else if (field.type !== 'checkbox' && field.type !== 'radio') {
                    field.value = '';
                }

                // 2. Deshabilitar y quitar required
                field.disabled = true;
                field.required = false;
            });
        }, 300);
    }

    /** 🟢 FUNCIÓN DE LIMPIEZA: Limpia todos los contenedores condicionales. */
    function limpiarTodo() {
        ocultarCampo(puntoFijoSelectContainer);
        ocultarCampo(fechaPuntoFijoContainer);
        ocultarCampo(retiroContainer);
        ocultarCampo(tipoEntregaContainer);
        ocultarCampo(destinoContainer);
        ocultarCampo(fechaEntregaContainer);
        fechaEntregaOriginal.required = false;
    }


    // ===========================================
    // 3. Lógica Principal: Tipo de Servicio (tipo_servicio)
    // ===========================================

    function actualizarCampos(inicial = false) {
        const tipo = tipoServicio.value;

        // Si no es la carga inicial, o si se detecta un cambio, limpiamos todo.
        if (!inicial) {
            limpiarTodo();
        }

        switch (tipo) {
            case '1': // Punto fijo (Directo)
                mostrarCampo(puntoFijoSelectContainer);
                mostrarCampo(fechaPuntoFijoContainer);
                puntoFijoSelect.required = true;
                fechaPuntoFijoInput.required = true;
                break;

            case '2': // Personalizado (A domicilio)
                mostrarCampo(destinoContainer);
                mostrarCampo(fechaEntregaContainer);
                destinoInput.required = true;
                fechaEntregaOriginal.required = true;
                break;

            case '3': // Recolecta de paquete
                mostrarCampo(retiroContainer);
                retiroInput.required = true;
                mostrarCampo(tipoEntregaContainer);
                // Llama a la sub-lógica, pero también maneja su estado inicial
                // actualizarTipoEntrega(inicial); // Se maneja por el listener de tipoEntrega
                break;

            case '4': // Casillero
                // No muestra campos adicionales, solo los del casillero
                break;

            default:
                tipoEntrega.value = '';
                break;
        }
    }

    // ===========================================
    // 4. Sub-Lógica: Tipo de Entrega (tipo_entrega)
    // ===========================================
    
    function actualizarTipoEntrega(inicial = false) {
        const tipoServicioVal = tipoServicio.value;
        const tipoEntregaVal = tipoEntrega.value;

        if (tipoServicioVal !== '3') {
            // Esta lógica solo aplica si el servicio es "Recolecta de paquete" (3)
            return;
        }

        // Limpiamos los campos de destino antes de decidir cuál mostrar
        ocultarCampo(puntoFijoSelectContainer);
        ocultarCampo(fechaPuntoFijoContainer);
        ocultarCampo(destinoContainer);
        ocultarCampo(fechaEntregaContainer);

        if (tipoEntregaVal === '5') {
            // Entrega en punto fijo
            mostrarCampo(puntoFijoSelectContainer);
            mostrarCampo(fechaPuntoFijoContainer);
            puntoFijoSelect.required = true;
            fechaPuntoFijoInput.required = true;

        } else if (tipoEntregaVal === 'personalizada') {
            // Entrega personalizada
            mostrarCampo(destinoContainer);
            mostrarCampo(fechaEntregaContainer);
            destinoInput.required = true;
            fechaEntregaOriginal.required = true;
        }
    }
    
    // ===========================================
    // 5. Lógica de Fletes (PAGO PARCIAL / COMPLETO)
    // ===========================================

    const pagoParcialSwitches = document.querySelectorAll('input[name="pago_parcial"]');
    const fleteTotalInput = document.getElementById('flete_total');
    const fletePagadoContainer = document.getElementById('flete_pagado_container');
    const fletePagadoInput = document.getElementById('flete_pagado');
    const fletePendienteContainer = document.getElementById('flete_pendiente_container');
    const fletePendienteInput = document.getElementById('flete_pendiente');
    // Asumo que tienes un ID en la etiqueta <label>
    const labelFleteTotal = document.getElementById('label_flete_total'); 

    // Función para calcular Flete Pendiente
    function calculateFletePendiente() {
        // Solo calcular si estamos en modo "Pago Parcial: Sí"
        if (document.getElementById('pagoParcialSi').checked) {
            const total = parseFloat(fleteTotalInput.value) || 0;
            const pagado = parseFloat(fletePagadoInput.value) || 0;
            let pendiente = (total - pagado);
            
            // Si el pagado excede el total, el pendiente es 0.
            if (pendiente < 0) {
                pendiente = 0;
            }

            fleteTotalInput.value = total.toFixed(2);
            fletePagadoInput.value = pagado.toFixed(2);
            fletePendienteInput.value = pendiente.toFixed(2);
        }
    }

    // Función principal para controlar la interfaz de fletes
    function handlePagoParcialChange() {
        const isParcial = document.getElementById('pagoParcialSi').checked;

        if (isParcial) {
            // Modo PAGO PARCIAL: Mostrar Flete Pagado y Pendiente
            mostrarCampo(fletePagadoContainer);
            mostrarCampo(fletePendienteContainer);
            fletePagadoInput.required = true;
            
            if (labelFleteTotal) {
                labelFleteTotal.textContent = 'Total de envío a cobrar ($)';
            }
            
            // Inicializar Pagado para que el cálculo no dé un valor erróneo al inicio
            if (!fletePagadoInput.value) {
                fletePagadoInput.value = 0.00;
            }
            
        } else {
            // Modo PAGO COMPLETO: Ocultar Flete Pagado y Pendiente
            ocultarCampo(fletePagadoContainer);
            ocultarCampo(fletePendienteContainer);
            fletePagadoInput.required = false; 
            
            if (labelFleteTotal) {
                labelFleteTotal.textContent = 'Envío a pagar por el vendedor ($)';
            }
            
            // En modo pago completo, el pendiente siempre es 0
            fletePendienteInput.value = 0.00;
        }
        
        // Recalcular pendiente al cambiar el modo para reflejar el estado correcto
        calculateFletePendiente();
    }


    // ===========================================
    // 6. Listeners y Ejecución Inicial
    // ===========================================
    
    // Textarea Auto-ajustable
    retiroInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Eventos de cambio de servicio/entrega
    tipoServicio.addEventListener('change', actualizarCampos);
    tipoEntrega.addEventListener('change', actualizarTipoEntrega);
    
    // Eventos de Fletes
    pagoParcialSwitches.forEach(radio => {
        radio.addEventListener('change', handlePagoParcialChange);
    });
    fleteTotalInput.addEventListener('input', calculateFletePendiente);
    fletePagadoInput.addEventListener('input', calculateFletePendiente);
    fleteTotalInput.addEventListener('blur', calculateFletePendiente); // Para asegurar formato
    fletePagadoInput.addEventListener('blur', calculateFletePendiente); // Para asegurar formato

    // Inicialización
    limpiarTodo(); // Limpiar contenedores condicionales al inicio
    actualizarCampos(true); // Restaurar el estado de servicio/entrega
    handlePagoParcialChange(); // Restaurar el estado de fletes


    // ===========================================
    // 7. Lógica de Datepicker (Punto Fijo)
    // ===========================================

    // Nota: Usamos jQuery ($) porque daterangepicker lo requiere
    const fechaPuntoFijoInputJQ = $('#fecha_entrega_puntofijo'); 
    const diasIndices = {
        'domingo': 0, 'lunes': 1, 'martes': 2, 'miercoles': 3, 
        'jueves': 4, 'viernes': 5, 'sabado': 6
    };
    let diasPermitidos = []; 
// Función principal para manejar la visibilidad del Monto declarado
function handleMontoVisibility() {
    // 1. Obtener el valor de la píldora "Paquete ya cancelado"
    const cobroValor = document.querySelector('input[name="toggleCobro"]:checked').value;
    const esCancelado = cobroValor === '1'; // True si el paquete ya fue cancelado

    // 2. Obtener las REFERENCIAS DE ELEMENTOS (¡LA CLAVE DE LA SOLUCIÓN!)
    const montoContainerEl = document.getElementById('monto_declarado_container');
    const mensajeNoMontoContainerEl = document.getElementById('mensaje_no_monto_container');

    if (esCancelado) {
        // --- Paquete CANCELADO (Monto declarado: NO) ---
        
        // Ocultar el campo de Monto declarado y limpiar su valor
        ocultarCampo(montoContainerEl);

        // Mostrar el mensaje de No Monto
        mostrarCampo(mensajeNoMontoContainerEl); // No necesita el 'false' si tu función no lo usa

    } else {
        // --- Paquete NO CANCELADO (Monto declarado: SÍ) ---

        // Ocultar el mensaje de No Monto
        ocultarCampo(mensajeNoMontoContainerEl);

        // Mostrar el campo de Monto declarado
        mostrarCampo(montoContainerEl); // No necesita el 'false'
    }
}
    // Función para configurar el daterangepicker según los días permitidos
    function configurarDatepicker() {
        fechaPuntoFijoInputJQ.daterangepicker({
            singleDatePicker: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Seleccionar',
                cancelLabel: 'Cancelar',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
            },
            isInvalidDate: function (date) {
                // Si no hay restricciones, todo es válido
                if (diasPermitidos.length === 0) return false;
                const diaSemana = date.day(); // 0 = domingo, 6 = sábado
                return !diasPermitidos.includes(diaSemana);
            }
        });

        // Actualizar campo al elegir fecha válida
        fechaPuntoFijoInputJQ.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
    }

    // Inicializar al cargar
    configurarDatepicker();

    // --- Cuando el usuario elige un punto fijo
    puntoFijoSelect.addEventListener('change', function () {
        const id = this.value;

        if (!id) {
            diasPermitidos = [];
            fechaPuntoFijoInputJQ.val('');
            fechaPuntoFijoInputJQ.data('daterangepicker').remove(); 
            configurarDatepicker();
            return;
        }

        // Llamada AJAX (Asumo que base_url está definido o es una ruta válida)
        fetch(`<?= base_url('settledPoints/getDays/') ?>${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                diasPermitidos = Object.entries(data)
                    .filter(([_, activo]) => activo)
                    .map(([nombre]) => diasIndices[nombre]);

                fechaPuntoFijoInputJQ.val('');
                fechaPuntoFijoInputJQ.data('daterangepicker').remove();
                configurarDatepicker();

                if (diasPermitidos.length === 0) {
                    Swal.fire('Aviso', 'Este punto fijo no tiene días configurados.', 'warning');
                }
            })
            .catch(err => console.error('Error obteniendo días del punto fijo:', err));
    });

    // --- Si cambia el tipo de servicio
    tipoServicio.addEventListener('change', function () {
        const tipo = this.value;
        // Solo restauramos si se selecciona un servicio diferente a '1' (Punto Fijo)
        if (tipo !== '1') {
            diasPermitidos = [];
            fechaPuntoFijoInputJQ.val('');
            fechaPuntoFijoInputJQ.data('daterangepicker').remove();
            configurarDatepicker();
        }
    });

});


const radiosCobro = document.querySelectorAll('input[name="toggleCobro"]');
radiosCobro.forEach(radio => {
    radio.addEventListener('change', handleMontoVisibility);
});
