$(function () {
    "use strict";

    const tableId = $("#datatable");

    const datatableForm = $("#datatableForm");

    /**
     *Server Side Datatable Records
     */
    function loadDatatables() {
        //Delete previous data
        tableId.DataTable().destroy();

        var exportColumns = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11]; //Index Starts from 0

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            method: "get",
            ajax: {
                url: baseURL + "/item/datatable-list",
                data: {
                    brand_id: $("#brand_id").val(),
                    item_category_id: $("#item_category_id").val(),
                    is_service: $("#is_service").val(),
                    created_by: $("#user_id").val(),
                    warehouse_id: $("#warehouse_id").val(),
                    tracking_type: $("#tracking_type").val(),
                },
            },
            columns: [
                { targets: 0, data: "id", orderable: true, visible: false },
                {
                    data: "id",
                    orderable: false,
                    className: "text-center",
                    render: function (data, type, full, meta) {
                        return (
                            '<input type="checkbox" class="form-check-input row-select" name="record_ids[]" value="' +
                            data +
                            '">'
                        );
                    },
                },
                {
                    data: "name",
                    render: function (data, type, full, meta) {
                        const quantity = parseFloat(full.current_stock);
                        const enabled = window.enableMinimumStockQty === true || window.enableMinimumStockQty === "true";
                        const threshold = parseFloat(window.minimumStockQty) || 0;

                        const style =
                            enabled && quantity <= threshold ? "color:red; font-weight:bold" : "";

                        const nameHtml = `<span style="${style}">${data}</span>`;

                        if (full.item_location) {
                            return (
                                nameHtml +
                                `
                <br>
                <span class="badge text-primary bg-light-primary p-2 text-uppercase px-3"
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Location">
                  ${full.item_location}
                  <i class="fadeIn bx bx-sm bx-location-plus"></i>
                </span>`
                            );
                        }

                        return nameHtml;
                    },
                },

                { data: "item_code", name: "item_code" },
                {
                    data: "sku",
                    name: "sku",
                    visible: itemSettings.sku == 1 ? true : false,
                },
                { data: "brand_name", name: "brand_name" },
                { data: "category_name", name: "category_name" },
                {
                    data: "sale_price",
                    name: "sale_price",
                    className: "text-end",
                },
                {
                    data: "purchase_price",
                    name: "purchase_price",
                    className: "text-end",
                },
                {
                    data: "current_stock",
                    name: "current_stock",
                    className: "text-left",
                },
                { data: "tracking_type", name: "tracking_type" },
                { data: "username", name: "username" },
                { data: "created_at", name: "created_at" },

                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                },
            ],

            dom:
                "<'row' " +
                "<'col-sm-12' " +
                "<'float-start' l" +
                /* card-body class - auto created here */
                ">" +
                "<'float-end' fr" +
                /* card-body class - auto created here */
                ">" +
                "<'float-end ms-2'" +
                "<'card-body ' B >" +
                ">" +
                ">" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className:
                        "btn btn-outline-danger buttons-copy buttons-html5 multi_delete",
                    text: "Delete",
                    action: function (e, dt, node, config) {
                        //Confirm user then trigger submit event
                        requestDeleteRecords();
                    },
                },
                // Apply exportOptions only to Copy button
                {
                    extend: "copyHtml5",
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
                // Apply exportOptions only to Excel button
                {
                    extend: "excelHtml5",
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
                // Apply exportOptions only to CSV button
                {
                    extend: "csvHtml5",
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
                // Apply exportOptions only to PDF button
                {
                    extend: "pdfHtml5",
                    orientation: "portrait", //or "landscape"
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
            ],

            select: {
                style: "os",
                selector: "td:first-child",
            },
            order: [[0, "desc"]],
            drawCallback: function () {
                /**
                 * Initialize Tooltip
                 * */
                setTooltip();
            },
        });

        table.on("click", ".deleteRequest", function () {
            let deleteId = $(this).attr("data-delete-id");

            deleteRequest(deleteId);
        });

        //Adding Space on top & bottom of the table attributes
        $(
            ".dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate"
        ).wrap("<div class='card-body py-3'>");
    }

    // Handle header checkbox click event
    tableId.find("thead").on("click", ".row-select", function () {
        var isChecked = $(this).prop("checked");
        tableId.find("tbody .row-select").prop("checked", isChecked);
    });

    /**
     * @return count
     * How many checkbox are checked
     */
    function countCheckedCheckbox() {
        var checkedCount = $('input[name="record_ids[]"]:checked').length;
        return checkedCount;
    }

    /**
     * Validate checkbox are checked
     */
    async function validateCheckedCheckbox() {
        const confirmed = await confirmAction(); //Defined in ./common/common.js
        if (!confirmed) {
            return false;
        }
        if (countCheckedCheckbox() == 0) {
            iziToast.error({
                title: "Warning",
                layout: 2,
                message: "Please select at least one record to delete",
            });
            return false;
        }
        return true;
    }
    /**
     * Caller:
     * Function to single delete request
     * Call Delete Request
     */
    async function deleteRequest(id) {
        const confirmed = await confirmAction(); //Defined in ./common/common.js
        if (confirmed) {
            deleteRecord(id);
        }
    }

    /**
     * Create Ajax Request:
     * Multiple Data Delete
     */
    async function requestDeleteRecords() {
        //validate checkbox count
        const confirmed = await confirmAction(); //Defined in ./common/common.js
        if (confirmed) {
            //Submit delete records
            datatableForm.trigger("submit");
        }
    }
    datatableForm.on("submit", function (e) {
        e.preventDefault();

        //Form posting Functionality
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest("form").attr("action"),
            formObject: form,
            formData: new FormData(document.getElementById(form.attr("id"))),
        };
        ajaxRequest(formArray); //Defined in ./common/common.js
    });

    /**
     * Create AjaxRequest:
     * Single Data Delete
     */
    function deleteRecord(id) {
        const form = datatableForm;
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest("form").attr("action"),
            formObject: form,
            formData: new FormData(), // Create a new FormData object
        };
        // Append the 'id' to the FormData object
        formArray.formData.append("record_ids[]", id);
        ajaxRequest(formArray); //Defined in ./common/common.js
    }

    /**
     * Ajax Request
     */
    function ajaxRequest(formArray) {
        var jqxhr = $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: "json",
            contentType: false,
            processData: false,
            headers: {
                "X-CSRF-TOKEN": formArray.csrf,
            },
            beforeSend: function () {
                // Actions to be performed before sending the AJAX request
                if (typeof beforeCallAjaxRequest === "function") {
                    // Action Before Proceeding request
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

    function afterCallAjaxResponse(formObject) {
        loadDatatables();
    }

    $(document).ready(function () {
        //Load Datatable
        loadDatatables();
    });

    $(document).on(
        "change",
        "#brand_id, #item_category_id, #is_service, #user_id, #warehouse_id, #tracking_type",
        function function_name() {
            loadDatatables();
        }
    );
});
