$(function () {
    "use strict";

    let originalButtonText;

    const openModal = $("#cashAdjustmentModal");
    const makePaymentForm = $("#cashAdjustmentForm");

    // ─────────────────────────────────────────────────────────────
    // Select2 (Register) – init on modal open; preselect on edit
    // ─────────────────────────────────────────────────────────────
    function initRegisterSelect2($select) {
        if ($select.data("select2")) return; // already initialized

        const ajaxUrl = $select.data("ajax-url"); // provided via data-ajax-url in Blade
        $select.select2({
            theme: "bootstrap-5",
            allowClear: true,
            dropdownParent: openModal,
            placeholder: $select.data("placeholder"),
            ajax: {
                url: ajaxUrl,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return { search: params.term || "", page: params.page || 1 };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { results: data.results, pagination: { more: data.hasMore } };
                },
            },
        });
    }

    function preselectRegister(data) {
        const $select = makePaymentForm.find("#register_id");
        initRegisterSelect2($select);

        if (data.register_id) {
    const text = data.register_text
        ? data.register_text.replace(/^(\d+)\s*-\s*/, '') + ` (${data.register_id})`
        : `(${data.register_id})`;
    const option = new Option(text, data.register_id, true, true);
    $select.append(option).trigger("change");
} else {
            // Clear selection if no register stored
            $select.val(null).trigger("change");
        }
    }

    // Re-init Select2 on every modal show (safe and idempotent)
    openModal.on("shown.bs.modal", function () {
        initRegisterSelect2(makePaymentForm.find("#register_id"));
    });

    // ─────────────────────────────────────────────────────────────
    // Submit logic
    // ─────────────────────────────────────────────────────────────
    makePaymentForm.on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            url: form.closest("form").attr("action"),
            formObject: form,
        };
        ajaxRequest(formArray);
    });

    function disableSubmitButton(form) {
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop("disabled", true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]').prop("disabled", false).html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) { disableSubmitButton(formObject); }
    function afterCallAjaxResponse(formObject)  { enableSubmitButton(formObject); }

    function formatNumberWithCommas(number) {
        const num = typeof number === 'string' ? parseFloat(number.replace(/,/g, '')) : number;
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateCashValues(cashIncreaseSum, totalCash) {
        const formattedCashIncrease = formatNumberWithCommas(cashIncreaseSum);
        const formattedTotalCash    = formatNumberWithCommas(totalCash);
        $(".cash-in-hand").html(formattedCashIncrease);
        $(".total-cash").html(formattedTotalCash);
    }

    function afterSeccessOfAjaxRequest(formObject, response) {
        formAdjustIfSaveOperation(formObject);
        closeModalAndAddOption(response);
        setCashInHandValue(response.cashInHand);
        updateCashValues(response.cashIncreaseSum, response.total_cash);
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
            headers: { "X-CSRF-TOKEN": formArray.csrf },
            beforeSend: function () {
                if (typeof beforeCallAjaxRequest === "function") {
                    beforeCallAjaxRequest(formArray.formObject);
                }
            },
        });
        jqxhr.done(function (response) {
            iziToast.success({ title: "Success", layout: 2, message: response.message });
            if (typeof afterSeccessOfAjaxRequest === "function") {
                afterSeccessOfAjaxRequest(formArray.formObject, response);
            }
        });
        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: "Error", layout: 2, message: message });
        });
        jqxhr.always(function () {
            if (typeof afterCallAjaxResponse === "function") {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function formAdjustIfSaveOperation(formObject) { loadDatatables(); }
    function closeModalAndAddOption(response)      { openModal.modal("hide"); }

    // ─────────────────────────────────────────────────────────────
    // Edit / New handlers (unchanged behavior except now preselecting register)
    // ─────────────────────────────────────────────────────────────
    $(document).on("click", ".make-cash-adjustment", function () {
        handleCashAdjustment();
    });

    $(document).on("click", ".edit-cash-adjustment", function () {
        var transactionId = $(this).attr("data-cash-adjustment-id");
        var url = baseURL + `/transaction/cash/adjustment/get/`;
        ajaxGetRequest(url, transactionId, "make-cash-adjustment");
    });

    function returnCashInHandValue() {
        var url = baseURL + `/transaction/get/cash-in-hand`;
        ajaxGetRequest(url, "", "get-cash-in-hand-value");
    }

    window.setCashInHandValue = function (amount = 0) {
        $(".cash-in-hand").html(_parseFix(parseFloat(amount).toFixed(2)));
    };

    function ajaxGetRequest(url, id, _from) {
        $.ajax({
            url: url + id,
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": makePaymentForm.find('input[name="_token"]').val(),
            },
            beforeSend: function () { showSpinner(); },
            success: function (response) {
                if (_from == "make-cash-adjustment") {
                    handleCashAdjustment(response.data);
                } else if (_from == "get-cash-in-hand-value") {
                    setCashInHandValue(response);
                } else {
                    //
                }
            },
            error: function (response) {
                var message = response.responseJSON.message;
                iziToast.error({ title: "Error", layout: 2, message: message });
            },
            complete: function () { hideSpinner(); },
        });
    }

    function handleCashAdjustment(data = getDefaultEmptyData()) {
        // Type
        makePaymentForm.find('select[name="adjustment_type"]').val(data.adjustment_type);

        // Date
        makePaymentForm.find('input[name="adjustment_date"]').val(data.adjustment_date);
        if (data.operation == "save") {
            makePaymentForm.find('input[name="adjustment_date"]').val(data.adjustment_date).flatpickr({
                dateFormat: dateFormatOfApp,
                defaultDate: new Date(),
            });
        }

        // Amount
        makePaymentForm.find('input[name="amount"]').val(data.amount);

        // Note
        makePaymentForm.find('textarea[name="note"]').val(data.note);

        // ID (for update)
        makePaymentForm.find('input[name="cash_adjustment_id"]').val(data.adjustment_id);

        preselectRegister(data);

        openModal.modal("show");
    }

    function getDefaultEmptyData() {
        return {
            adjustment_type: "Cash Increase",
            adjustment_date: "",
            amount: _parseFix(0),
            note: "",
            adjustment_id: "",
            operation: "save",
            // register info (none for "save")
            register_id: null,
            register_text: null,
        };
    }
});
