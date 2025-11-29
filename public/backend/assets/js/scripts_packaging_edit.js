$(document).ready(function () {
    //Select2 Vendedor y Sucursal

    $('#vendedor').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Buscar vendedor...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: sellerSearchUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            },
            error: function (xhr) {
                console.log("ERROR AJAX:", xhr.responseText);
            }
        }
    });

    $('#branch').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Buscar sucursal...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: branchSearchUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.branch_name
                    }))
                };
            }
        }
    }).trigger('change'); // <-- Esta línea hace que Select2 lea el option inicial

    $('#punto_fijo').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Buscar punto fijo...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: puntoFijoSearchUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.point_name
                    }))
                };
            }
        }
    }).trigger('change'); // <-- Esta línea hace que Select2 lea el option inicial
});
