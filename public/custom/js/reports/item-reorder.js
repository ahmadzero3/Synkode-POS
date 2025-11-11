$(function () {
    "use strict";

    let originalButtonText;

    const tableId = $('#generalReport');

    let taxSelectionBox = $('select[name="customer_id"]')

    /**
     * Language
     * */
    const _lang = {
        total: "Total",
        noRecordsFound: "No Records Found !!",
    };

    $("#reportForm").on("submit", function (e) {
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

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop('disabled', false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
        showSpinner();
    }
    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
        hideSpinner();
    }
    function afterSeccessOfAjaxRequest(formObject, response) {
        formAdjustIfSaveOperation(response);
    }
    function afterFailOfAjaxRequest(formObject) {
        showNoRecordsMessageOnTableBody();
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
        jqxhr.done(function (response) {
            // Actions to be performed after response from the AJAX request
            if (typeof afterSeccessOfAjaxRequest === 'function') {
                afterSeccessOfAjaxRequest(formArray.formObject, response);
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
            // Actions to be performed after the AJAX request is completed, regardless of success or failure
            if (typeof afterCallAjaxResponse === 'function') {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function formAdjustIfSaveOperation(response) {
        var tableBody = tableId.find('tbody');
        tableBody.empty();

        var id = 1;
        var totalQuantity = 0;
        var tr = "";

        var hasRows = (response && Array.isArray(response.data) && response.data.length > 0);

        if (hasRows) {
            $.each(response.data, function (index, item) {
                totalQuantity += parseFloat(item.quantity || 0);

                tr += "<tr>";
                tr += "<td>" + (id++) + "</td>";
                tr += "<td>" + (item.item_name ?? '') + "</td>";
                tr += "<td>" + (item.brand_name ?? '') + "</td>";
                tr += "<td>" + (item.category_name ?? '') + "</td>";
                tr += "<td class='text-end'>" + _parseQuantity(item.min_stock ?? 0) + "</td>";
                tr += "<td class='text-end'>" + _parseQuantity(item.quantity ?? 0) + "</td>";
                tr += "<td>" + (item.unit_name ?? '') + "</td>";
                tr += "</tr>";
            });
        } else {
            // Show toast (so you still see the error message) and the default row in the table
            iziToast.error({ title: 'Error', layout: 2, message: _lang.noRecordsFound });
            var fullCols = tableId.find('thead > tr:first > th').not('.d-none').length;
            tr += "<tr class='default-row'>";
            tr += "<td colspan='" + fullCols + "' class='text-center'><strong>" + _lang.noRecordsFound + "</strong></td>";
            tr += "</tr>";
        }

        // Always append the “Total” row (footer-style) inside tbody
        var spanCols = columnCountWithoutDNoneClass(2); // all visible cols minus (current_stock + unit)
        tr += "<tr class='fw-bold'>";
        tr += "<td colspan='" + spanCols + "' class='text-end'>" + _lang.total + "</td>";
        tr += "<td class='text-end'>" + _parseQuantity(totalQuantity) + "</td>";
        tr += "<td></td>";
        tr += "</tr>";

        tableBody.append(tr);
    }


    function showNoRecordsMessageOnTableBody() {
        var tableBody = tableId.find('tbody');
        tableBody.empty();

        var fullCols = tableId.find('thead > tr:first > th').not('.d-none').length;

        var tr = "<tr class='default-row'>";
        tr += "<td colspan='" + fullCols + "' class='text-center'><strong>" + _lang.noRecordsFound + "</strong></td>";
        tr += "</tr>";

        // Keep the footer-style total row visible even in “no records” state
        var spanCols = columnCountWithoutDNoneClass(2);
        tr += "<tr class='fw-bold'>";
        tr += "<td colspan='" + spanCols + "' class='text-end'>" + _lang.total + "</td>";
        tr += "<td class='text-end'>" + _parseQuantity(0) + "</td>";
        tr += "<td></td>";
        tr += "</tr>";

        tableBody.append(tr);
    }


    function columnCountWithoutDNoneClass(minusCount) {
        return tableId.find('thead > tr:first > th').not('.d-none').length - minusCount;
    }

    /**
     *
     * Table Exporter
     * PDF, SpreadSheet
     * */
    $(document).on("click", '#generate_pdf', function () {
        tableId.tableExport({ type: 'pdf', escape: 'false' });
    });

    $(document).on("click", '#generate_excel', function () {
        tableId.tableExport({ type: 'xlsx', escape: 'false' });
    });

    $(document).on('change', 'select[name="item_category_id"]', function () {
        emptyItemSelection();
    });

    function emptyItemSelection() {
        $('#item_id').val(null).trigger('change');
    }
});//main function
