"use strict";

const tableId = $("#invoiceItemsTable");

let originalButtonText;

let submitButton = 'button[id="submit_form"]';

let addRowButtonText;

const rowCountStartFrom = 0;

const operation = $("#operation").val();

const currentWarehouse = $("#warehouse_id");

const itemSearchInputBoxId = $("#search_item");

const serialCheckbox = $("#serial_number_checkbox");

var searchedItemPrice = 0;

var buttonId = $("#add_row");

let callType = "submit_form";

var partyId = $("#party_id");

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
    paymentAndGrandTotalMismatched:
        "Total Payment Amount Should be equal to Grand Total!",
    rowAddedSuccessdully: "Item Added!",
    taxTypeChanged: "Tax type has changed!",
    pleaseSelectParty: "Please Select Party!",
};

$("#submit_form, #submit_form_with_print").on("click", function (event) {
    event.preventDefault();

    var clickedId = this.id;

    if (clickedId === "submit_form_with_print") {
        callType = "submit_form_with_print";

        // 🔴 Always force invoice to finished when saving/printing
        $("#invoice_status").val("finished");
    }

    $("#invoiceForm").submit();
});

$("#hold_button").on("click", function (event) {
    event.preventDefault();
    $("#invoice_status").val("pending");
    $("#invoiceForm").submit();
});

function validatePaymentAndInvoiceTotal() {
    if (_parseFix(calculateTotalPayment()) != getGrandTotal()) {
        iziToast.error({
            title: "Warning",
            layout: 2,
            message: _lang.paymentAndGrandTotalMismatched,
        });
        return false;
    }
    return true;
}

$("#invoiceForm").on("submit", function (e) {
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
    originalButtonText = form.find(submitButton).text();
    form.find(submitButton)
        .prop("disabled", true)
        .html(
            '  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...'
        );
}

function enableSubmitButton(form) {
    form.find(submitButton).prop("disabled", false).html(originalButtonText);
}

function beforeCallAjaxRequest(formObject) {
    showSpinner();
    disableSubmitButton(formObject);
}
function afterCallAjaxResponse(formObject) {
    hideSpinner();
    enableSubmitButton(formObject);
}
function afterSeccessOfAjaxRequest(formObject) {
    formAdjustIfSaveOperation(formObject);
    pageRedirect(formObject);
}

function pageRedirect(formObject) {
    var invoiceId = formObject.response.id;

    // if(invoiceId !== undefined){
    //    var redirectTo = '/sale/invoice/details/'+invoiceId;
    // }else{
    //    var redirectTo = '/sale/invoice/list';
    // }
    // setTimeout(function() {
    //    location.href = baseURL + redirectTo;
    // }, 1000);

    /**
     * Show Print Page
     * */
    if (callType === "submit_form_with_print") {
        if (
            window.open(
                `${baseURL}/pos/print/${invoiceId}`,
                "_blank",
                "scrollbars=1,resizable=1,height=300,width=450"
            )
        ) {
            window.location = baseURL + "/pos";
        }
    } else {
        window.location = baseURL + "/pos";
    }

    //Reload Page
    //window.location=baseURL+"/pos";
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
        headers: {
            "X-CSRF-TOKEN": formArray.csrf,
        },
        beforeSend: function () {
            // Actions to be performed before sending the AJAX request
            if (typeof beforeCallAjaxRequest === "function") {
                beforeCallAjaxRequest(formArray.formObject);
            }
        },
    });
    jqxhr.done(function (data) {
        formArray.formObject.response = data;
        iziToast.success({
            title: "Success",
            layout: 2,
            message: data.message,
        });
        // Actions to be performed after response from the AJAX request
        if (typeof afterSeccessOfAjaxRequest === "function") {
            afterSeccessOfAjaxRequest(formArray.formObject);
        }
    });
    jqxhr.fail(function (xhr) {
        if (
            xhr.status === 422 &&
            xhr.responseJSON &&
            xhr.responseJSON.message
        ) {
            iziToast.error({
                title: "Error",
                message: xhr.responseJSON.message,
            });
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            iziToast.error({
                title: "Error",
                layout: 2,
                message: xhr.responseJSON.message,
            });
        }
    });
    jqxhr.always(function () {
        // Actions to be performed after the AJAX request is completed, regardless of success or failure
        if (typeof afterCallAjaxResponse === "function") {
            afterCallAjaxResponse(formArray.formObject);
        }
    });
}

function formAdjustIfSaveOperation(formObject) {
    const _method = formObject.find('input[name="_method"]').val();
    /* Only if Save Operation called*/
    if (_method.toUpperCase() == "POST") {
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

function isPartySelected() {
    if (!partyId.val()) {
        iziToast.info({
            title: "Message",
            layout: 2,
            message: _lang.pleaseSelectParty,
        });
        partyId.focus().select2("open");
        return false;
    }
    return true;
}
/**
 * Add Service or Products on front view
 * call addRow()
 * */
$("#add_row").on("click", function () {
    addRow();
});

/**
 * Add Row to front view
 * */
function addRow(recordObject) {
    if (Object.keys(recordObject).length === 0) {
        iziToast.error({
            title: "Warning",
            layout: 2,
            message: _lang.pleaseSelectItem,
        });
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
    itemSearchInputBoxId.val("").focus();
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
        name: $("#search_item").val().trim(),
        description: "",
        quantity: 1,
        sale_price: searchedItemPrice,
        total_price: 0,
    };

    return dataObject;
}

/**
 * Make Loading of Add Button
 * */
function disableAddRowButton(buttonId) {
    addRowButtonText = buttonId.text();
    // Set button text to "Loading..."
    buttonId.html(
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...'
    );
    // Disable the button
    buttonId.prop("disabled", true);
}

function enableAddRowButton(buttonId) {
    //Restore the actual button name
    buttonId.html(addRowButtonText);
    // Enable the button
    buttonId.prop("disabled", false);
}

/**
 * return Table row count
 * */
function getRowCount() {
    var rowCount = returnDecimalValueByName("row_count");
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
    if (trackingType == "batch") {
        message = `<b>Tracking Type:</b><br>Batch wise Tracking`;
    } else if (trackingType == "serial") {
        message = `<b>Tracking Type:</b>:<br>Serial wise Tracking`;
    } else {
        message = `<b>Tracking Type:</b><br>Regular`;
    }
    return `<a tabindex="0" class="text-primary" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="${message}"><i class="fadeIn animated bx bx-info-circle"></i></a>`;
}

serialCheckbox.on("change", function () {
    $(".serial-column, .serial-td").toggleClass("d-none", !this.checked);
    updateDefaultRowColspan();
});

function updateDefaultRowColspan() {
    const visibleColumns = $("#invoiceItemsTable > thead > tr:first > th").not(
        ".d-none"
    ).length;
    $(".default-row").attr("colspan", visibleColumns);
}
/**
 * Create Service Table Row
 * Params: record Object
 *
 * autoLoadUpdateOperation parameter is true only when update page autoloaded
 * ex: Sale bill -> Edit
 * */
function addRowToInvoiceItemsTable(
    recordObject,
    loadedFromUpdateOperation = false
) {
    if (!isPartySelected()) {
        return false;
    }

    //Find service table row count
    var currentRowId = getRowCount();

    var tableBody = tableId.find("tbody");
    var warehouseId =
        parseInt(recordObject.warehouse_id) > 0
            ? recordObject.warehouse_id
            : currentWarehouse.val();
    var hiddenWarehouseId =
        '<input type="hidden" name="warehouse_id[' +
        currentRowId +
        ']" class="form-control" value="' +
        warehouseId +
        '">';
    var hiddenItemId =
        '<input type="hidden" name="item_id[' +
        currentRowId +
        ']" class="form-control" value="' +
        recordObject.id +
        '">';
    var inputItemName =
        `<label class="form-label" role="button">${recordObject.name}</label> ` +
        itemInfoIcon(recordObject.tracking_type);
    var inputDescription =
        '<textarea rows="1" type="text" name="description[' +
        currentRowId +
        ']" class="form-control" placeholder="Description">' +
        recordObject.description +
        "</textarea>";

    var serialTracking = `<i class="fadeIn animated bx bx-list-ol text-primary serialBtnForInvoice" role='button' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="${_lang.clickToSelectSerial}"></i>`;
    var _serialNumbers =
        recordObject.serial_numbers !== undefined
            ? recordObject.serial_numbers
            : "";
    var hiddenSerialNumbers = `<input type="hidden" name='serial_numbers[${currentRowId}]' class='form-control' value='${_serialNumbers}'>`;

    var _batchNumber =
        recordObject.batch_no !== undefined ? recordObject.batch_no : "";
    var inputBatchNumber =
        '<input type="text" name="batch_no[' +
        currentRowId +
        ']" class="form-control batch-group" value="' +
        _batchNumber +
        '">';

    var _mfgDate =
        recordObject.mfg_date !== undefined ? recordObject.mfg_date : "";
    var mfgDate = `<input type='text' class="form-control datepicker batch-group" name="mfg_date[${currentRowId}]" placeholder="Pick Date" value='${_mfgDate}'></td>`;

    var _expDate =
        recordObject.exp_date !== undefined ? recordObject.exp_date : "";
    var expDate = `<input type='text' class="form-control datepicker batch-group" name="exp_date[${currentRowId}]" placeholder="Pick Date" value='${_expDate}'></td>`;

    var _modelNo =
        recordObject.model_no !== undefined ? recordObject.model_no : "";
    var modelNo =
        '<input type="text" name="model_no[' +
        currentRowId +
        ']" class="form-control batch-group" value="' +
        _modelNo +
        '">';

    var _mrp =
        recordObject.mrp !== undefined && parseFloat(recordObject.mrp) >= 0
            ? _parseFix(recordObject.mrp)
            : "";
    var mrp =
        '<input type="text" name="mrp[' +
        currentRowId +
        ']" class="form-control" value="' +
        _mrp +
        '">';

    var _color = recordObject.color !== undefined ? recordObject.color : "";
    var color =
        '<input type="text" name="color[' +
        currentRowId +
        ']" class="form-control batch-group" value="' +
        _color +
        '">';

    var _size = recordObject.size !== undefined ? recordObject.size : "";
    var size =
        '<input type="text" name="size[' +
        currentRowId +
        ']" class="form-control batch-group" value="' +
        _size +
        '">';

    var inputQuantity =
        '<input type="text" name="quantity[' +
        currentRowId +
        ']" class="form-control text-start" value="' +
        recordObject.quantity +
        '">';
    var inputUnitPrice =
        '<input type="text" name="sale_price[' +
        currentRowId +
        ']" class="form-control text-start" value="' +
        _parseFix(recordObject.sale_price) +
        '">';
    var hiddenTotalUnitPrice =
        '<input type="hidden" name="total_sale_price[' +
        currentRowId +
        ']" class="form-control" value="' +
        recordObject.total_price +
        '">';

    var unitSelectionBox = `<input type="hidden" name='conversion_rate[${currentRowId}]' data-base-unit-id='${recordObject.base_unit_id}' data-base-price='${recordObject.sale_price}' value='${recordObject.conversion_rate}'>`;
    unitSelectionBox += `<input type="hidden" id='selected_unit_id[${currentRowId}]' value='${recordObject.selected_unit_id}'>`;
    unitSelectionBox += generateUnitSelectionBox(
        recordObject.unitList,
        currentRowId,
        recordObject.selected_unit_id
    );

    var inputDiscount = '<div class="input-group">';
    inputDiscount +=
        '<input type="text" name="discount[' +
        currentRowId +
        ']" class="form-control" value="' +
        (recordObject.sale_price_discount
            ? _parseFix(recordObject.sale_price_discount)
            : 0) +
        '"' +
        (!allowUserToSaleDiscount ? " readonly" : "") +
        ">";
    inputDiscount +=
        '<button class="btn btn-outline-secondary btn_discount_type" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="' +
        _lang.clickTochange +
        '">' +
        getAutoDiscountTypeSymbol(recordObject.discount_type) +
        "</button></div>";

    var hiddenDiscountType =
        '<input type="hidden" name="discount_type[' +
        currentRowId +
        ']" class="form-control" value="' +
        recordObject.discount_type +
        '">';
    var hiddenTotalPriceAfterDiscount =
        '<input type="hidden" name="total_price_after_discount[' +
        currentRowId +
        ']" class="form-control" value="' +
        recordObject.total_price_after_discount +
        '">';
    var inputDiscountAmount =
        '<input type="text" name="discount_amount[' +
        currentRowId +
        ']" class="form-control form-control-plaintext text-end" readonly value="' +
        (recordObject.discount_amount ?? "") +
        '">';

    if (loadedFromUpdateOperation == false) {
        //Loaded from item selection box
        var taxType = recordObject.is_sale_price_with_tax
            ? "exclusive"
            : "exclusive";
    } else {
        var taxType = recordObject.is_sale_price_with_tax
            ? "inclusive"
            : "exclusive";
    }
    var hiddenTaxType =
        '<input type="hidden" name="tax_type[' +
        currentRowId +
        ']" class="form-control" value="' +
        taxType +
        '">';

    var taxGroup = "";
    //taxGroup = '<div class="input-group">';
    taxGroup += generateTaxSelectionBox(
        recordObject.taxList,
        currentRowId,
        recordObject.tax_id
    );
    //taxGroup +='<button class="btn btn-outline-secondary btn_tax_type" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="'+_lang.clickToChangeTaxType+'">'+ getShortTaxTypeName(taxType) +'</button></div>';

    var inputTaxAmount =
        '<input type="text" name="tax_amount[' +
        currentRowId +
        ']" class="form-control text-end form-control-plaintext" readonly  value="' +
        (recordObject.tax_amount ?? "") +
        '">';

    var inputTotal =
        '<input type="text" name="total[' +
        currentRowId +
        ']" class="form-control form-control-plaintext text-start" readonly  value="0">';

    /*Keeping the Scheduled Job Records*/
    var removeClass = !recordObject.assigned_user_id ? "remove" : "";
    var inputDeleteButton =
        '<button type="button" class="btn btn-outline-danger ' +
        removeClass +
        '"><i class="bx bx-trash me-0"></i></button>';

    var newRow = $('<tr id="' + currentRowId + '" class="highlight">');
    newRow.append(
        "<td>" +
        hiddenWarehouseId +
        hiddenItemId +
        inputItemName +
        inputDescription +
        "</td>"
    );

    const isSerialVisible =
        itemSettings.enable_serial_tracking &&
        $("#serial_number_checkbox").prop("checked");
    newRow.append(
        `<td class="serial-td ${isSerialVisible ? "" : "d-none"}">` +
        serialTracking +
        hiddenSerialNumbers +
        "</td>"
    );
    /*newRow.append(`<td class="${(!itemSettings.enable_batch_tracking)?'d-none':''}">` + inputBatchNumber + '</td>');
            newRow.append(`<td class="${(!itemSettings.enable_mfg_date)?'d-none':''}">` + mfgDate + '</td>');
            newRow.append(`<td class="${(!itemSettings.enable_exp_date)?'d-none':''}">` + expDate + '</td>');
            newRow.append(`<td class="${(!itemSettings.enable_model)?'d-none':''}">` + modelNo + '</td>');
            newRow.append(`<td class="${(!itemSettings.show_mrp)?'d-none':''}">` + mrp + '</td>');*/
    newRow.append(
        `<td class="${!itemSettings.enable_color ? "d-none" : ""}">` +
        color +
        "</td>"
    );
    newRow.append(
        `<td class="${!itemSettings.enable_size ? "d-none" : ""}">` +
        size +
        "</td>"
    );

    newRow.append("<td>" + inputQuantity + "</td>");
    newRow.append("<td>" + unitSelectionBox + "</td>");
    newRow.append("<td>" + inputUnitPrice + hiddenTotalUnitPrice + "</td>");
    newRow.append(
        `<td class="${!itemSettings.show_discount ? "d-none" : ""}">` +
        inputDiscount +
        hiddenDiscountType +
        hiddenTotalPriceAfterDiscount +
        inputDiscountAmount +
        "</td>"
    );

    newRow.append(
        `<td class="${noTaxFlag() ? "d-none" : ""}">` +
        taxGroup +
        hiddenTaxType +
        inputTaxAmount +
        "</td>"
    );
    newRow.append("<td>" + inputTotal + "</td>");
    newRow.append("<td>" + inputDeleteButton + "</td>");

    // Add action buttons
    var actionButtonCell = $("<td>");

    // Append new row to the table body
    tableBody.prepend(newRow);

    //Serial #1
    loadTrackingDataFromAjax(
        recordObject,
        currentRowId,
        loadedFromUpdateOperation
    );

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
 * Generate Tax Type Selection Box
 * */
function generateTaxSelectionBox(taxList, currentRowId, selectId = null) {
    // Check if taxList already has a "None" entry
    const hasNone = Object.values(taxList).some(
        (tax) => tax.name.toLowerCase() === "none"
    );

    let options = Object.values(taxList)
        .map(
            (tax) =>
                `<option value="${tax.id}" data-tax-rate="${tax.rate}" ${selectId == tax.id ? "selected" : ""
                }>${tax.name}</option>`
        )
        .join("");

    // If no "None" option in list, prepend it
    if (!hasNone) {
        const noneOption = `<option value="" data-tax-rate="0" ${!selectId ? "selected" : ""
            }>None</option>`;
        options = noneOption + options;
    }

    return `<select class="form-select" name="tax_id[${currentRowId}]">${options}</select>`;
}


/**
 * Generate Unit Selection Box
 * */
function generateUnitSelectionBox(unitList, currentRowId, selectId = null) {
    const options = Object.values(unitList)
        .map(
            (unit) =>
                `<option value="${unit.id}" ${selectId === unit.id ? "selected" : ""
                }>${unit.name}</option>`
        )
        .join("");

    return `<select class="form-select" name="unit_id[${currentRowId}]">${options}</select>`;
}

/**
 * Remove Default Row from table
 * */
function removeDefaultRowFromTable() {
    if ($(".default-row").length) {
        $(".default-row").closest("tr").remove();
    }
}

function afterRemoveFunctions() {
    setBottomOfTableRecords();
    // Check if the table is empty and re-add default row
    const tableBody = $("#invoiceItemsTable tbody");
    if (tableBody.children("tr").length === 0) {
        tableBody.append(
            '<tr><td colspan="9" class="text-center fw-light fst-italic default-row">No items are added yet !!</td></tr>'
        );
        updateDefaultRowColspan();
    }
}
/**
 * Delete Row
 * */
$(document).on("click", ".remove", function () {
    $(this).closest("tr").remove();
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
        dateFormat: "H:i", //"K" for AM & PM
    });
}

/**
 * Discount Type change on click
 *
 * */
$(document).on("click", ".btn_discount_type", function () {
    var _this = $(this);
    const currentRowId = _this.closest("tr").attr("id");

    var btnName = _this.text();

    // Select discount_type using this index
    var discountType = $('input[name="discount_type[' + currentRowId + ']"]');
    var discountTypeValue = discountType.val();

    //Change Button Name or Symbol
    if (discountTypeValue == "percentage") {
        _this.text(getAutoDiscountTypeSymbol("fixed"));
        discountType.val("fixed");
    } else {
        _this.text(getAutoDiscountTypeSymbol("percentage"));
        discountType.val("percentage");
    }
    $('input[name="discount_amount[' + currentRowId + ']"]')
        .focus()
        .trigger("change");
});

function getAutoDiscountTypeSymbol(discountType) {
    if (discountType == "percentage") {
        return "%";
    } else {
        return "$";
    }
}
/**
 * Tax Type change on click
 *
 * */
$(document).on("click", ".btn_tax_type", function () {
    var _this = $(this);
    const currentRowId = _this.closest("tr").attr("id");

    var btnName = _this.text();

    // Select tax_type using this index
    var taxType = $('input[name="tax_type[' + currentRowId + ']"]');
    var taxTypeValue = taxType.val();

    //Change Button Name or Symbol
    if (taxTypeValue == "inclusive") {
        _this.text(getShortTaxTypeName("exclusive"));
        taxType.val("exclusive");
    } else {
        _this.text(getShortTaxTypeName("inclusive"));
        taxType.val("inclusive");
    }
    iziToast.info({ title: "Info", layout: 2, message: _lang.taxTypeChanged });
    $('input[name="tax_amount[' + currentRowId + ']"]')
        .focus()
        .trigger("change");
});

function getShortTaxTypeName(taxType) {
    return taxType == "inclusive" ? "Inc." : "Exl.";
}
/**
 * Row: Change events
 * */
$(document).on(
    "change",
    "#invoiceItemsTable tr input, #invoiceItemsTable tr select",
    function () {
        const rowId = $(this).closest("tr").attr("id");
        rowCalculator(rowId);
    }
);

/**
 * Calculate Sum Of Total Column(s)
 * */
function returnSumOfTotalColumn() {
    var rowCount = getRowCount();
    let sumOfTotalColumn = 0;
    for (var i = rowCountStartFrom; i < rowCount; i++) {
        if ($("#total[" + i + "]")) {
            sumOfTotalColumn += returnDecimalValueByName("total[" + i + "]");
        }
    }
    return sumOfTotalColumn;
}

/**
 * Calculate Sum Of Total Column(s)
 * */
function returnSumOfQuantityColumn() {
    var rowCount = getRowCount();
    let sumOfQuantityColumn = 0;
    for (var i = rowCountStartFrom; i < rowCount; i++) {
        if ($("#total[" + i + "]")) {
            sumOfQuantityColumn += returnDecimalValueByName(
                "quantity[" + i + "]"
            );
        }
    }
    return sumOfQuantityColumn;
}

/**
 * Calculate Sum Of Tax Column(s)
 * */
function returnSumOfTaxColumn() {
    var rowCount = getRowCount();
    let totalTaxAmount = 0;
    for (var i = rowCountStartFrom; i < rowCount; i++) {
        if ($("#total[" + i + "]")) {
            totalTaxAmount += returnDecimalValueByName("tax_amount[" + i + "]");
        }
    }
    return totalTaxAmount;
}

/**
 * Calculate Sum Of Discount Column(s)
 * */
function returnSumOfDiscountColumn() {
    var rowCount = getRowCount();
    let totalTaxAmount = 0;
    for (var i = rowCountStartFrom; i < rowCount; i++) {
        if ($("#total[" + i + "]")) {
            totalTaxAmount += returnDecimalValueByName(
                "discount_amount[" + i + "]"
            );
        }
    }
    return totalTaxAmount;
}

function setSumOfTablefooter() {
    var sumOfTotalColumn = returnSumOfTotalColumn();
    var sumOfQuantityColumn = returnSumOfQuantityColumn();
    var sumOfTaxColumn = returnSumOfTaxColumn();
    var sumOfDiscountColumn = returnSumOfDiscountColumn();
    //Set Sum Of Total
    $(".sum_of_total").text(_parseFix(sumOfTotalColumn));
    $(".sum_of_quantity").text(_parseFix(sumOfQuantityColumn));
    $(".sum_of_tax").text(_parseFix(sumOfTaxColumn));
    $(".sum_of_discount").text(_parseFix(sumOfDiscountColumn));
}

/**
 * Set bottom records of invoice
 * @set Sutotal
 * @set Tax Total
 * @set Grand Total
 * */
function setBottomInvoiceTotal() {
    var sumOfTotalColumn = returnSumOfTotalColumn();
    var getRoundOff = getCurrentRoundOffAmount();

    sumOfTotalColumn = parseFloat(sumOfTotalColumn) + parseFloat(getRoundOff);

    //Set Grand Total
    $(".grand_total").val(_parseFix(sumOfTotalColumn));

    convertToExchangeCurrencyAmount();

    autoFillPaymentInputBox();
}

function getGrandTotal() {
    return parseFloat($(".grand_total").val());
}

function autoFillPaymentInputBox() {
    if (operation == "save") {
        var grandTotal = getGrandTotal();
        $("input[name='payment_amount[0]']").val(grandTotal);
    }
}
/**
 * Rounf-Off Checked or not
 * @return boolean
 * */
function isCheckboxChecked() {
    return $("#round_off_checkbox").prop("checked");
}
/**
 * On-change round off
 * Based on Checkbox
 * Automatic calculation
 * */
$(document).on("change", "#round_off_checkbox", function () {
    setRoundOffAmount("automatic");
    setBottomInvoiceTotal();
});
/**
 * Manuall Round-Off
 * */
$(document).on("change", ".round_off", function () {
    setRoundOffAmount("manual");
    setBottomInvoiceTotal();
});

function setRoundOffAmount(workType = "automatic") {
    if (workType == "automatic") {
        var setRoundOffAmount = calculateRoundOff();
    } else {
        //manual
        var setRoundOffAmount = getCurrentRoundOffAmount();
    }
    setRoundOffAmount = returnZeroIfEmptyyOrisNaN(setRoundOffAmount);

    $(".round_off").val(_parseFix(setRoundOffAmount));
}

function getCurrentRoundOffAmount() {
    return $(".round_off").val();
}
/**
 * Calculate Round-Off
 */
function calculateRoundOff() {
    if (isCheckboxChecked()) {
        var sumOfTotalColumn = returnSumOfTotalColumn();

        var afterRoundOff = Math.round(sumOfTotalColumn);

        var roundOffValue = afterRoundOff - sumOfTotalColumn;

        return roundOffValue;
    } else {
        return 0;
    }
}

/**
 * Calculate Price Corversation rate wise
 * example: Base Unit 1, Secondary unit 10
 * EachUnit = BaseUnit/SecondaryUnit
 * */
function calculatePriceWithConversationRate(rowId) {
    var baseUnitId = $("input[name='conversion_rate[" + rowId + "]'").attr(
        "data-base-unit-id"
    );
    var baseUnitPrice = $("input[name='sale_price[" + rowId + "]'").val();
    var conversionRate = returnDecimalValueByName(
        "conversion_rate[" + rowId + "]"
    );

    var _newSelectedUnitId = $(
        'select[name="unit_id[' + rowId + ']"] option:selected'
    ).val();
    var _oldSelectedUnitId = $(
        "input[id='selected_unit_id[" + rowId + "]'"
    ).val();

    if (_newSelectedUnitId === _oldSelectedUnitId) {
        $("input[name='sale_price[" + rowId + "]'").val(
            _parseFix(baseUnitPrice)
        );
    } else if (
        _newSelectedUnitId !== _oldSelectedUnitId &&
        _newSelectedUnitId !== baseUnitId
    ) {
        $("input[name='sale_price[" + rowId + "]'").val(
            _parseFix(baseUnitPrice / conversionRate)
        );
    } else {
        $("input[name='sale_price[" + rowId + "]'").val(
            _parseFix(baseUnitPrice * conversionRate)
        );
    }
    //Update selected ID
    $("input[id='selected_unit_id[" + rowId + "]'").val(_newSelectedUnitId);
}

/**
 * Main:
 * Calculate specific row
 * */
function rowCalculator(rowId) {
    calculatePriceWithConversationRate(rowId); // Serial#0
    rowCalculateTotalUnitPrice(rowId); // Serial#1
    rowCalculateDiscountAmount(rowId); // Serial#2
    rowCalculateTaxAmount(rowId); // Serial#3
    rowCalculateTotal(rowId); // Serial#4
    setBottomOfTableRecords();
}
function setBottomOfTableRecords() {
    setSumOfTablefooter(); // Serial#5
    setRoundOffAmount(); // Serial#6
    setBottomInvoiceTotal(); // Serial#7
    calulateBalance(); // Serial#8
}
/**
 * Row: Calculate Quantity x Price
 * */
function rowCalculateTotalUnitPrice(rowId) {
    var quantity = returnDecimalValueByName("quantity[" + rowId + "]");
    var unitPrice = returnDecimalValueByName("sale_price[" + rowId + "]");
    //Set to Hidden Box
    $("input[name='total_sale_price[" + rowId + "]'").val(quantity * unitPrice);
}

/**
 * Row: Settle row Total
 * */
function rowCalculateTotal(rowId) {
    var totalSalePrice = returnDecimalValueByName(
        "total_sale_price[" + rowId + "]"
    ); //Price * Qty
    var discountAmount = returnDecimalValueByName(
        "discount_amount[" + rowId + "]"
    );
    var taxType = $('input[name="tax_type[' + rowId + ']"]').val();
    var taxAmount = returnDecimalValueByName("tax_amount[" + rowId + "]");

    var rowFinalTotal = totalSalePrice - discountAmount;
    if (taxType == "exclusive") {
        rowFinalTotal += taxAmount;
    }
    $("input[name='total[" + rowId + "]'").val(_parseFix(rowFinalTotal));
}

/**
 * Row: Calculate Discount Amount
 * Based on percentage & fixed
 * */
function rowCalculateDiscountAmount(rowId) {
    var discountAmount = 0;
    var totalPriceAfterDiscount = 0;
    var totalUnitPrice = returnDecimalValueByName(
        "total_sale_price[" + rowId + "]"
    );
    var discountType = returnRowDiscountType(rowId);
    var discountInput = returnDecimalValueByName("discount[" + rowId + "]");

    discountAmount =
        discountType == "percentage"
            ? (totalUnitPrice * discountInput) / 100
            : discountInput;
    totalPriceAfterDiscount = totalUnitPrice - discountAmount;
    //Set Discount Amount
    $("input[name='discount_amount[" + rowId + "]'").val(
        _parseFix(discountAmount)
    );
    $("input[name='total_price_after_discount[" + rowId + "]'").val(
        totalPriceAfterDiscount
    );
}

/**
 * Row: Calculate tax
 * Based on Inclusive and exclusive
 * */
function rowCalculateTaxAmount(rowId) {
    var taxAmount = 0;
    var taxType = returnRowTaxType(rowId);
    var taxRate = $(
        'select[name="tax_id[' + rowId + ']"] option:selected'
    ).data("tax-rate");
    taxRate = parseFloat(taxRate);
    var totalPriceAfterDiscount = returnDecimalValueByName(
        "total_price_after_discount[" + rowId + "]"
    );
    taxAmount = returnTaxValue(taxType, taxRate, totalPriceAfterDiscount);
    //Set Tax Amount
    $("input[name='tax_amount[" + rowId + "]'").val(_parseFix(taxAmount));
}

/**
 * Row: find tax type
 * @return inclusive or exclusive
 * */
function returnRowTaxType(rowId) {
    return $("input[name='tax_type[" + rowId + "]'").val();
}

/**
 * Row: Find disocunt type
 * @return percentage or fixed
 * */
function returnRowDiscountType(rowId) {
    return $("input[name='discount_type[" + rowId + "]'").val();
}

/**
 * return Decimal input value
 * */
function returnDecimalValueByName(inputBoxName) {
    var _inpuBoxId = $("input[name ='" + inputBoxName + "']");
    var inputBoxValue = _inpuBoxId.val();

    if (inputBoxValue == "" || isNaN(inputBoxValue)) {
        return parseFloat(0);
    }
    return parseFloat(inputBoxValue);
}

/**
 * Autocomplete
 * Item Search Input box
 * */

itemSearchInputBoxId
    .autocomplete({
        minLength: 1,
        source: function (request, response) {
            $.ajax({
                url: baseURL + "/item/ajax/get-list",
                dataType: "json",
                data: {
                    search: request.term,
                    warehouse_id: currentWarehouse.val(),
                    party_id: partyId.val(),
                },
                success: function (data) {
                    if (
                        data.length === 1 &&
                        data[0].item_code !== "" &&
                        (data[0].item_code === request.term ||
                            data[0].item_code === request.term)
                    ) {
                        // If only one item is returned and its barcode or item_code matches the search term exactly
                        itemSearchInputBoxId.autocomplete("close");
                        itemSearchInputBoxId
                            .autocomplete("option", "select")
                            .call(itemSearchInputBoxId[0], null, {
                                item: data[0],
                            });
                    } else {
                        response(data);
                    }
                },
            });
        },

        focus: function (event, ui) {
            if (ui.item !== undefined) {
                itemSearchInputBoxId.val(ui.item.name);
                if (ui.item.sale_price !== undefined) {
                    searchedItemPrice = _parseFix(ui.item.sale_price);
                }
            }
            return false;
        },

        select: function (event, ui) {
            if (ui.item !== undefined) {
                itemSearchInputBoxId.val(ui.item.name);
                addRow(ui.item);
            }
            return false;
        },

        open: function (event, ui) {
            // Add header after the menu is opened
            var header = $(
                "<li class='ui-autocomplete-category' style='padding: 5px; border-bottom: 1px solid #ddd; background-color: #f8f9fa;'>" +
                "<div style='display: flex; font-weight: bold;'>" +
                "<span style='flex: 3;'>Name</span>" +
                "<span style='flex: 1;'>Brand</span>" +
                "<span style='flex: 1; text-align: right;'>Sales Price</span>" +
                "<span style='flex: 1; text-align: right;'>Purchase Price</span>" +
                "<span style='flex: 1; text-align: right;'>Stock</span>" +
                "</div></li>"
            );
            $(this).autocomplete("widget").prepend(header);
        },
    })
    .autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .attr("style", "padding: 5px; border-bottom: 1px solid #eee;")
            .append(
                `<div style="display: flex; align-items: center;">
                        <span style="flex: 3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.name || "N/A"
                }</span>
                        <span style="flex: 1; text-align: left;">${item.brand_name
                }</span>
                        <span style="flex: 1; text-align: right;">${_parseFix(item.sale_price) || "N/A"
                }</span>
                        <span style="flex: 1; text-align: right;">${_parseFix(item.purchase_price) || "N/A"
                }</span>
                        <span style="flex: 1; text-align: right; color: ${_parseQuantity(item.current_stock) > 0
                    ? "#000000"
                    : "#dc3545"
                };">${_parseQuantity(item.current_stock) || "N/A"
                }</span>
                     </div>`
            )
            .appendTo(ul);
    };

$(document).ready(function () {
    /**
     * Empty Defalt Row: colspan count
     * */
    $(".default-row").attr(
        "colspan",
        $("#invoiceItemsTable > thead > tr:first > th").not(".d-none").length
    );

    /**
     * Set colspan of the table bottom
     * */
    $(".tfoot-first-td").attr(
        "colspan",
        $("#invoiceItemsTable > thead > tr:first > th").not(".d-none").length -
        6
    );

    /**
     * Update Opetation
     * */
    if (operation == "update" || operation == "convert") {
        updateOperation(itemsTableRecords);
    }
});

/**
 * Used this in sale-order -> operation -> create
 *
 * */
window.addRowToInvoiceItemsTableFromBatchTracking = function (dataObject) {
    /**
     * Remove already added row while selecting batch
     * */
    tableId.find(`tr#${dataObject.mainTableRowId}`).remove();

    /**
     * @argument (object, true)
     * Dynamically adding row then no need to show pop up so it's second argument is true
     * */
    addRowToInvoiceItemsTable(dataObject, true);
};

function updateOperation(stringData) {
    var jsonObject = JSON.parse(stringData);

    jsonObject.forEach((data, index) => {
        var dataObject = {
            warehouse_id: data.warehouse_id,
            id: data.item_id,
            name: data.item.name,
            tracking_type: data.tracking_type,
            description: data.description != null ? data.description : "",
            sale_price: data.unit_price,
            is_sale_price_with_tax: data.tax_type == "inclusive" ? 1 : 0,
            tax_id: data.tax_id,
            quantity: _parseQuantity(data.current_stock),
            taxList: taxList,
            unitList: data.unitList,
            base_unit_id: data.item.base_unit_id,
            secondary_unit_id: data.item.secondary_unit_id,
            selected_unit_id: data.unit_id,
            conversion_rate: data.item.conversion_rate,
            sale_price_discount: data.discount,
            discount_type: data.discount_type,
            discount_amount: data.discount_amount,
            total_price_after_discount: 0,
            tax_amount: data.tax_amount,
            total_price: data.total,

            serial_numbers:
                data.tracking_type == "serial"
                    ? JSON.stringify(
                        data.itemSerialTransactions.map(
                            (item) => item.serial_code
                        )
                    )
                    : "",

            batch_no:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.batch_no !== null
                    ? data.batch.item_batch_master.batch_no
                    : "",
            mfg_date:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.mfg_date !== null
                    ? data.batch.item_batch_master.mfg_date
                    : "",
            exp_date:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.exp_date !== null
                    ? data.batch.item_batch_master.exp_date
                    : "",
            mrp:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.mrp !== null
                    ? data.batch.item_batch_master.mrp
                    : data.mrp,
            model_no:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.model_no !== null
                    ? data.batch.item_batch_master.model_no
                    : "",
            color:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.color !== null
                    ? data.batch.item_batch_master.color
                    : "",
            size:
                data.tracking_type == "batch" &&
                    data.batch.item_batch_master.size !== null
                    ? data.batch.item_batch_master.size
                    : "",
        };

        addRowToInvoiceItemsTable(dataObject, true);
    });
}

function loadTrackingDataFromAjax(
    recordObject,
    rowId,
    loadedFromUpdateOperation
) {
    if (
        recordObject.tracking_type == "serial" &&
        loadedFromUpdateOperation == false
    ) {
        /**
         * Show TrackingType Data
         * POP UP Selection
         * */
        _globalSerialTracking(rowId);
    } else if (
        recordObject.tracking_type == "batch" &&
        loadedFromUpdateOperation == false
    ) {
        /**
         * Show Batch Tracking
         * loadedFromUpdateOperation = false because don't show pop up
         * */
        _globalBatchTracking(rowId);
    } else {
        //General
    }
    autoSetClassOfTableRow(recordObject.tracking_type, rowId);
}

/**
 * Table Class adjustments
 * */
function autoSetClassOfTableRow(trackingType, rowId) {
    if (trackingType == "serial") {
        /**
         * Disabling Datepicker by removing datepicker class name
         * */
        $(`input[name='mfg_date[${rowId}]']`)
            .removeClass("datepicker")
            .prop("readonly", true)
            .attr("placeholder", "");
        $(`input[name='exp_date[${rowId}]']`)
            .removeClass("datepicker")
            .prop("readonly", true)
            .attr("placeholder", "");

        /**
         * Set all batch group readonly
         * */
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`)
            .prop("readonly", true)
            .addClass("cursor-not-allowed");
        $(`#invoiceItemsTable tr#${rowId} input.serial-group`).prop(
            "readonly",
            false
        );
    } else if (trackingType == "batch") {
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`).prop(
            "readonly",
            false
        );
        $(`input[name='batch_no[${rowId}]']`).addClass(
            "border custom-border-primary"
        );

        $(`#invoiceItemsTable tr#${rowId} i.serialBtnForInvoice`)
            .removeClass("serialBtnForInvoice")
            .addClass("serialBtnForInvoice-disabled")
            .addClass("text-muted")
            .addClass("cursor-not-allowed")
            .attr("data-bs-toggle", "");

        //$(".serialBtnForInvoice").removeClass('serialBtnForInvoice').addClass('serialBtnForInvoice-disabled');
    } else {
        $(`input[name='mfg_date[${rowId}]']`)
            .removeClass("datepicker")
            .prop("readonly", true)
            .attr("placeholder", "");
        $(`input[name='exp_date[${rowId}]']`)
            .removeClass("datepicker")
            .prop("readonly", true)
            .attr("placeholder", "");
        $(`#invoiceItemsTable tr#${rowId} input.batch-group`)
            .prop("readonly", true)
            .addClass("cursor-not-allowed");
        $(`#invoiceItemsTable tr#${rowId} i.serialBtnForInvoice`)
            .removeClass("serialBtnForInvoice")
            .addClass("serialBtnForInvoice-disabled")
            .addClass("text-muted")
            .addClass("cursor-not-allowed")
            .attr("data-bs-toggle", "");
    }
    //If Discount Column is not allowed to edit by user then add this class to it.
    $(`input[name="discount[${rowId}]"]`)
        .filter("[readonly]")
        .addClass("cursor-not-allowed");
}

/**
 * Calculate Balance Amount
 * */
function calulateBalance() {
    var grandTotal = getGrandTotal();
    var currentPayment = calculateTotalPayment();

    if (operation == "update" || operation == "convert") {
        var previouslyPaidAmount = _parseFix($(".paid_amount").val());
        var balance = grandTotal - previouslyPaidAmount - currentPayment;
    } else {
        //save operation
        var balance = grandTotal - currentPayment;
    }
    $(".balance").text(_parseFix(balance));

    //Show Change Return
    changeReturn(balance);
}

function changeReturn(balance) {
    var changeReturnValue = balance < 0 ? balance : 0;
    $(".change_return").text(_parseFix(Math.abs(changeReturnValue)));
}

/**
 * Show Second Payment Option
 * Which is already hidden with class d-none
 * */
$(".add-payment-type").on("click", function () {
    $('select[name="payment_type_id[1]"]').closest("tr").removeClass("d-none");
    $(".add-payment-type-parent").addClass("d-none");
    $(".change_return_parent").addClass("d-none");
});

/*When enter payment input box*/
$('[name^="payment_amount"]').on("keyup", function () {
    calulateBalance();
});

$(document).on("click", ".delete-payment", function () {
    var paymentId = $(this).closest("tr").attr("id");
    deletePaymentRequest(paymentId);
});

/**
 * Caller:
 * Function to single delete request
 * Call Delete Request
 */
async function deletePaymentRequest(paymentId) {
    const confirmed = await confirmAction(); //Defined in ./common/common.js
    if (confirmed) {
        var url = baseURL + "/payment/sale-bill/delete/";
        ajaxGetRequest(url, paymentId, "delete-payment");
    }
}

function ajaxGetRequest(url, id, _from) {
    $.ajax({
        url: url + id,
        type: "GET",
        headers: {
            "X-CSRF-TOKEN": $("#invoiceForm")
                .find('input[name="_token"]')
                .val(),
        },
        beforeSend: function () {
            showSpinner();
        },
        success: function (response) {
            if (_from == "delete-payment") {
                handleDeleteResponse(response, id);
            } else {
                //
            }
        },
        error: function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: "Error", layout: 2, message: message });
        },
        complete: function () {
            hideSpinner();
        },
    });
}

function handleDeleteResponse(response, id) {
    iziToast.success({
        title: "Success",
        layout: 2,
        message: response.message,
    });

    //Delete row from table
    $(".payment-total").html(_parseFix(response.data.paid_amount));
    $(".paid_amount").val(_parseFix(response.data.paid_amount_without_format));

    calulateBalance();

    $("#payments-table tr#" + id).remove();
}

/**
 * Event on
 * Customer or Party Selection
 * */
$(document).on("change", "#party_id", function () {
    var selectedData = $("#party_id").select2("data")[0]; // `data()` returns an array, take the first item
    // Access the `is_wholesale_customer` property
    if (!selectedData) {
        return true;
    }

    var customerType =
        selectedData.is_wholesale_customer == 1 ? "Wholesale" : "Retail";
    iziToast.success({
        title: "",
        layout: 3,
        message: `<b>${customerType} Customer Selected</b>`,
    });
});

/**
 * Store Resized Items Table
 * */
document.addEventListener("DOMContentLoaded", function () {
    const resizableDiv = document.getElementById("resizableDiv");

    // Restore size from localStorage
    const savedHeight = localStorage.getItem("resizableDivHeight");
    if (savedHeight) {
        resizableDiv.style.height = savedHeight;
    }

    // Save size on resize
    resizableDiv.addEventListener("mouseup", function () {
        const currentHeight = resizableDiv.offsetHeight + "px";
        localStorage.setItem("resizableDivHeight", currentHeight);
    });
});
// ─── CALCULATOR BUTTONS LOGIC ──────────────────────────────────────────
(function () {
    const display = document.getElementById("calc-display");
    const buttons = document.querySelectorAll(".calc-btn");

    buttons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const val = btn.getAttribute("data-value");

            // Clear
            if (btn.id === "calc-clear") {
                display.value = "";
                return;
            }

            // Backspace
            if (btn.id === "calc-backspace") {
                display.value = display.value.slice(0, -1);
                return;
            }

            if (btn.id === "calc-equals") {
                const expr = display.value;
                try {
                    // sanitize just in case
                    const sanitized = expr.replace(/[^0-9+\-*/.()]/g, "");
                    const result = Function(
                        `"use strict";return (${sanitized})`
                    )();
                    // show full expression = result
                    display.value = expr + " = " + result;
                } catch (e) {
                    display.value = "Error";
                }
                return;
            }

            // Digit / operator
            if (val) {
                display.value += val;
            }
        });
    });
})();

// Calculator Popup & Drag Logic (no localStorage)
(function () {
    const calcPopup = document.getElementById("calculator-popup");
    const calcToggle = document.getElementById("calculator-toggle");
    const calcClose = document.getElementById("calculator-close");
    const calcHeader = document.getElementById("calculator-header");
    let offsetX = 0,
        offsetY = 0,
        isDragging = false;

    // Position calculator under the icon
    function positionCalcUnderIcon() {
        const icon = calcToggle.getBoundingClientRect();
        calcPopup.style.left = icon.left + "px";
        calcPopup.style.top = icon.bottom + 5 + "px";
    }

    // Show/hide calculator
    calcToggle.addEventListener("click", () => {
        if (calcPopup.style.display === "block") {
            calcPopup.style.display = "none";
        } else {
            positionCalcUnderIcon();
            calcPopup.style.display = "block";
        }
    });

    // Close calculator
    calcClose.addEventListener("click", () => {
        calcPopup.style.display = "none";
    });

    // Start drag
    calcHeader.addEventListener("mousedown", (e) => {
        isDragging = true;
        const rect = calcPopup.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        document.body.style.userSelect = "none";
    });

    // During drag
    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        let left = e.clientX - offsetX;
        let top = e.clientY - offsetY;
        // clamp to viewport
        left = Math.max(
            0,
            Math.min(window.innerWidth - calcPopup.offsetWidth, left)
        );
        top = Math.max(
            0,
            Math.min(window.innerHeight - calcPopup.offsetHeight, top)
        );
        calcPopup.style.left = left + "px";
        calcPopup.style.top = top + "px";
    });

    // End drag
    document.addEventListener("mouseup", () => {
        if (isDragging) {
            isDragging = false;
            document.body.style.userSelect = "";
        }
    });

    // Ensure hidden by default
    calcPopup.style.display = "none";
})();

document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('load', function () {
        setTimeout(function () {
            let customerDisplayWindow = null;

            async function openCustomerDisplay() {
                const url = window.location.origin + "/sale/invoice/customer-display";

                if ("getScreenDetails" in window) {
                    try {
                        const details = await window.getScreenDetails();

                        // pick non-primary screen
                        const otherScreen = details.screens.find(s => !s.isPrimary);
                        const target = otherScreen || details.currentScreen;

                        const specs = `left=${target.availLeft},top=${target.availTop},width=${target.availWidth},height=${target.availHeight},resizable=1,scrollbars=0`;

                        customerDisplayWindow = window.open(url, "customer_display", specs);

                        if (customerDisplayWindow) {
                            customerDisplayWindow.focus();
                            setTimeout(() => {
                                sendWarehouseInfo();
                                updateCustomerDisplay();
                            }, 800);
                        }
                    } catch (e) {
                        console.warn("Error opening customer display:", e);
                    }
                } else {
                    customerDisplayWindow = window.open(
                        url,
                        "customer_display",
                        "width=800,height=600"
                    );

                    if (customerDisplayWindow) {
                        setTimeout(() => {
                            sendWarehouseInfo();
                            updateCustomerDisplay();
                        }, 800);
                    }
                }
            }



            function sendWarehouseInfo() {
                if (!customerDisplayWindow || customerDisplayWindow.closed) return;
                var currentWarehouseId = $('#warehouse_id').val() || '';
                var currentWarehouseName = $("#warehouse_id option:selected").text() || 'N/A';
                customerDisplayWindow.postMessage({
                    type: 'INITIAL_WAREHOUSE_INFO',
                    warehouseId: currentWarehouseId,
                    warehouseName: currentWarehouseName
                }, '*');
            }

            function updateCustomerDisplay() {
                if (!customerDisplayWindow || customerDisplayWindow.closed) {
                    openCustomerDisplay();
                    setTimeout(updateCustomerDisplay, 1000);
                    return;
                }

                const items = [];
                $('#invoiceItemsTable tbody tr').not('.default-row').each(function () {
                    const name = $(this).find('td:eq(0) label.form-label').text().trim();
                    const quantity = $(this).find('input[name^="quantity"]').val();
                    const unitPrice = $(this).find('input[name^="sale_price"]').val();
                    const total = $(this).find('input[name^="total"]').val();

                    if (name && quantity) {
                        items.push({ name, quantity, unitPrice, total });
                    }
                });

                const totals = {
                    subtotal: $('#totalPrice').text(),
                    tax: $('#totalTax').text(),
                    discount: $('#totalDiscount').text(),
                    grandTotal: $('input[name="grand_total"]').val()
                };

                try {
                    const ticker = $(".ticker-content").text() || '';

                    customerDisplayWindow.postMessage({
                        type: 'UPDATE_CUSTOMER_DISPLAY',
                        items,
                        totals,
                        ticker: ticker
                    }, '*');
                } catch (e) {
                    console.error('Error sending message to customer display:', e);
                    openCustomerDisplay();
                }
            }


            openCustomerDisplay();

            const observer = new MutationObserver(updateCustomerDisplay);
            const targetNode = document.getElementById('invoiceItemsTable');
            if (targetNode) {
                observer.observe(targetNode, { childList: true, subtree: true });
            }

            $(document).on('change', '#invoiceItemsTable input, input[name^="payment_amount"]', updateCustomerDisplay);

            window.addEventListener('message', function (event) {
                if (event.data.type === 'CUSTOMER_DISPLAY_READY') {
                    sendWarehouseInfo();
                    setTimeout(updateCustomerDisplay, 500);
                }
            });

            setTimeout(updateCustomerDisplay, 2000);

            setInterval(function () {
                if (customerDisplayWindow && customerDisplayWindow.closed) {
                    openCustomerDisplay();
                    setTimeout(updateCustomerDisplay, 1000);
                }
            }, 3000);

            // warehouse change listener
            $(document).on('change', '#warehouse_id', function () {
                var selectedWarehouseId = $(this).val();
                var selectedWarehouseName = $("#warehouse_id option:selected").text();
                if (customerDisplayWindow && !customerDisplayWindow.closed) {
                    customerDisplayWindow.postMessage({
                        type: 'WAREHOUSE_CHANGED',
                        warehouseId: selectedWarehouseId,
                        warehouseName: selectedWarehouseName
                    }, '*');
                }
            });
        }, 1000);
    });
    $(document).ready(function () {
        const countInput = $('input[name="count_id"]');
        const prefixInput = $('input[name="prefix_code"]');

        // Hook into any form reset or reload
        const originalReset = HTMLFormElement.prototype.reset;
        HTMLFormElement.prototype.reset = function () {
            originalReset.call(this);
            // restore locked values
            if (countInput.attr('data-locked')) {
                countInput.val(countInput.val());
            }
            if (prefixInput.attr('data-locked')) {
                prefixInput.val(prefixInput.val());
            }
        };

        // Also block any accidental blank assignment from scripts
        const originalVal = $.fn.val;
        $.fn.val = function (value) {
            if (arguments.length === 0) {
                return originalVal.call(this);
            }
            const name = $(this).attr('name');
            if ((name === 'count_id' || name === 'prefix_code') && $(this).attr('data-locked')) {
                return this; // skip writing
            }
            return originalVal.call(this, value);
        };
    });
});
