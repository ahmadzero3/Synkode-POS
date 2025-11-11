$(function () {
    "use strict";

    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");

    function loadDatatables() {
        tableId.DataTable().destroy();

        var exportColumns = [2, 3, 4, 5, 6, 7];

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            method: 'get',
            ajax: '/session/datatable-list',
            columns: [
                { targets: 0, data: 'id', orderable: true, visible: false },
                {
                    data: 'id',
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, full, meta) {
                        return '<input type="checkbox" class="form-check-input row-select" name="record_ids[]" value="' + data + '">';
                    }
                },
                { data: 'user_name', name: 'user_name' },
                { data: 'start_at', name: 'start_at' },
                { data: 'end_at', name: 'end_at' },
                { data: 'duration_minutes', name: 'duration_minutes' },
                { data: 'created_by_display', name: 'created_by_display' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],

            dom: "<'row' "+
                    "<'col-sm-12' "+
                        "<'float-start'l>"+
                        "<'float-end'f>"+
                        "<'float-end ms-2'<'card-body'B>>"+
                    ">"+
                 ">"+
                 "<'row'<'col-sm-12'tr>>"+
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className: 'btn btn-outline-danger buttons-copy buttons-html5 multi_delete',
                    text: 'Delete',
                    action: function (e, dt, node, config) {
                        requestDeleteRecords();
                    }
                },
                {
                    extend: 'copyHtml5',
                    exportOptions: { columns: exportColumns }
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
            order: [[0, 'desc']]
        });

        table.on('click', '.deleteRequest', function () {
            let deleteId = $(this).attr('data-delete-id');
            deleteRequest(deleteId);
        });

        // Match register list style
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate')
            .wrap("<div class='card-body py-3'>");
    }

    // Handle header checkbox click event
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
        const confirmed = await confirmAction();
        if (confirmed) datatableForm.trigger('submit');
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
        var jqxhr = $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': formArray.csrf },
        });

        jqxhr.done(function (data) {
            iziToast.success({ title: 'Success', layout: 2, message: data.message });
        });

        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: 'Error', layout: 2, message: message });
        });

        jqxhr.always(function () {
            afterCallAjaxResponse(formArray.formObject);
        });
    }

    function afterCallAjaxResponse(formObject) {
        loadDatatables();
    }

    $(document).ready(function () {
        loadDatatables();
    });
});