$(function() {
	"use strict";

    const tableId = $('#datatable');
    const datatableForm = $("#datatableForm");

    /**
     *Server Side Datatable Records
    */
    window.loadDatatables = function() {
        //Delete previous data
        tableId.DataTable().destroy();

        // Include the new Register Name column in exports
        var exportColumns = [1,2,3,4,5,6]; // Index Starts from 0

        var table = tableId.DataTable({
            processing: true,
            serverSide: true,
            method:'get',
            ajax: {
                url: baseURL+'/transaction/cash/datatable-list',
                data:{
                    party_id : $('#party_id').val(),
                    user_id : $('#user_id').val(),
                    from_date : $('input[name="from_date"]').val(),
                    to_date : $('input[name="to_date"]').val(),
                },
            },
            columns: [
                {targets: 0, data:'id', orderable:true, visible:false},
                {data: 'transaction_type', name: 'transaction_type'},
                {data: 'transaction_date', name: 'transaction_date'},
                {data: 'party_name', name: 'party_name'},

                // NEW: Register Name (server sends 'register_text')
                {data: 'register_text', name: 'register_text'},

                {
                    data: 'amount',
                    name: 'amount',
                    render: function(data, type, row) {
                        // data is already formatted server-side (e.g., "1,000.00")
                        return '<span class="text-' + row.color_class + '">' + data + '</span>';
                    }
                },
                {data: 'note', name: 'note'},
                {data: 'username', name: 'username'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],

            dom: "<'row' "+
                    "<'col-sm-12' "+
                        "<'float-start' l>"+
                        "<'float-end' fr>"+
                        "<'float-end ms-2'"+
                            "<'card-body ' B >"+
                        ">"+
                    ">"+
                  ">"+
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

            buttons: [
                {
                    className: 'btn btn-outline-danger buttons-copy buttons-html5 multi_delete',
                    text: 'Delete',
                    action: function ( e, dt, node, config ) {
                       requestDeleteRecords();
                    }
                },
                { extend: 'copyHtml5',  exportOptions: { columns: exportColumns } },
                { extend: 'excelHtml5', exportOptions: { columns: exportColumns } },
                { extend: 'csvHtml5',   exportOptions: { columns: exportColumns } },
                { extend: 'pdfHtml5',   orientation: 'portrait', exportOptions: { columns: exportColumns } },
            ],

            select: {
                style: 'os',
                selector: 'td:first-child'
            },
            order: [[0, 'desc']]
        });

        table.on('click', '.deleteRequest', function () {
            let deleteId = $(this).attr('data-delete-id');
            deleteRequest(deleteId);
        });

        //Adding Space on top & bottom of the table attributes
        $('.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate').wrap("<div class='card-body py-3'>");
    }

    // Handle header checkbox click event
    tableId.find('thead').on('click', '.row-select', function() {
        var isChecked = $(this).prop('checked');
        tableId.find('tbody .row-select').prop('checked', isChecked);
    });

    function countCheckedCheckbox(){
        return $('input[name="record_ids[]"]:checked').length;
    }

    async function validateCheckedCheckbox(){
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (!confirmed) {
            return false;
        }
        if(countCheckedCheckbox() == 0){
            iziToast.error({title: 'Warning', layout: 2, message: "Please select at least one record to delete"});
            return false;
        }
        return true;
    }

    // Single delete request
    async function deleteRequest(id) {
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (confirmed) {
            deleteRecord(id);
        }
    }

    // Multiple delete request
    async function requestDeleteRecords(){
        const confirmed = await confirmAction();//Defined in ./common/common.js
        if (confirmed) {
            datatableForm.trigger('submit');
        }
    }

    datatableForm.on("submit", function(e) {
        e.preventDefault();

        const form = $(this);
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest('form').attr('action'),
            formObject : form,
            formData : new FormData(document.getElementById(form.attr("id"))),
        };
        ajaxRequest(formArray); //Defined in ./common/common.js
    });

    function deleteRecord(id){
        const form = datatableForm;
        const formArray = {
            formId: form.attr("id"),
            csrf: form.find('input[name="_token"]').val(),
            _method: form.find('input[name="_method"]').val(),
            url: form.closest('form').attr('action'),
            formObject : form,
            formData: new FormData()
        };
        formArray.formData.append('record_ids[]', id);
        ajaxRequest(formArray); //Defined in ./common/common.js
    }

    function afterSeccessOfAjaxRequest(formObject, response){
        //It is from cash-in-hand.js
        setCashInHandValue(response.cashInHand);
    }

    function ajaxRequest(formArray){
        var jqxhr = $.ajax({
            type: formArray._method,
            url: formArray.url,
            data: formArray.formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': formArray.csrf },
            beforeSend: function() {
                if (typeof beforeCallAjaxRequest === 'function') {
                    // Action Before Proceeding request
                }
            },
        });
        jqxhr.done(function(response) {
            iziToast.success({title: 'Success', layout: 2, message: response.message});
            if (typeof afterSeccessOfAjaxRequest === 'function') {
                afterSeccessOfAjaxRequest(formArray.formObject, response);
            }
        });
        jqxhr.fail(function(response) {
            var message = response.responseJSON.message;
            iziToast.error({title: 'Error', layout: 2, message: message});
        });
        jqxhr.always(function() {
            if (typeof afterCallAjaxResponse === 'function') {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function afterCallAjaxResponse(formObject){
        loadDatatables();
    }

    $(document).ready(function() {
        //Load Datatable
        loadDatatables();

        // Modal payment type select2 reinit (existing)
        initSelect2PaymentType({ dropdownParent: $('#invoicePaymentModal') });
	});

    $(document).on("change", '#party_id, #user_id, input[name="from_date"], input[name="to_date"]', function(e) {
        loadDatatables();
    });

});

window.setCashInHandValue = function(amount = 0, date = new Date()) {
    const formattedDate = date.toLocaleDateString('en-GB'); // Format: DD-MM-YYYY
    $(".cash-in-hand").html(`Cash In Hand (${formattedDate}): ${_parseFix(parseFloat(amount).toFixed(2))}`);
}
