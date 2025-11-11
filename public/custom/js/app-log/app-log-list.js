$(function () {
    "use strict";

    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");
    const logDetailsModal = new bootstrap.Modal(document.getElementById('logDetailsModal'));

    function loadDatatables() {
        tableId.DataTable().destroy();

        var exportColumns = [2, 3, 4, 5];

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            method: 'get',
            ajax: '/app-log/datatable-list',
            columns: [
                { targets: 0, data: 'uuid', orderable: true, visible: false },
                {
                    data: 'uuid',
                    orderable: false,
                    className: 'text-center',
                    render: function (data) {
                        return '<input type="checkbox" class="form-check-input row-select" name="record_ids[]" value="' + data + '">';
                    }
                },
                { data: 'type_badge', name: 'type' },
                { data: 'severity_badge', name: 'severity' },
                { data: 'message', name: 'message' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],

            dom: "<'row' " +
                "<'col-sm-12' " +
                "<'float-start'l>" +
                "<'float-end'f>" +
                "<'float-end ms-2'<'card-body'B>>" +
                ">" +
                ">" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className: 'btn btn-outline-danger buttons-copy buttons-html5 multi_delete',
                    text: 'Delete',
                    action: function () {
                        requestDeleteRecords();
                    }
                },
                {
                    extend: 'copyHtml5',
                    exportOptions: { columns: exportColumns },
                    text: 'Copy',
                    init: function (api, node) {
                        $(node).removeClass('dt-button');
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'portrait',
                    exportOptions: { columns: exportColumns },
                },
            ],

            select: { style: 'os', selector: 'td:first-child' },
            order: [],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            language: { buttons: { copyTitle: '', copySuccess: { _: '', 1: '' } } }
        });

        table.on('click', '.deleteRequest', function () {
            let deleteId = $(this).attr('data-delete-id');
            deleteRequest(deleteId);
        });

        table.on('click', '.view-details', function () {
            let logId = $(this).attr('data-log-id');
            viewLogDetails(logId);
        });

        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate')
            .wrap("<div class='card-body py-3'>");
    }

    tableId.find('thead').on('click', '.row-select', function () {
        var isChecked = $(this).prop('checked');
        tableId.find('tbody .row-select').prop('checked', isChecked);
    });

    function countCheckedCheckbox() {
        return $('input[name="record_ids[]"]:checked').length;
    }

    async function validateCheckedCheckbox() {
        const confirmed = await confirmAction();
        if (!confirmed) return false;

        if (countCheckedCheckbox() == 0) {
            iziToast.error({ title: 'Warning', layout: 2, message: "Please select at least one record to delete" });
            return false;
        }
        return true;
    }

    async function deleteRequest(id) {
        const confirmed = await confirmAction();
        if (confirmed) deleteRecord(id);
    }

    async function requestDeleteRecords() {
        if (!await validateCheckedCheckbox()) return;
        datatableForm.trigger('submit');
    }

    function viewLogDetails(logId) {
        $.ajax({
            url: '/app-log/' + logId,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;

                    $('#detail-type').text(data.type);
                    $('#detail-severity').text(data.severity);
                    $('#detail-message').text(data.message);
                    $('#detail-created-at').text(data.created_at);
                    $('#detail-content').text(JSON.stringify(data.content, null, 2));

                    logDetailsModal.show();
                }
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    layout: 2,
                    message: 'Failed to load log details'
                });
            }
        });
    }

    datatableForm.on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest('form').attr('action'),
            formObject: form,
            formData: new FormData(document.getElementById(form.attr("id"))),
        };
        ajaxRequest(formArray);
    });

    function deleteRecord(id) {
        const form = datatableForm;
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest('form').attr('action'),
            formObject: form,
            formData: new FormData()
        };
        formArray.formData.append('record_ids[]', id);
        ajaxRequest(formArray);
    }

    function ajaxRequest(formArray) {
        $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': formArray.csrf },
        })
            .done(function (data) {
                iziToast.success({ title: 'Success', layout: 2, message: data.message });
                loadDatatables();
            })
            .fail(function (response) {
                var message = response.responseJSON?.message || 'An error occurred';
                iziToast.error({ title: 'Error', layout: 2, message: message });
            });
    }

    // ✅ New: iziToast confirmation for "Clear All Logs"
    $('#clearAllLogs').on('click', function () {
        iziToast.question({
            timeout: 0,
            close: false,
            overlay: true,
            displayMode: 'once',
            id: 'clearAllLogsQuestion',
            zindex: 99999,
            title: 'Confirm',
            message: 'Are you sure you want to clear all application logs? This action cannot be undone.',
            position: 'center',
            buttons: [
                [
                    '<button><b>Yes, Clear All</b></button>',
                    function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');

                        $.ajax({
                            url: '/app-log/clear-all',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                iziToast.success({
                                    title: 'Success',
                                    layout: 2,
                                    message: response.message
                                });
                                loadDatatables();
                            },
                            error: function () {
                                iziToast.error({
                                    title: 'Error',
                                    layout: 2,
                                    message: 'Failed to clear logs'
                                });
                            }
                        });
                    },
                    true
                ],
                [
                    '<button>No, Cancel</button>',
                    function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        iziToast.info({
                            title: 'Cancelled',
                            layout: 2,
                            message: 'Operation cancelled'
                        });
                    }
                ]
            ]
        });
    });

    $(document).ready(function () {
        loadDatatables();
    });
});