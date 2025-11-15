$(function () {
    "use strict";

    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");

    // ✅ Laravel route passed from Blade (fallback for dev)
    const datatableUrl = window.databaseBackupDatatableUrl || '/database-backup/datatable-list';
    const deleteUrl = window.databaseBackupDeleteUrl || '/database-backup/delete';
    const createUrl = window.databaseBackupCreateUrl || '/database-backup/create';

    // ✅ Initialize DataTable
    function loadDatatables() {
        if ($.fn.DataTable.isDataTable(tableId)) {
            tableId.DataTable().destroy();
        }

        const exportColumns = [2, 3, 4];

        const table = tableId.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: datatableUrl,
                type: 'GET',
                error: function (xhr, error, code) {
                    console.error("❌ DataTable AJAX error:", xhr.status, xhr.responseText);
                }
            },
            columns: [
                { data: 'id', orderable: true, visible: false },
                {
                    data: 'id',
                    orderable: false,
                    className: 'text-center',
                    render: data => `<input type="checkbox" class="form-check-input row-select" name="record_ids[]" value="${data}">`
                },
                { data: 'file_name', name: 'file_name' },
                { data: 'size', name: 'size' },
                { data: 'date', name: 'date' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],

            dom:
                "<'row'<'col-sm-12'<'float-start'l><'float-end'f><'float-end ms-2'<'card-body'B>>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className: 'btn btn-outline-danger buttons-copy buttons-html5 multi_delete',
                    text: 'Delete',
                    action: requestDeleteRecords,
                },
                { extend: 'copyHtml5', exportOptions: { columns: exportColumns } },
                { extend: 'excelHtml5', exportOptions: { columns: exportColumns } },
                { extend: 'csvHtml5', exportOptions: { columns: exportColumns } },
                { extend: 'pdfHtml5', orientation: 'portrait', exportOptions: { columns: exportColumns } },
            ],

            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
        });

        // ✅ Handle single delete click
        $(document).off('click', '.deleteBackupBtn').on('click', '.deleteBackupBtn', function () {
            const filename = $(this).data('filename');
            deleteRequest(filename);
        });

        // ✅ Wrap layout like other tables
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate')
            .wrap("<div class='card-body py-3'>");

        return table;
    }

    // ✅ Select all checkboxes in header
    tableId.find('thead').on('click', '.row-select', function () {
        const isChecked = $(this).prop('checked');
        tableId.find('tbody .row-select').prop('checked', isChecked);
    });

    function countCheckedCheckbox() {
        return $('input[name="record_ids[]"]:checked').length;
    }

    async function confirmDelete() {
        return await confirmAction("Are you sure you want to delete?");
    }

    // ✅ Delete single backup file
    async function deleteRequest(filename) {
        const confirmed = await confirmDelete();
        if (!confirmed) return;

        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: {
                filename: filename,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                iziToast.success({
                    title: 'Deleted',
                    message: response.message,
                    layout: 2,
                    position: 'topRight',
                });
                loadDatatables();
            },
            error: function (xhr) {
                let message = 'Error deleting backup.';
                if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                iziToast.error({ title: 'Error', layout: 2, message });
            },
        });
    }

    // ✅ Delete multiple records
    async function requestDeleteRecords() {
        const confirmed = await confirmDelete();
        if (!confirmed) return;

        if (countCheckedCheckbox() === 0) {
            iziToast.error({
                title: 'Warning',
                layout: 2,
                message: "Please select at least one record to delete",
                position: 'topRight',
            });
            return;
        }

        datatableForm.trigger('submit');
    }

    // ✅ Handle multi delete submit
    datatableForm.on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: deleteUrl,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                iziToast.success({
                    title: 'Success',
                    layout: 2,
                    message: data.message,
                    position: 'topRight',
                });
                loadDatatables();
            },
            error: function (response) {
                const message = response.responseJSON?.message || 'Error deleting backups.';
                iziToast.error({ title: 'Error', layout: 2, message });
            },
        });
    });

    // ✅ Create Backup
    $('#createBackupForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(form[0]);

        $.ajax({
            url: createUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function () {
                form.find('button[type="submit"]').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            },
            success: function (response) {
                iziToast.success({
                    title: 'Success',
                    message: response.message,
                    layout: 2,
                    position: 'topRight',
                });
                $('#createDatabaseBackupModal').modal('hide');
                loadDatatables();
            },
            error: function (xhr) {
                let message = 'Error creating backup.';
                if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                iziToast.error({
                    title: 'Error',
                    message,
                    layout: 2,
                    position: 'topRight',
                });
            },
            complete: function () {
                form.find('button[type="submit"]').prop('disabled', false).html('Submit');
            },
        });
    });

    // ✅ Reset modal after closing
    $('#createDatabaseBackupModal').on('hidden.bs.modal', function () {
        $('#createBackupForm')[0].reset();
        $('#backup_type_full').prop('checked', true);
        $('#schedule_type_now').prop('checked', true);
    });

    // ✅ Initialize everything
    $(document).ready(loadDatatables);
});
