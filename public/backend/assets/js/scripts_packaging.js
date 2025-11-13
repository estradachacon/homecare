// Calcular flete pendiente en tiempo real
document.addEventListener('input', function () {
	const total = parseFloat(document.querySelector('[name="flete_total"]').value) || 0;
	const pagado = parseFloat(document.querySelector('[name="flete_pagado"]').value) || 0;
	document.querySelector('[name="flete_pendiente"]').value = (total - pagado).toFixed(2);
});


document.addEventListener('DOMContentLoaded', function () {
	// --- 1. Referencias de Elementos ---

	// Elementos de Control Principal
	const tipoServicio = document.getElementById('tipo_servicio');
	const tipoEntrega = document.getElementById('tipo_entrega');

	// Contenedores
	const retiroContainer = document.getElementById('retiro_paquete_container');
	const tipoEntregaContainer = document.getElementById('tipo_entrega_container');
	const destinoContainer = document.getElementById('destino_container');

	// Contenedores de Punto Fijo
	const puntoFijoSelectContainer = document.getElementById('punto_fijo_container');
	const fechaPuntoFijoContainer = document.getElementById('fecha_punto_fijo_container');

	// Campos de Input/Select
	const retiroInput = document.getElementById('retiro_paquete');
	const destinoInput = document.getElementById('destino_input');
	const puntoFijoSelect = document.getElementById('puntofijo_select');
	const fechaPuntoFijoInput = document.getElementById('fecha_entrega_puntofijo');
	const fechaEntregaContainer = document.getElementById('fecha_entrega_container');


	// Campo de Fecha de Entrega general
	const fechaEntregaOriginal = document.querySelector('[name="fecha_entrega"]');

	// --- 2. Funciones de Manipulación de DOM ---

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

	/** * 🟢 NUEVA FUNCIÓN DE LIMPIEZA: Limpia todos los contenedores condicionales. 
	 * Se llama solo al inicio o cuando se requiere una limpieza profunda.
	 */
	function limpiarTodo() {
		ocultarCampo(puntoFijoSelectContainer);
		ocultarCampo(fechaPuntoFijoContainer);
		ocultarCampo(retiroContainer);
		ocultarCampo(tipoEntregaContainer);
		ocultarCampo(destinoContainer);
		ocultarCampo(fechaEntregaContainer);
		fechaEntregaOriginal.required = false;
	}


	// --- 3. Lógica Principal: Tipo de Servicio (tipo_servicio) ---
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
				ocultarCampo(retiroContainer);
				ocultarCampo(destinoContainer);
				ocultarCampo(tipoEntregaContainer);
				ocultarCampo(fechaEntregaContainer);
				puntoFijoSelect.required = true;
				fechaPuntoFijoInput.required = true;
				break;

			case '2': // Personalizado (A domicilio)
				mostrarCampo(destinoContainer);
				mostrarCampo(fechaEntregaContainer);
				ocultarCampo(puntoFijoSelectContainer);
				ocultarCampo(tipoEntregaContainer);
				ocultarCampo(retiroContainer);
				ocultarCampo(fechaPuntoFijoContainer);
				destinoInput.required = true;
				fechaEntregaOriginal.required = true;
				break;

			case '3': // Recolecta de paquete
				mostrarCampo(retiroContainer);
				retiroInput.required = true;
				mostrarCampo(tipoEntregaContainer);
				ocultarCampo(destinoContainer);
				ocultarCampo(puntoFijoSelectContainer);
				ocultarCampo(fechaPuntoFijoContainer);
				// Llama a la sub-lógica, pero también maneja su estado inicial
				//actualizarTipoEntrega(inicial); 
				break;

			case '4': // Casillero
				ocultarCampo(destinoContainer);
				ocultarCampo(puntoFijoSelectContainer);
				ocultarCampo(tipoEntregaContainer);
				ocultarCampo(retiroContainer);
				break;

			default:
				tipoEntrega.value = '';
				break;
		}
	}

	// --- 4. Sub-Lógica: Tipo de Entrega (tipo_entrega) ---
	function actualizarTipoEntrega(inicial = false) {
		const tipoServicioVal = tipoServicio.value;
		const tipoEntregaVal = tipoEntrega.value;

		if (tipoServicioVal !== '3') {
			return;
		}

		// Si el servicio es '3', pero solo cambiamos la sub-opción de entrega, limpiamos los destinos
		if (!inicial) {
			ocultarCampo(puntoFijoSelectContainer);
			ocultarCampo(fechaPuntoFijoContainer);
			ocultarCampo(destinoContainer);
			fechaEntregaOriginal.required = false;
		}

		if (tipoEntregaVal === '5') {
			// Entrega en punto fijo
			mostrarCampo(puntoFijoSelectContainer);
			mostrarCampo(fechaPuntoFijoContainer);
			ocultarCampo(destinoContainer);
			ocultarCampo(fechaEntregaContainer);
			puntoFijoSelect.required = true;
			fechaPuntoFijoInput.required = true;

		} else if (tipoEntregaVal === 'personalizada') {
			// Entrega personalizada
			mostrarCampo(destinoContainer);
			mostrarCampo(fechaEntregaContainer);
			ocultarCampo(puntoFijoSelectContainer);
			ocultarCampo(fechaPuntoFijoContainer);
			destinoInput.required = true;
			fechaEntregaOriginal.required = true;
		}
		else if (tipoEntregaVal === '') {
			// Entrega personalizada
			ocultarCampo(destinoContainer);
			ocultarCampo(puntoFijoSelectContainer);
			ocultarCampo(fechaPuntoFijoContainer);
			ocultarCampo(fechaEntregaContainer);
			destinoInput.required = true;
			fechaEntregaOriginal.required = true;
		}
	}

	// --- 5. Listeners y Ejecución Inicial ---

	retiroInput.addEventListener('input', function () {
		this.style.height = 'auto';
		this.style.height = this.scrollHeight + 'px';
	});

	// Eventos de cambio
	tipoServicio.addEventListener('change', actualizarCampos);
	tipoEntrega.addEventListener('change', actualizarTipoEntrega);

	// 2. Ejecutar la lógica para mostrar el estado actual (si el campo tiene un valor preseleccionado).
	// Le pasamos 'true' para indicar que es la carga inicial y evitar la doble limpieza.
	actualizarCampos(true);

	// --- 5. Lógica de Fletes ---
	const fleteTotal = document.getElementById('flete_total');
	const fletePagado = document.getElementById('flete_pagado');
	const fletePendiente = document.getElementById('flete_pendiente');

	function actualizarPendiente() {
		let total = parseFloat(fleteTotal.value) || 0;
		let pagado = parseFloat(fletePagado.value) || 0;
		let pendiente = total - pagado;

		if (pendiente < 0) pendiente = 0;

		fleteTotal.value = total.toFixed(2);
		fletePagado.value = pagado.toFixed(2);
		fletePendiente.value = pendiente.toFixed(2);
	}

	[fleteTotal, fletePagado].forEach(field => {
		field.addEventListener('change', actualizarPendiente);
		field.addEventListener('blur', actualizarPendiente);
	});
	// 🟢 INICIALIZACIÓN: 
	// 1. Limpiar todos los campos *sin animación* al cargar.
	limpiarTodo();
});


document.addEventListener('DOMContentLoaded', function () {
	const tipoServicioSelect = document.getElementById('tipo_servicio');
	const puntoFijoSelect = document.getElementById('puntofijo_select');
	const fechaPuntoFijoInput = $('#fecha_entrega_puntofijo'); // Usamos jQuery porque daterangepicker lo necesita

	let diasPermitidos = []; // ejemplo [1, 4] = lunes y jueves
	const diasIndices = {
		'domingo': 0,
		'lunes': 1,
		'martes': 2,
		'miercoles': 3,
		'jueves': 4,
		'viernes': 5,
		'sabado': 6
	};

	// --- Función para configurar el daterangepicker según los días permitidos
	function configurarDatepicker() {
		fechaPuntoFijoInput.daterangepicker({
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
		fechaPuntoFijoInput.on('apply.daterangepicker', function (ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD'));
		});
	}

	// Inicializar al cargar (por si no hay restricciones)
	configurarDatepicker();

	// --- Cuando el usuario elige un punto fijo
	puntoFijoSelect.addEventListener('change', function () {
		const id = this.value;

		if (!id) {
			diasPermitidos = [];
			fechaPuntoFijoInput.val('');
			fechaPuntoFijoInput.data('daterangepicker').remove(); // eliminar actual
			configurarDatepicker(); // reactivar sin restricciones
			return;
		}

		fetch(`<?= base_url('settledPoints/getDays/') ?>${id}`, {
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(res => res.json())
			.then(data => {
				diasPermitidos = Object.entries(data)
					.filter(([_, activo]) => activo)
					.map(([nombre]) => diasIndices[nombre]);

				fechaPuntoFijoInput.val('');
				fechaPuntoFijoInput.data('daterangepicker').remove(); // eliminar instancia anterior
				configurarDatepicker();

				if (diasPermitidos.length === 0) {
					Swal.fire('Aviso', 'Este punto fijo no tiene días configurados.', 'warning');
				}
			})
			.catch(err => console.error('Error obteniendo días del punto fijo:', err));
	});

	// --- Si cambia el tipo de servicio
	tipoServicioSelect.addEventListener('change', function () {
		const tipo = this.value;
		if (tipo !== 'punto_fijo') {
			// Si no es punto fijo → sin restricciones
			diasPermitidos = [];
			fechaPuntoFijoInput.val('');
			fechaPuntoFijoInput.data('daterangepicker').remove();
			configurarDatepicker();
		}
	});
});
