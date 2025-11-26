$(document).on("shown.bs.modal", "[id^='reenvioModal']", function () {

    const modal = $(this);

    // --------------------------------------------------------------------
    // 🔒 Evitar que se creen listeners duplicados al abrir el modal de nuevo
    // --------------------------------------------------------------------
    modal.off("change", ".selDestino");
    modal.off("change", ".puntoSelect");

    // --------------------------------------------------------------------
    // 📌 Cambio TIPO DE DESTINO
    // --------------------------------------------------------------------
    modal.on("change", ".selDestino", function () {

        let id = $(this).data('id');
        let tipo = $(this).val();
        let fechaInput = modal.find('#fechaEntrega' + id);

        // Ocultar bloques
        modal.find('#divPunto' + id).addClass('d-none');
        modal.find('#divPersonalizado' + id).addClass('d-none');
        modal.find('#divCasillero' + id).addClass('d-none');
        modal.find('#fechaEntregaBox' + id).show();

        // Eliminar datepicker previo
        fechaInput.data('daterangepicker')?.remove();

        if (tipo === 'punto') {
            modal.find('#divPunto' + id).removeClass('d-none');
            fechaInput.attr('name', 'fecha_entrega_puntofijo');
        }

        if (tipo === 'personalizado') {
            modal.find('#divPersonalizado' + id).removeClass('d-none');
            fechaInput.attr('name', 'fecha_entrega_personalizado');

            // Si va vacío → poner fecha de hoy
            if (!fechaInput.val()) {
                fechaInput.val(moment().format('YYYY-MM-DD'));
            }

            fechaInput.daterangepicker({
                singleDatePicker: true,
                autoApply: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD',
                    firstDay: 1
                }
            });
        }

        if (tipo === 'casillero') {
            modal.find('#divCasillero' + id).removeClass('d-none');

            fechaInput.attr('name', '');
            fechaInput.val('');
            modal.find('#fechaEntregaBox' + id).hide();
        }
    });

    // --------------------------------------------------------------------
    // 📌 Cambio de PUNTO FIJO → recalcular fecha válida
    // --------------------------------------------------------------------
    modal.on("change", ".puntoSelect", function () {

        let puntoId = $(this).val();
        let paqueteId = $(this).data('id');
        let inputFecha = modal.find('#fechaEntrega' + paqueteId);

        if (!puntoId) {
            inputFecha.val('');
            return;
        }

        $.ajax({
            url: base_url + "settledPoints/getDays/" + puntoId,
            method: "GET",
            dataType: "json",
            success: function (days) {

                const allowedDays = [];
                if (days.sun) allowedDays.push(0);
                if (days.mon) allowedDays.push(1);
                if (days.tus) allowedDays.push(2);
                if (days.wen) allowedDays.push(3);
                if (days.thu) allowedDays.push(4);
                if (days.fri) allowedDays.push(5);
                if (days.sat) allowedDays.push(6);

                let nextValid = moment();
                for (let i = 0; i < 14; i++) {
                    if (allowedDays.includes(nextValid.day())) break;
                    nextValid.add(1, 'days');
                }

                // Eliminar datepicker previo
                inputFecha.data('daterangepicker')?.remove();

                inputFecha.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    startDate: nextValid,
                    autoUpdateInput: true,
                    isInvalidDate: d => !allowedDays.includes(d.day()),
                    locale: {
                        format: 'YYYY-MM-DD',
                        firstDay: 1
                    }
                });

                inputFecha.val(nextValid.format('YYYY-MM-DD'));
            }
        });
    });

    // --------------------------------------------------------------------
    // 📌 SELECT2 dentro del modal → solo una vez
    // --------------------------------------------------------------------
    modal.find('.select2punto').each(function () {

        if ($(this).hasClass("select2-hidden-accessible")) return;

        $(this).select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '🔍 Buscar punto fijo...',
            dropdownParent: modal,
            ajax: {
                url: base_url + "settledPoints/getList",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(item => ({
                        id: item.id,
                        text: item.point_name
                    }))
                })
            }
        });

    });

});
