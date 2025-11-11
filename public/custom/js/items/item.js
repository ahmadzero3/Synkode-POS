let originalButtonText;

const isService = $("input[name='is_service']");

/**
 * Language
 */
const _lang = {
    batchBtnName: "Batch",
    serialBtnName: "Serial",
    enterSerialNumber: "Please Enter Serial Number!",
    productSelected: "Product Selected!",
    serviceSelected: "Service Selected!",
    offerSelected: "Offer Selected!",
};

$("#itemForm").on("submit", function (e) {
    e.preventDefault();

    const currentType = $('input[name="item_type_radio"]:checked').val();

    if (currentType === 'offers') {
        const name = $.trim($('input[name="name"]').val() || "");
        const salePrice = parseFloat($('input[name="sale_price"]').val() || 0);

        let items = [];
        try {
            items = JSON.parse($('input[name="offer_items_json"]').val() || "[]");
        } catch (err) {
            items = [];
        }

        // filter valid rows
        items = items.filter(r => r && r.item_id && Number(r.quantity) >= 1);

        if (!name) {
            iziToast.error({ title: 'Error', layout: 2, message: 'Name is required for Offer/Combo.' });
            return;
        }
        if (!salePrice || salePrice <= 0) {
            iziToast.error({ title: 'Error', layout: 2, message: 'Please enter a Sale Price for the Offer/Combo.' });
            return;
        }
        if (items.length < 2) {
            iziToast.error({ title: 'Error', layout: 2, message: 'Offer/Combo must contain at least 2 items.' });
            return;
        }

        // re-pack cleaned items (so back-end gets sane data)
        $('input[name="offer_items_json"]').val(JSON.stringify(items));
    }

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
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...');
}

function enableSubmitButton(form) {
    form.find('button[type="submit"]')
        .prop('disabled', false)
        .html(originalButtonText);
}

function beforeCallAjaxRequest(formObject) { disableSubmitButton(formObject); }
function afterCallAjaxResponse(formObject) { enableSubmitButton(formObject); }
function afterSeccessOfAjaxRequest(formObject) {
    formAdjustIfSaveOperation(formObject);
    pageRedirect(formObject);
}
function pageRedirect(formObject) {
    var redirectTo = '/item/list';
    setTimeout(function () { location.href = baseURL + redirectTo; }, 1000);
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
        headers: { 'X-CSRF-TOKEN': formArray.csrf },
        beforeSend: function () { if (typeof beforeCallAjaxRequest === 'function') beforeCallAjaxRequest(formArray.formObject); },
    });
    jqxhr.done(function (data) {
        iziToast.success({ title: 'Success', layout: 2, message: data.message });
        if (typeof afterSeccessOfAjaxRequest === 'function') afterSeccessOfAjaxRequest(formArray.formObject);
    });
    jqxhr.fail(function (response) {
        var message = response.responseJSON?.message || 'Request failed';
        iziToast.error({ title: 'Error', layout: 2, message: message });
    });
    jqxhr.always(function () { if (typeof afterCallAjaxResponse === 'function') afterCallAjaxResponse(formArray.formObject); });
}

function formAdjustIfSaveOperation(formObject) {
    const _method = formObject.find('input[name="_method"]').val();
    if (_method.toUpperCase() === 'POST') {
        var formId = formObject.attr("id");
        $("#" + formId)[0].reset();
    }
}

/** Select All */
$("#select_all").on("click", function () {
    var checkBox = $(this).prop("checked");
    $(".row-select").prop("checked", checkBox);
    $(".row-select").each(function () {
        var permissionClass = $(this).attr("id");
        $("." + permissionClass + "_p").prop("checked", checkBox);
    });
});

/** Group Checkbox operation */
$(".row-select").on("click", function () {
    var checkBox = $(this).prop("checked");
    var groupClassName = $(this).attr("id");
    $("." + groupClassName + "_p").each(function () {
        $(this).prop("checked", checkBox);
    });
});

/** Image Browse & Reset */
function loadImageBrowser(uploadedImage, accountFileInput, accountImageReset) {
    if (uploadedImage.length) {
        const avatarSrc = uploadedImage.attr("src");

        accountFileInput.on("change", function () {
            if (accountFileInput[0].files[0]) {
                uploadedImage.attr("src", window.URL.createObjectURL(accountFileInput[0].files[0]));
            }
        });

        accountImageReset.on("click", function () {
            accountFileInput[0].value = "";
            uploadedImage.attr("src", avatarSrc);
        });
    }
}

$(document).ready(function () {
    loadImageBrowser($("#uploaded-image-1"), $(".input-box-class-1"), $(".image-reset-class-1"));
    showButtonOfTracking();
    initOfferItemSearch();

    // Set initial visibility
    $("input[name='item_type_radio']:checked").trigger('change');
});

$(document).on('click', '.auto-generate-code', function () {
    $("input[name='item_code']").val(getRandomInt(1000000000, 9999999999));
});

$(document).on('change', 'input[name="tracking_type"]', function () {
    showButtonOfTracking();
});

function showButtonOfTracking() {
    var currentValue = $('input[name="tracking_type"]:checked').val();
    var btn = '';
    if (currentValue === 'batch') {
        btn += `<button class="btn btn-outline-primary trackBtn batchBtn" type="button">${_lang.batchBtnName}</button>`;
    } else if (currentValue === 'serial') {
        btn += `<button class="btn btn-outline-info trackBtn serialBtn" type="button">${_lang.serialBtnName}</button>`;
    }
    $(".trackBtn").remove();
    $("input[name='opening_quantity']").after(btn);
}

/** Product / Service / Offers toggle */
$("input[name='item_type_radio']").on("change", function () {
    const checkedValue = $(this).val();

    if (checkedValue === 'product') {
        iziToast.info({ title: 'Info', layout: 2, message: _lang.productSelected });
        $('.item-type-product').show();
        $('.item-type-offers').hide();
        $('.item-type-product-service').show();
        $('.purchase-field').show();
        isService.val(0);
    } else if (checkedValue === 'service') {
        iziToast.info({ title: 'Info', layout: 2, message: _lang.serviceSelected });
        $('.item-type-product').hide();
        $('.item-type-offers').hide();
        $('.item-type-product-service').show();
        $('.purchase-field').hide();
        isService.val(1);
    } else if (checkedValue === 'offers') {
        iziToast.info({ title: 'Info', layout: 2, message: _lang.offerSelected });
        $('.item-type-product').hide();
        $('.item-type-offers').show();
        $('.item-type-product-service').show();
        $('.purchase-field').hide();
        isService.val(0);
    }
});

/** Avoid form submit on Enter */
$('#itemForm input[name="conversion_rate"]').on('keypress', function (e) {
    if (e.keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

/** Calculate Sale Price using Sale Profit Margin */
const saleProfitMarginInput = $('input[name="profit_margin"]');
const salePriceInput = $('input[name="sale_price"]');
const purchasePriceInput = $('input[name="purchase_price"]');
const taxSelect = $('select[name="tax_id"]');

saleProfitMarginInput.on('input', function () {
    const profitMargin = parseFloat($(this).val()) || 0;
    const purchasePrice = parseFloat(purchasePriceInput.val()) || 0;
    const taxRate = parseFloat(taxSelect.find(':selected').data('tax-rate')) || 0;

    if (isNaN(profitMargin) || isNaN(purchasePrice) || purchasePrice <= 0) {
        salePriceInput.val('');
        return;
    }
    if (profitMargin > 100) {
        alert('Profit margin must be less or equal to 100%');
        saleProfitMarginInput.val('');
        salePriceInput.val('');
        return;
    }

    let salePriceBeforeTax = purchasePrice * (1 + profitMargin / 100);
    let salePrice = salePriceBeforeTax * (1 + taxRate / 100);
    salePriceInput.val(_parseFix(salePrice));
});

salePriceInput.on('input', function () {
    const salePrice = parseFloat($(this).val()) || 0;
    const purchasePrice = parseFloat(purchasePriceInput.val()) || 0;
    const taxRate = parseFloat(taxSelect.find(':selected').data('tax-rate')) || 0;

    if (isNaN(salePrice) || isNaN(purchasePrice) || purchasePrice <= 0) {
        saleProfitMarginInput.val('');
        return;
    }

    const salePriceBeforeTax = salePrice / (1 + taxRate / 100);
    const profitMargin = ((salePriceBeforeTax - purchasePrice) / purchasePrice) * 100;
    saleProfitMarginInput.val(_parseFix(profitMargin));
});

taxSelect.on('change', function () { saleProfitMarginInput.trigger('input'); });
purchasePriceInput.on('input', function () { saleProfitMarginInput.trigger('input'); });

function initOfferItemSearch() {
    const searchBox = $("#search_item");
    const tableBody = $("#offersItemsTable tbody");
    let rowIndex = 0;

    if (!searchBox.length) return;

    searchBox.autocomplete({
        minLength: 1,
        source: function (request, response) {
            $.ajax({
                url: baseURL + "/item/ajax/get-list",
                dataType: "json",
                data: { search: request.term },
                success: function (data) { response(data); }
            });
        },
        focus: function (event, ui) {
            searchBox.val(ui.item.name);
            return false;
        },
        select: function (event, ui) {
            addOfferRow(ui.item);
            searchBox.val("");
            return false;
        }
    }).autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append(`<div>${item.name}</div>`)
            .appendTo(ul);
    };

    function addOfferRow(item) {
        tableBody.find(".default-row").closest("tr").remove();

        const newRow = $(`
            <tr id="offer-row-${rowIndex}">
                <td>${item.name}<input type="hidden" name="offer_items[${rowIndex}][item_id]" value="${item.id}"></td>
                <td><input type="number" name="offer_items[${rowIndex}][quantity]" class="form-control" value="1" min="1"></td>
                <td><button type="button" class="btn btn-outline-danger remove-offer-item"><i class="bx bx-trash me-0"></i></button></td>
            </tr>
        `);

        tableBody.prepend(newRow);
        rowIndex++;
        updateOfferItemsJson();
    }

    function updateOfferItemsJson() {
        const offerItems = [];
        tableBody.find("tr").each(function () {
            const itemId = $(this).find('input[name*="[item_id]"]').val();
            const qty = $(this).find('input[name*="[quantity]"]').val();
            if (itemId) {
                offerItems.push({ item_id: itemId, quantity: qty });
            }
        });
        $('input[name="offer_items_json"]').val(JSON.stringify(offerItems));
    }

    $(document).on("click", ".remove-offer-item", function () {
        $(this).closest("tr").remove();
        updateOfferItemsJson();

        if (tableBody.find("tr").length === 0) {
            tableBody.html(`<tr><td colspan="3" class="text-center fw-light fst-italic default-row">No items are added yet!!</td></tr>`);
        }
    });

    $(document).on("change", 'input[name*="[quantity]"]', function () {
        updateOfferItemsJson();
    });

    $("#itemForm").on("submit", function () {
        updateOfferItemsJson();
    });
}
