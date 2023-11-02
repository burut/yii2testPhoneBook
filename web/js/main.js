$(document).ready(function() {
    let loader = $('.js-response-loader'),
        editModal = $('#edit-person'),
        deleteModal = $('#delete-person'),
        now = new Date()
    ;

    $('.js-datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        endDate: new Date(now.getFullYear() - 18, now.getMonth(), now.getDay()),
    });

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

                editModal.find('.js-id').val(response.id);
                editModal.find('.js-firstname').val(response.firstname);
                editModal.find('.js-lastname').val(response.lastname);
                editModal.find('.js-email').val(response.email);
                editModal.find('.js-birthday').val(response.birthday);
                fillPhoneNumbers(response.phones)
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

    $('.js-update').on('click', function () {
        if (validateFirstname() === false || validateEmail() === false || validatePhoneNumbers() === false) {
            return false;
        }

        loader.show();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {
                form: $('.js-edit-person').serializeObject(),
            },
            url: 'http://yii2test.lan/index.php?r=test/person-data-save',
            success: function (response) {
                if (response.status) {
                    location.reload();
                } else {
                    alert(response.text_message ? response.text_message : ':( Something went wrong!')
                    loader.hide();
                    errorHandler(response);
                }
            }
        });
    });

    $(document).on('click', '.js-remove-phonenumber', function () {
        let $elem = $(this).prev();
        $elem.parent().remove();
    });

    $(document).on('keyup', 'input[name="phonenumber[]"]', function () {
        validatePhoneNumber($(this));
    });
    $(document).bind('paste', 'input[name="phonenumber[]"]', function () {
        validatePhoneNumber($(this));
    });
    $('.js-firstname').on('keyup', function () {
        validateFirstname();
    });
    $('.js-email').on('keyup', function () {
        validateEmail();
    });

    $('#add-another-phonenumber').on('click', function () {
        addNewPhonenumberField();
    });

    function validatePhoneNumber($this) {
        $this.val($this.val().replace(/[^+0-9]/g, ''));
        match = $this.val().match(/[0-9]/g);
        length = match ? match.length : 0;
        if (length < 10 || length > 12) {
            $this.addClass('alert-field');

            return false;
        }
        $this.removeClass('alert-field');
        return true;
    }
    function validatePhoneNumbers() {
        elements = $(document).find('input[name="phonenumber[]"]');
        for (i = 0; i <= (elements.length - 1); i++) {
            let $this = $(elements[i]);
            if (!$this.prop("disabled")) {
                if (validatePhoneNumber($this) === false) {
                    return false;
                }
            }
        }
    }
    function validateEmail() {
        reexp = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
        if (!reexp.test($('.js-email').val())) {
            $('.js-email').addClass('alert-field');

            return false;
        }
        $('.js-email').removeClass('alert-field');
    }
    function validateFirstname() {
        val = $('.js-firstname').val();
        if (!val.trim()) {
            $('.js-firstname').val('');
            $('.js-firstname').addClass('alert-field');

            return false;
        }
        $('.js-firstname').removeClass('alert-field');
    }
    function addNewPhonenumberField() {
        let $template = $('#phonenumber-field-template'),
            $clone = $template.clone(),
            $field = $clone.find('[name="phonenumber[]"]')
        ;

        $clone.removeAttr('id')
            .removeClass('hide')
            .addClass('cloned-phonenumber')
        ;

        $field.val('').removeAttr("disabled");
        $template.before($clone);

        return $field;
    }
    function errorHandler(response) {
        if (response.too_young) {
            $('.js-birthday').addClass('alert-field');
        }
        if (response.empty_firstname) {
            $('.js-firstname').addClass('alert-field');
        }
        if (response.invalid_email) {
            $('.js-email').addClass('alert-field');
        }
        if (response.empty_numbers) {
            $('.js-phonenumber').addClass('alert-field');
        }

        if (response.duplicate_phone) {
            $.each($(document).find('input[name="phonenumber[]"]'), function (k, e) {
                if ($(e).val().trim() === response.duplicate_phone) {
                    $(e).addClass('alert-field');
                }
            });
        }
    }
    function fillPhoneNumbers(phones) {
        $(document).find('.cloned-phonenumber').remove();

        $.each(phones, function (k, phone) {
            if (k == 0) {
                editModal.find('.js-phonenumber').val(phone.number)
            } else {
                field = addNewPhonenumberField();
                $(field).val(phone.number);
            }
        });
    }
    function clearForm() {
        editModal.find('.js-id').val('');
        editModal.find('.js-firstname').val('');
        editModal.find('.js-lastname').val('');
        editModal.find('.js-email').val('');
        editModal.find('.js-birthday').val('');
        editModal.find('.js-phonenumber').val('')
        $(document).find('.cloned-phonenumber').remove();
    }
});
