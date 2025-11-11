"use strict";

const tableId = $('#invoiceItemsTable');

let originalButtonText;

let submitButton = 'button[id="submit_form"]';

let addRowButtonText;

const rowCountStartFrom = 0;

const operation = $('#operation').val();

const currentWarehouse = $('#warehouse_id');

const itemSearchInputBoxId = $("#search_item");

var searchedItemPrice = 0;

var buttonId = $("#add_row");

/**
 * Language
 * */
const _lang = {
    pleaseSelectItem: "Item Name Should not be empty",
    pleaseSelectItemFromSearchBox: "Choose Item from Search Results!!",
    clickTochange: "Click to Change",
    clickToChangeTaxType: "Click to Change Tax Type",
    clickToSelectSerial: "Click to Select Serial Numbers",
    enterValidNumber: "Please enter a valid number",
    wantToDelete: "Do you want to delete?",
    paymentAndGrandTotalMismatched: "Total Payment Amount Should be equal to Grand Total!",
    rowAddedSuccessdully: "Item Added!",
    taxTypeChanged: "Tax type has changed!",
};

$("#submit_form").on('click', function (event) {
    event.preventDefault();

    $("#stockAdjustmentForm").submit();

});

function validatePaymentAndInvoiceTotal() {
    if (_parseFix(calculateTotalPayment()) != getGrandTotal()) {
        iziToast.error({ title: 'Warning', layout: 2, message: _lang.paymentAndGrandTotalMismatched });
        return false;
    }
    return true;
}

$("#stockAdjustmentForm").on("submit", function (e) {
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
    originalButtonText = form.find(submitButton).text();
    form.find(submitButton)
        .prop('disabled', true)
        .html('  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
}

function enableSubmitButton(form) {
    form.find(submitButton)
        .prop('disabled', false)
        .html(originalButtonText);
}

function beforeCallAjaxRequest(formObject) {
    disableSubmitButton(formObject);
}
function afterCallAjaxResponse(formObject) {
    enableSubmitButton(formObject);
}
function afterSeccessOfAjaxRequest(formObject) {
    formAdjustIfSaveOperation(formObject);
    pageRedirect(formObject);
}

function pageRedirect(formObject) {
    var redirectTo = '';
    if (formObject.response.id !== undefined) {
        redirectTo = '/stock-adjustment/details/' + formObject.response.id;
    } else {
        redirectTo = '/stock-adjustment/list';
    }
    setTimeout(function () {
        location.href = baseURL + redirectTo;
    }, 1000);
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
        formArray.formObject.response = data;
        iziToast.success({ title: 'Success', layout: 2, message: data.message });
        // Actions to be performed after response from the AJAX request
        if (typeof afterSeccessOfAjaxRequest === 'function') {
            afterSeccessOfAjaxRequest(formArray.formObject);
        }
    });
    jqxhr.fail(function (response) {
        var message = response.responseJSON.message;
        iziToast.error({ title: 'Error', layout: 2, message: message });
    });
    jqxhr.always(function () {
        // Actions to be performed after the AJAX request is completed, regardless of success or failure
        if (typeof afterCallAjaxResponse === 'function') {
            afterCallAjaxResponse(formArray.formObject);
        }
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
/**
 * When i click on Enter Button, call addRow()
 * */
itemSearchInputBoxId.on("keydown", function (event) {
    if (event.key === "Enter") {
        //iziToast.info({title: 'Info', layout: 2, message: _lang.pleaseSelectItemFromSearchBox});
    }
});
/**
 * Add Service or Products on front view
 * call addRow()
 * */
$("#add_row").on('click', function () {
    addRow();
});

/**
 * Add Row to front view
 * */
function addRow(recordObject) {
    if (Object.keys(recordObject).length === 0) {
        iziToast.error({ title: 'Warning', layout: 2, message: _lang.pleaseSelectItem });
        itemSearchInputBoxId.focus();
        return;
    }

    //Disable the Button
    disableAddRowButton(buttonId);
    //JSON Data to add row
    addRowToInvoiceItemsTable(recordObject);
    /*Enable the Disabled Button*/
    enableAddRowButton(buttonId);
    //Make Input box empty and keep curson on it
    itemSearchInputBoxId.val('').focus();
    //Row Added Message
    rowAddedSuccessdully();
}

function rowAddedSuccessdully() {
    //iziToast.success({title: _lang.rowAddedSuccessdully, layout: 2, message: ''});
    itemSearchInputBoxId.autocomplete("close");

    //reset variable
    searchedItemPrice = 0;

}
/**
 * Prepaired JSON data
 * */
function defaultJsonData() {
    var dataObject = {
        name: $('#search_item').val().trim(),
        description: "",
        quantity: 1,
        purchase_price: searchedItemPrice,
        total_price: 0
    };

    return dataObject;
}

/**
 * Make Loading of Add Button
 * */
function disableAddRowButton(buttonId) {
    addRowButtonText = buttonId.text();
    // Set button text to "Loading..."
    buttonId.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
    // Disable the button
    buttonId.prop('disabled', true);
}

function enableAddRowButton(buttonId) {
    //Restore the actual button name
    buttonId.html(addRowButtonText);
    // Enable the button
    buttonId.prop('disabled', false);
}

/**
 * return Table row count
 * */
function getRowCount() {
    var rowCount = returnDecimalValueByName('row_count');
    return rowCount;
}

/**
 * set table row count
 * */
function setRowCount() {
    var increamentRowCount = getRowCount();
    increamentRowCount++;
    $('input[name="row_count"]').val(increamentRowCount);
}

function itemInfoIcon(trackingType) {
    var message;
    if (trackingType == 'batch') {
        message = `<b>Tracking Type:</b><br>Batch wise Tracking`;
    } else if (trackingType == 'serial') {
        message = `<b>Tracking Type:</b>:<br>Serial wise Tracking`;
    } else {
        message = `<b>Tracking Type:</b><br>Regular`;
    }
    return `<a tabindex="0" class="text-primary" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="${message}"><i class="fadeIn animated bx bx-info-circle"></i></a>`;
}
/**
 * Create Service Table Row
 * Params: record Object
 *
 * autoLoadUpdateOperation parameter is true only when update page autoloaded
 * ex: Purchase bill -> Edit
 * */
function addRowToInvoiceItemsTable(recordObject, loadedFromUpdateOperation = false) {

    //Find service table row count
    var currentRowId = getRowCount();

    var tableBody = tableId.find('tbody');
    var warehouseId = (parseInt(recordObject.warehouse_id) > 0) ? recordObject.warehouse_id : currentWarehouse.val();
    var hiddenWarehouseId = '<input type="hidden" name="warehouse_id[' + currentRowId + ']" class="form-control" value="' + warehouseId + '">';
    var hiddenItemId = '<input type="hidden" name="item_id[' + currentRowId + ']" class="form-control" value="' + recordObject.id + '">';
    var inputItemName = `<label class="form-label mb-0" role="button">${recordObject.name}</label> ` + itemInfoIcon(recordObject.tracking_type);
    inputItemName += (recordObject.brand_name !== undefined)
        ? `<br><span class="badge bg-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Brand Name">${recordObject.brand_name}</span>`
        : '';
    var hiddenTrackingType = `<input type="hidden" id="tracking_type[${currentRowId}]" class="form-control" value="${recordObject.tracking_type}">`;
    var inputDescription = '<textarea rows="1" type="text" name="description[' + currentRowId + ']" class="form-control" placeholder="Description">' + recordObject.description + '</textarea>';

    var serialTracking = `<i class="fadeIn animated bx bx-list-ol bx-sm text-primary serialBtnForInvoice" role='button' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="${_lang.clickToSelectSerial}"></i>`;
    var _serialNumbers = (recordObject.serial_numbers !== undefined) ? recordObject.serial_numbers : '';
    var hiddenSerialNumbers = `<input type="hidden" name='serial_numbers[${currentRowId}]' class='form-control' value='${_serialNumbers}'>`;

    var _batchNumber = (recordObject.batch_no !== undefined) ? recordObject.batch_no : '';
    var inputBatchNumber = '<input type="text" name="batch_no[' + currentRowId + ']" class="form-control batch-group" value="' + _batchNumber + '">';

    var _mfgDate = (recordObject.mfg_date !== undefined) ? recordObject.mfg_date : '';
    var mfgDate = `<input type='text' class="form-control datepicker batch-group" name="mfg_date[${currentRowId}]" placeholder="Pick Date" value='${_mfgDate}'></td>`;

    var _expDate = (recordObject.exp_date !== undefined) ? recordObject.exp_date : '';
    var expDate = `<input type='text' class="form-control datepicker batch-group" name="exp_date[${currentRowId}]" placeholder="Pick Date" value='${_expDate}'></td>`;

    var _modelNo = (recordObject.model_no !== undefined) ? recordObject.model_no : '';
    var modelNo = '<input type="text" name="model_no[' + currentRowId + ']" class="form-control batch-group" value="' + _modelNo + '">';

    var _color = (recordObject.color !== undefined) ? recordObject.color : '';
    var color = '<input type="text" name="color[' + currentRowId + ']" class="form-control batch-group" value="' + _color + '">';

    var _size = (recordObject.size !== undefined) ? recordObject.size : '';
    var size = '<input type="text" name="size[' + currentRowId + ']" class="form-control batch-group" value="' + _size + '">';

    var stockInUnit = '<input type="text" disabled name="stockInUnit[' + currentRowId + ']" class="form-control" value="' + recordObject.stock_in_unit + '">';
    stockInUnit += `<span class="badge bg-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Warehouse Name"><i class="bx bx-building"></i> ${recordObject.warehouse_name}</span>`;

    var inputQuantity = '<input type="number" name="quantity[' + currentRowId + ']" class="form-control" value="' + recordObject.quantity + '">';

    var unitSelectionBox = `<input type="hidden" name='conversion_rate[${currentRowId}]' data-base-unit-id='${recordObject.base_unit_id}' data-base-price='${recordObject.purchase_price}' value='${recordObject.conversion_rate}'>`;
    unitSelectionBox += `<input type="hidden" id='selected_unit_id[${currentRowId}]' value='${recordObject.selected_unit_id}'>`;
    unitSelectionBox += generateUnitSelectionBox(recordObject.unitList, currentRowId, recordObject.selected_unit_id);

    var adjustmentTypeSelectionBox = generateAdjustmentTypeSelectionBox(currentRowId, recordObject.adjustment_type || 'increase');

    /*Keeping the Scheduled Job Records*/
    var removeClass = (!recordObject.assigned_user_id) ? 'remove' : '';
    var inputDeleteButton = '<button type="button" class="btn btn-outline-danger ' + removeClass + '"><i class="bx bx-trash me-0"></i></button>';


    var newRow = $('<tr id="' + currentRowId + '" class="highlight">');
    newRow.append('<td>' + hiddenWarehouseId + hiddenItemId + inputItemName + hiddenTrackingType + inputDescription + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_serial_tracking) ? 'd-none' : ''}">` + serialTracking + hiddenSerialNumbers + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_batch_tracking) ? 'd-none' : ''}">` + inputBatchNumber + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_mfg_date) ? 'd-none' : ''}">` + mfgDate + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_exp_date) ? 'd-none' : ''}">` + expDate + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_model) ? 'd-none' : ''}">` + modelNo + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_color) ? 'd-none' : ''}">` + color + '</td>');
    newRow.append(`<td class="${(!itemSettings.enable_size) ? 'd-none' : ''}">` + size + '</td>');
    newRow.append('<td>' + stockInUnit + '</td>');
    newRow.append('<td>' + inputQuantity + '</td>');
    newRow.append('<td>' + unitSelectionBox + '</td>');
    newRow.append('<td>' + adjustmentTypeSelectionBox + '</td>');
    newRow.append('<td>' + inputDeleteButton + '</td>'); // action moved to last


    // Append new row to the table body
    tableBody.prepend(newRow);

    //Serial #1
    loadTrackingDataFromAjax(recordObject, currentRowId, loadedFromUpdateOperation);

    //Serial #2
    afterAddRowFunctionality(currentRowId);
}

/**
 * HTML : After Add Row Functionality
 * */
function afterAddRowFunctionality(currentRowId) {
    //Remove Default existing row if exist
    removeDefaultRowFromTable();

    //Set Row Count
    setRowCount();

    //Custom Function Reset Date & Time Picker
    resetFlatpickr();

    //Reinitiate Tooltip
    setTooltip();

    //Calculate Row Records
    rowCalculator(currentRowId);
}

/**
 * Generate Unit Selection Box
 * */
function generateUnitSelectionBox(unitList, currentRowId, selectId = null) {
    const options = Object.values(unitList)
        .map(unit => `<option value="${unit.id}" ${selectId == unit.id ? 'selected' : ''}>${unit.name}</option>`)
        .join('');

    return `<select class="form-select" name="unit_id[${currentRowId}]">${options}</select>`;
}

/**
 * Generate Adjustment Type Selection Box
 * Increase or Decrease
 */
function generateAdjustmentTypeSelectionBox(currentRowId, selectedType = 'increase') {
    const options = `
            <option value="increase" ${selectedType === 'increase' ? 'selected' : ''}>Increase</option>
            <option value="decrease" ${selectedType === 'decrease' ? 'selected' : ''}>Decrease</option>
        `;
    return `<select class="form-select" name="adjustment_type[${currentRowId}]">${options}</select>`;
}

/**
 * Remove Default Row from table
 * */
function removeDefaultRowFromTable() {
    if ($('.default-row').length) {
        $('.default-row').closest('tr').remove();
    }
}

function afterRemoveFunctions() {
    setBottomOfTableRecords();

    // If no rows left, restore default row with proper colspan
    if ($('#invoiceItemsTable tbody tr').length === 0) {
        var colCount = $('#invoiceItemsTable > thead > tr:first > th').not('.d-none').length;
        var defaultRow = `<tr class="default-row">
                            <td colspan="${colCount}" class="text-center fw-light fst-italic">
                                No items are added yet!!
                            </td>
                          </tr>`;
        $('#invoiceItemsTable tbody').append(defaultRow);
    }
}

/**
 * Delete Row
 * */
$(document).on('click', '.remove', function () {
    $(this).closest('tr').remove();
    afterRemoveFunctions();
});
/**
 * Reset Date & Time Pickers
 * */
function resetFlatpickr() {
    $(".datepicker").flatpickr({
        dateFormat: dateFormatOfApp, // Set the date format
    });

    flatpickr(".time-picker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",//"K" for AM & PM
    });
}


/**
 * Row: Change events
 * */
$(document).on('change', '#invoiceItemsTable tr input, #invoiceItemsTable tr select', function () {
    const rowId = $(this).closest('tr').attr('id');
    rowCalculator(rowId);
});


/**
 * Calculate Sum Of Total Column(s)
 * */
function returnSumOfQuantityColumn() {
    var rowCount = getRowCount();
    let sumOfQuantityColumn = 0;
    for (var i = rowCountStartFrom; i < rowCount; i++) {
        if ($("#total[" + i + "]")) {
            sumOfQuantityColumn += returnDecimalValueByName('quantity[' + i + ']');
        }
    }
    return sumOfQuantityColumn;
}

function setSumOfTablefooter() {
    var sumOfQuantityColumn = returnSumOfQuantityColumn();
    //Set Sum Of Total
    $('.sum_of_quantity').text(_parseFix(sumOfQuantityColumn));
}
/**
 * Main:
 * Calculate specific row
 * */
function rowCalculator(rowId) {
    setBottomOfTableRecords();
}
function setBottomOfTableRecords() {
    setSumOfTablefooter();              // Serial#5
}


/**
 * return Decimal input value
 * */
function returnDecimalValueByName(inputBoxName) {
    var _inpuBoxId = $("input[name ='" + inputBoxName + "']");
    var inputBoxValue = _inpuBoxId.val();

    if (inputBoxValue == '' || isNaN(inputBoxValue)) {
        return parseFloat(0);
    }
    return parseFloat(inputBoxValue);
}

/**
 * Autocomplete
 * Item Search Input box
 * */
itemSearchInputBoxId.on('click', function () {
    initItemAutocomplete(itemSearchInputBoxId, {
        warehouse_id: currentWarehouse.val(),
        module: 'sale',
        request_from: 'stock_adjustment',
        onSelect: function (item) {
            addRow(item); // Your existing addRow logic
        }
    });
});

$(document).ready(function () {
    /**
     * Toggle the sidebar of the template
     * */
    toggleSidebar();

    /**
     * Empty Defalt Row: colspan count
     * */
    $('.default-row').attr('colspan', $('#invoiceItemsTable > thead > tr:first > th').not('.d-none').length);

    /**
     * Set colspan of the table bottom
     * */
    var footerTotalColspan = 3 + itemSettings.enable_serial_tracking
        + itemSettings.enable_batch_tracking
        + itemSettings.enable_mfg_date
        + itemSettings.enable_exp_date
        + itemSettings.enable_model
        + itemSettings.enable_color
        + itemSettings.enable_size;
    $('.tfoot-first-td').attr('colspan', footerTotalColspan);

    /**
     * Update Opetation
     * */
    if (operation == 'update' || operation == 'convert') {
        updateOperation();
    }
});

function updateOperation() {

    var jsonObject = JSON.parse(itemsTableRecords);

    jsonObject.forEach((data, index) => {
        var dataObject = {
            warehouse_id: data.warehouse_id,
            warehouse_name: data.warehouse.name,

            id: data.item_id,
            name: data.item.name,
            brand_name: data.item.brand ? data.item.brand.name : '',
            tracking_type: data.tracking_type,
            description: (data.description != null) ? data.description : '',
            quantity: _parseQuantity(data.quantity),
            unitList: data.unitList,
            base_unit_id: data.item.base_unit_id,
            secondary_unit_id: data.item.secondary_unit_id,
            selected_unit_id: data.unit_id,
            stock_in_unit: data.stock_in_unit,
            adjustment_type: data.adjustment_type,
            //conversion_rate   : data.item.conversion_rate,
            serial_numbers: (data.tracking_type == 'serial') ? JSON.stringify(data.itemSerialTransactions.map(item => item.serial_code)) : '',
            batch_no: (data.tracking_type == 'batch' && data.batch.item_batch_master.batch_no !== null) ? data.batch.item_batch_master.batch_no : '',
            mfg_date: (data.tracking_type == 'batch' && data.batch.item_batch_master.mfg_date !== null) ? data.batch.item_batch_master.mfg_date : '',
            exp_date: (data.tracking_type == 'batch' && data.batch.item_batch_master.exp_date !== null) ? data.batch.item_batch_master.exp_date : '',
            model_no: (data.tracking_type == 'batch' && data.batch.item_batch_master.model_no !== null) ? data.batch.item_batch_master.model_no : '',
            color: (data.tracking_type == 'batch' && data.batch.item_batch_master.color !== null) ? data.batch.item_batch_master.color : '',
            size: (data.tracking_type == 'batch' && data.batch.item_batch_master.size !== null) ? data.batch.item_batch_master.size : '',
        };

        addRowToInvoiceItemsTable(dataObject, true);
    });
}

function loadTrackingDataFromAjax(recordObject, rowId, loadedFromUpdateOperation) {
    if (recordObject.tracking_type == 'serial' && loadedFromUpdateOperation == false) {
        /**
         * Show TrackingType Data
         * */
        _globalSerialTracking(rowId);

    }
    else if (recordObject.tracking_type == 'batch' && loadedFromUpdateOperation == false) {
        //
    }

    autoSetClassOfTableRow(recordObject.tracking_type, rowId);

}

/**
 * Table Class adjustments
 * */
function autoSetClassOfTableRow(trackingType, rowId) {
    if (trackingType == 'serial') {
        /**
         * Disabling Datepicker by removing datepicker class name
         * */
        $(`input[name='mfg_date[${rowId}]']`).removeClass('datepicker').prop('readonly', true).attr('placeholder', '');
        $(`input[name='exp_date[${rowId}]']`).removeClass('datepicker').prop('readonly', true).attr('placeholder', '');

        /**
         * Set all batch group readonly
         * */
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`).prop('readonly', true).addClass('cursor-not-allowed');
        $(`#invoiceItemsTable tr#${rowId} input.serial-group`).prop('readonly', false);
    }
    else if (trackingType == 'batch') {
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`).prop('readonly', false);
        $(`input[name='batch_no[${rowId}]']`).addClass('border custom-border-primary');

        $(`#invoiceItemsTable tr#${rowId} i.serialBtnForInvoice`)
            .removeClass('serialBtnForInvoice')
            .addClass('serialBtnForInvoice-disabled')
            .addClass('text-muted')
            .addClass('cursor-not-allowed').attr('data-bs-toggle', '');

        //$(".serialBtnForInvoice").removeClass('serialBtnForInvoice').addClass('serialBtnForInvoice-disabled');
    } else {
        $(`input[name='mfg_date[${rowId}]']`).removeClass('datepicker').prop('readonly', true).attr('placeholder', '');
        $(`input[name='exp_date[${rowId}]']`).removeClass('datepicker').prop('readonly', true).attr('placeholder', '');
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`).prop('readonly', true).addClass('cursor-not-allowed');
        $(`#invoiceItemsTable tr#${rowId} i.serialBtnForInvoice`)
            .removeClass('serialBtnForInvoice')
            .addClass('serialBtnForInvoice-disabled')
            .addClass('text-muted')
            .addClass('cursor-not-allowed').attr('data-bs-toggle', '');
    }
    //If Discount Column is not allowed to edit by user then add this class to it.
    $(`input[name="discount[${rowId}]"]`).filter('[readonly]').addClass('cursor-not-allowed');
}



// Enable autocomplete for dynamically created batch_no[] input fields
$(document).on('focus', 'input[name^="batch_no["]', function () {
    const $input = $(this);

    // Always re-initialize autocomplete to ensure correct data for each row
    if ($input.data('ui-autocomplete')) {
        $input.autocomplete('destroy');
        $input.removeData('ui-autocomplete');
    }

    // Extract row index from input name, e.g., batch_no[2]
    const match = $input.attr('name').match(/^batch_no\[(\d+)\]$/);
    if (!match) return;
    const rowIndex = match[1];

    // Get item_id and warehouse_id for this row
    const itemId = $(`input[name="item_id[${rowIndex}]"]`).val();
    const warehouseId = $(`input[name="warehouse_id[${rowIndex}]"]`).val() || currentWarehouse.val();

    // Wrap the input in a container with a larger width if not already wrapped
    if (!$input.parent().hasClass('autocomplete-input-wrapper')) {
        $input.wrap('<div class="autocomplete-input-wrapper" style=""></div>');
        //$input.css('min-width', '340px'); // Make input wider than the td
    }

    $input.autocomplete({
        minLength: 0,
        appendTo: $input.parent(), // Render menu inside the wrapper for better control
        source: function (request, response) {
            if (!itemId || !warehouseId) {
                response([]);
                return;
            }

            //add ajax request here
            $.ajax({
                url: baseURL + '/item/batch-table-records/ajax/get-list',
                dataType: "json",
                data: {
                    search: request.term,
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    required_only: 'batch_no',
                },
                success: function (data) {
                    if (data && Array.isArray(data)) {
                        const filtered = data.filter(batch =>
                            batch.batchNo && batch.batchNo.toLowerCase().includes(request.term.toLowerCase())
                        );
                        response(filtered.map(batch => ({
                            label: batch.batchNo,
                            value: batch.batchNo,
                            mfg_date: batch.mfgDate,
                            exp_date: batch.expDate,
                            model_no: batch.modelNo,
                            color: batch.color,
                            size: batch.size,
                            available_stock: batch.availableStock
                        })));
                    } else {
                        response([]);
                    }


                },
                error: function () {
                    iziToast.error({ title: 'Error', layout: 2, message: 'Failed to fetch batch details.' });
                    response([]);

                }
            });

        },
        select: function (event, ui) {
            if (ui.item) {
                $(`input[name="mfg_date[${rowIndex}]"]`).val(ui.item.mfg_date || '');
                $(`input[name="exp_date[${rowIndex}]"]`).val(ui.item.exp_date || '');
                $(`input[name="model_no[${rowIndex}]"]`).val(ui.item.model_no || '');
                $(`input[name="color[${rowIndex}]"]`).val(ui.item.color || '');
                $(`input[name="size[${rowIndex}]"]`).val(ui.item.size || '');
            }
        },
        open: function (event, ui) {
            // Add header after the menu is opened
            // Use table layout for perfect alignment like Syncfusion
            if (!$(this).data('headerAppended')) {
                const header = $(`
                        <li class='ui-autocomplete-category' style='padding:0; border-bottom:1px solid #ddd; background:#f8f9fa;'>
                            <div style="display:table; width:100%;">
                                <div style="display:table-row; font-weight:bold;">
                                    <div style="display:table-cell; padding:6px 8px; width:18%;">Batch Name</div>
                                    <div style="display:table-cell; padding:6px 8px; width:14%;">Mfg Date</div>
                                    <div style="display:table-cell; padding:6px 8px; width:14%;">Exp Date</div>
                                    <div style="display:table-cell; padding:6px 8px; width:14%;">Model No.</div>
                                    <div style="display:table-cell; padding:6px 8px; width:14%;">Color</div>
                                    <div style="display:table-cell; padding:6px 8px; width:14%;">Size</div>
                                    <div style="display:table-cell; padding:6px 8px; width:12%; text-align:right;">Stock</div>
                                </div>
                            </div>
                        </li>
                    `);
                $(this).autocomplete("widget").prepend(header);
                $(this).data('headerAppended', true);
            }
            $(this).autocomplete("widget").css({
                'min-width': '700px',
                'width': 'auto',
                'max-width': '900px',
                'z-index': 9999
            });
        },
        close: function () {
            // Remove header flag so it can be re-added next time
            $(this).removeData('headerAppended');
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        // Use table row layout for each item for perfect alignment
        return $("<li>")
            .css("padding", "0")
            .append(`
                    <div style="display:table; width:100%;">
                        <div style="display:table-row;">
                            <div style="display:table-cell; padding:6px 8px; width:18%;">${item.value || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:14%;">${item.mfg_date || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:14%;">${item.exp_date || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:14%;">${item.model_no || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:14%;">${item.color || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:14%;">${item.size || ''}</div>
                            <div style="display:table-cell; padding:6px 8px; width:12%; text-align:right; color:${parseFloat(item.available_stock) > 0 ? '#000' : '#dc3545'};">${item.available_stock || 'N/A'}</div>
                        </div>
                    </div>
                `)
            .appendTo(ul);
    };

    // Open the autocomplete menu immediately for better UX
    setTimeout(() => $input.autocomplete('search', $input.val()), 100);

    // Show suggestions when pressing the down arrow key
    $input.on('keydown', function (e) {
        if (e.key === "ArrowDown") {
            $input.autocomplete('search', $input.val());
        }
    });
});


function setupAutocomplete(inputNamePrefix, requiredField) {
    $(document).on('focus', `input[name^="${inputNamePrefix}["]`, function () {
        const $input = $(this);

        /**
         * in this row i have id= racking_type[id], execute only if tracking_type is batch
         * restrict if tracking_type is serial or regular, where has only id and value
         */
        const trackingType = $input.closest('tr').find('input[id^="tracking_type["]').val();
        if (trackingType !== 'batch') {
            return; // Only apply autocomplete for batch tracking type
        }

        // Destroy existing autocomplete instance
        if ($input.data('ui-autocomplete')) {
            $input.autocomplete('destroy').removeData('ui-autocomplete');
        }

        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {

                $.ajax({
                    url: baseURL + '/item/batch-table-records/ajax/get-list',
                    dataType: 'json',
                    data: {
                        search: request.term,
                        required_only: requiredField,
                    },
                    success: function (data) {
                        const results = Array.isArray(data.results) ? data.results : [];
                        response(results.map(item => ({
                            label: item.text,
                            value: item.text
                        })));
                    },
                    error: function () {
                        iziToast.error({ title: 'Error', layout: 2, message: 'Failed to fetch batch details.' });
                        response([]);
                    }
                });
            }
        });

        // Trigger autocomplete dropdown
        $input.trigger('keydown');
        setTimeout(() => $input.autocomplete('search', $input.val()), 100);
    });
}

// Initialize autocomplete for different fields
setupAutocomplete('color', 'color');
setupAutocomplete('model_no', 'model_no');
setupAutocomplete('size', 'size');
