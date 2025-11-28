$(document).ready(function () {

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

});
