$(document).ready(function() {
    let loader = $('.js-response-loader'),
        editModal = $('#edit-person'),
        deleteModal = $('#delete-person'),
        now = new Date()
    ;

    $('input[name="Person[birthday]"]').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        endDate: new Date(now.getFullYear() - 18, now.getMonth(), now.getDay()),
    });

    function initPhonenumbers(phones) {
        $('#phone-number option').remove();
        $('#phone-number').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            minimumResultsForSearch: -1,
            createTag: function (params) {
                val = validatePhoneNumber(params.term);
                if (!val) {
                    return null;
                }
                return {
                    id: val,
                    text: val
                }
            },
        });

        $(phones).each(function (e, v) {
            newOption = new Option(v.text, v.id, true, true);
            $('#phone-number').append(newOption).trigger('change');
        });
    }

    function validatePhoneNumber(value) {
        value = value.trim().replace(/[^+0-9]/g, '');
        let match = value.match(/[0-9]/g),
            matchPlus = value.match(/^\+/g),
            length = match ? match.length : 0
        ;

        if ((!matchPlus && length === 10) || (matchPlus && length === 12)) {
            return value
        }

        return false;
    }

    $('.js-datatable').DataTable({
        'pageLength': 500,
        'ordering'  : true,
        'searching' : true,
        'paging'    : false,
        'info'      : false,
        'order'     : [[0, 'asc']],
        'columnDefs': [
            {
                'targets': [5],
                'visible': false
            },
            {
                'targets': [6,7],
                'orderable': false,
            }
        ],
    });

    $('.js-add-new-person').on('click', function () {
        editModal.modal().show();
        clearForm();
    });

    $('.js-edit').on('click', function () {
        loader.show();
        clearForm();
        let id = $(this).data('id');

        $.ajax({
            type: 'GET',
            dataType: 'json',
            data: {
                id: id,
            },
            url: 'http://yii2test.lan/index.php?r=test/person-data-load',
            success: function (response) {
                loader.hide();
                if (!response.id) {
                    return false;
                }
                editModal.modal().show();

                $('input[name="Person[id]"]').val(response.id);
                $('input[name="Person[firstname]"]').val(response.firstname);
                $('input[name="Person[lastname]"]').val(response.lastname);
                $('input[name="Person[email]"]').val(response.email);
                $('input[name="Person[birthday]"]').val(response.birthday);
                initPhonenumbers(response.phones);
            }
        });
    });

    $('.js-delete').on('click', function () {
        $('.js-delete-person').data('id', $(this).data('id'));
        deleteModal.modal().show();
    });

    $('.js-delete-person').on('click', function () {
        loader.show();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {
                id: $('.js-delete-person').data('id'),
            },
            url: 'http://yii2test.lan/index.php?r=test/person-data-delete',
            success: function (response) {
                location.reload();
            }
        });
    });

    function clearForm() {
        $('input[name="Person[id]"]').val('');
        $('input[name="Person[firstname]"]').val('');
        $('input[name="Person[lastname]"]').val('');
        $('input[name="Person[email]"]').val('');
        $('input[name="Person[birthday]"]').val('');
        initPhonenumbers([]);
    }
});
