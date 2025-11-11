$(function () {
    "use strict";

    let originalButtonText;

    $("#registerForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);

        // Client-side validation (custom)
        const name = $.trim($("#name").val());
        const code = $.trim($("#code").val());
        const user = $.trim($("#user_id").val());
        const missing = [];

        if (!name) missing.push("Register Name");
        if (!code) missing.push("Register Code");
        if (!user) missing.push("User");

        if (missing.length > 0) {
            iziToast.error({
                title: 'Error',
                layout: 2,
                position: 'topRight',
                message: missing.join(', ') + ' field(s) are required.'
            });
            return false;
        }

        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.attr('action'),
            formObject: form,
        };
        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop('disabled', false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }

    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
    }

    function afterSuccessOfAjaxRequest(formObject) {
        formAdjustIfSaveOperation(formObject);
        pageRedirect(formObject);
    }

    function pageRedirect(formObject) {
        setTimeout(() => {
            location.href = baseURL + '/register/list';
        }, 1000);
    }

    function ajaxRequest(formArray) {
        const formData = new FormData(document.getElementById(formArray.formId));
        $.ajax({
            type: 'POST',
            url: formArray.url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': formArray.csrf },
            beforeSend: function () {
                beforeCallAjaxRequest(formArray.formObject);
            },
            success: function (data) {
                iziToast.success({ title: 'Success', layout: 2, message: data.message });
                afterSuccessOfAjaxRequest(formArray.formObject);
            },
            error: function (response) {
                if (response.status === 422 && response.responseJSON && response.responseJSON.errors) {
                    const errors = response.responseJSON.errors;
                    let msg = '';
                    Object.keys(errors).forEach(key => {
                        msg += errors[key][0] + '<br>';
                    });
                    iziToast.error({ title: 'Error', layout: 2, message: msg });
                } else {
                    iziToast.error({
                        title: 'Error',
                        layout: 2,
                        message: response.responseJSON?.message || 'An error occurred.'
                    });
                }
            },
            complete: function () {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function formAdjustIfSaveOperation(formObject) {
        if (formObject.find('input[name="_method"]').val().toUpperCase() === 'POST') {
            formObject[0].reset();
        }
    }
});

$(document).ready(function () {
    initSelect2Cashiers();
});
