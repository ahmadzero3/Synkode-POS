$(function () {
    "use strict";

    let originalButtonText;

    $(
        "#smtpForm, #logoForm, #generalForm, #cacheForm, #twilioForm, #vonageForm, #appLogForm"
    ).on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            url: form.closest("form").attr("action"),
            formObject: form,
        };
        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop("disabled", true)
            .html(
                '  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...'
            );
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop("disabled", false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
    }
    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
    }

    function ajaxRequest(formArray) {
        var formData = new FormData(document.getElementById(formArray.formId));
        var jqxhr = $.ajax({
            type: "POST",
            url: formArray.url,
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            beforeSend: function () {
                // Actions to be performed before sending the AJAX request
                if (typeof beforeCallAjaxRequest === "function") {
                    beforeCallAjaxRequest(formArray.formObject);
                }
            },
        });
        jqxhr.done(function (data) {
            iziToast.success({
                title: "Success",
                layout: 2,
                message: data.message,
            });
        });
        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: "Error", layout: 2, message: message });
        });
        jqxhr.always(function () {
            // Actions to be performed after the AJAX request is completed, regardless of success or failure
            if (typeof afterCallAjaxResponse === "function") {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    //First Load:
    function first_load() {
        $(".general_tab").show();
        $(
            ".logo_tab, .smtp_tab, .sms_tab, .cache_tab, .database_tab, .app_log_tab"
        ).hide();
    }
    function show_logo() {
        $(".logo_tab").show();
        $(
            ".general_tab, .smtp_tab, .sms_tab, .cache_tab, .database_tab, .app_log_tab"
        ).hide();
    }
    function show_smtp() {
        $(".smtp_tab").show();
        $(
            ".general_tab, .logo_tab, .cache_tab, .database_tab, .sms_tab, .app_log_tab"
        ).hide();
    }
    function show_sms() {
        $(".sms_tab").show();
        $(
            ".general_tab, .logo_tab, .cache_tab, .database_tab, .smtp_tab, .app_log_tab"
        ).hide();
    }
    function show_cache() {
        $(".cache_tab").show();
        $(
            ".general_tab, .smtp_tab, .sms_tab, .logo_tab, .database_tab, .app_log_tab"
        ).hide();
    }
    function show_app_log() {
        $(".app_log_tab").show();
        $(
            ".general_tab, .smtp_tab, .sms_tab, .logo_tab, .cache_tab, .database_tab"
        ).hide();
    }
    function show_database() {
        $(".database_tab").show();
        $(
            ".general_tab, .smtp_tab, .sms_tab, .cache_tab, .logo_tab, .app_log_tab"
        ).hide();
    }
    first_load();

    $(".show_general").on("click", function () {
        first_load();
    });
    $(".show_logo").on("click", function () {
        show_logo();
    });
    $(".show_smtp").on("click", function () {
        show_smtp();
    });
    $(".show_sms").on("click", function () {
        show_sms();
    });
    $(".show_cache").on("click", function () {
        show_cache();
    });
    $(".show_app_log").on("click", function () {
        show_app_log();
    });
    $(".show_database").on("click", function () {
        show_database();
    });

    function loadImageBrowser(
        uploadedImage,
        accountFileInput,
        accountImageReset
    ) {
        if (uploadedImage.length) {
            const avatarSrc = uploadedImage.attr("src");

            accountFileInput.on("change", function () {
                if (accountFileInput[0].files[0]) {
                    uploadedImage.attr(
                        "src",
                        window.URL.createObjectURL(accountFileInput[0].files[0])
                    );
                }
            });

            accountImageReset.on("click", function () {
                accountFileInput[0].value = "";
                uploadedImage.attr("src", avatarSrc);
            });
        }
    }

    $(document).ready(function () {
        loadImageBrowser(
            $("#uploaded-image-1"),
            $(".input-box-class-1"),
            $(".image-reset-class-1")
        );
        loadImageBrowser(
            $("#uploaded-image-2"),
            $(".input-box-class-2"),
            $(".image-reset-class-2")
        );
        loadImageBrowser(
            $("#uploaded-image-3"),
            $(".input-box-class-3"),
            $(".image-reset-class-3")
        );
    });
});