$(function () {
    "use strict";

    let originalButtonText;

    $("#loginForm").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.closest('form').attr('action'),
            formObject: form,
        };
        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop('disabled', true)
            .html('  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
    }
    function showRedirectMessageOnButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .html('  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Redirecting...');
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop('disabled', false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }

    function afterSeccessOfAjaxRequest(formObject, redirectUrl) {
        redirectToDashboard(formObject, redirectUrl);
    }

    function afterFailOfAjaxRequest(formObject) {
        enableSubmitButton(formObject);
    }

    function ajaxRequest(formArray) {
        var formData = new FormData(document.getElementById(formArray.formId));
        var jqxhr = $.ajax({
            type: 'POST',
            url: formArray.url,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': formArray.csrf
            },
            beforeSend: function () {
                // Actions to be performed before sending the AJAX request
                if (typeof beforeCallAjaxRequest === 'function') {
                    beforeCallAjaxRequest(formArray.formObject);
                }
            },
        });
        jqxhr.done(function (data) {
            iziToast.success({ title: 'Success', layout: 2, message: data.message });
            // Actions to be performed after response from the AJAX request
            if (typeof afterSeccessOfAjaxRequest === 'function') {
                // Use the redirect URL from server response or default to 'dashboard'
                var redirectUrl = data.redirect_url || 'dashboard';
                afterSeccessOfAjaxRequest(formArray.formObject, redirectUrl);
            }
        });
        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: 'Error', layout: 2, message: message });
            if (typeof afterFailOfAjaxRequest === 'function') {
                afterFailOfAjaxRequest(formArray.formObject);
            }
        });
        jqxhr.always(function () {
            //
        });
    }

    function formAdjustIfSaveOperation(formObject) {
        const _method = formObject.find('input[name="_method"]').val();
        /* Only if Save Operation called*/
        if (_method.toUpperCase() == 'POST') {
            var formId = formObject.attr("id");
            $("#" + formId)[0].reset();
        }
    }

    function redirectToDashboard(formObject, redirectUrl) {
        showRedirectMessageOnButton(formObject);
        setTimeout(function () {
            location.href = redirectUrl;
        }, 1000);
    }

    /**
     * Hide and Show Password
     * */
    $("#show_hide_password a").on('click', function (event) {
        show_hide_password('show_hide_password');
    });
    /**
     * Hide and show dynamic function execution
    */
    function show_hide_password(id) {
        var input = $('#' + id + ' input');
        var icon = $('#' + id + ' i');
        if (input.attr("type") === "text") {
            input.attr('type', 'password');
            icon.removeClass("bx-hide").addClass("bx-show");
        } else if (input.attr("type") === "password") {
            input.attr('type', 'text');
            icon.removeClass("bx-show").addClass("bx-hide");
        }
    }
});//main function