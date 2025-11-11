"use strict";

async function confirmAction(title = "Are you sure?") {
    return new Promise((resolve) => {
        /* Confirm before Proceeding */
        swal({
            title: title,
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            // If user confirms, resolve with true; otherwise, resolve with false.
            resolve(willDelete);
        });
    });
}

/**
 * Load Parties from PartyController
 * Ajax Operation for select2
 * */
function initSelect2Parties() {
    $('.party-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/party/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                    party_type: $(".party-ajax").data('party-type'),
                    page: params.page || 1
                };
                return query;
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text,
                            mobile: item.mobile,
                            is_wholesale_customer: item.is_wholesale_customer,
                            to_pay: item.to_pay,
                            to_receive: item.to_receive,
                            currency_id: item.currency_id,
                        };
                    }),
                    pagination: {
                        more: data.hasMore // your backend should return hasMore: true/false
                    }
                };
            }
        },
        templateResult: function (data) {
            if (!data.id) {
                return data.text; // Placeholder
            }

            var bottomText = '';
            var balanceText = '';
            var toPay = _parseFix(data.to_pay);
            var toReceive = _parseFix(data.to_receive);

            if (toPay > toReceive) {
                balanceText = `<i class="fadeIn fs-3 text-danger bx bx-right-top-arrow-circle"></i> ${toPay}`;
            } else {
                balanceText = `<i class="fadeIn fs-3 text-success bx bx-left-down-arrow-circle"></i> ${toReceive}`;
            }

            if ($(".party-ajax").data('party-type') == 'customer') {
                bottomText += data.is_wholesale_customer ? 'Wholesaler ' : 'Retailer ';
            }

            // Customize the dropdown item display
            return $(
                `<div>
                    <span class='fs-4'>${data.text}</span><br>
                    ${balanceText}, <small class="">${bottomText + '<i class="fadeIn text-primary bx bx-mobile"></i> ' + (data.mobile ? data.mobile : '-')}</small>
                </div>`
            );
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text; // Placeholder
            }
            // Customize the selected item display
            return $(
                `<span>${data.text} <small class="text-muted">(${data.mobile ?? '-'})</small></span>`
            );
        },
        escapeMarkup: function (markup) {
            return markup; // Allow HTML rendering
        }
    });
}

/**
 * Initialize Select2 for Invoice
 * Ajax Operation for select2
 * */
function initSelect2Invoice() {
    $('.invoice-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/sale/invoice/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                // Select2 sends params.page, default to 1 if not set
                var page = params.page || 1;
                var query = {
                    search: params.term,
                    page: page
                };
                return query;
            },
            processResults: function (data, params) {
                console.log('processResults fired in .invoice-ajax!');
                // params.page is the one sent to the server, default to 1
                params.page = params.page || 1;

                return {
                    results: data.results.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text,
                            party_name: item.party_name,
                        };
                    }),
                    pagination: {
                        more: data.hasMore // your backend should return hasMore: true/false
                    }
                };
            }
        },
        templateResult: function (data) {
            if (!data.id) {
                return data.text; // Placeholder
            }

            var partyName = data.party_name ? `<div class="text-muted small mt-1">${data.party_name}</div>` : '';

            // Customize the dropdown item display
            return $(
                `<div>
                    <span class='fs-4'>${data.text}</span> ${partyName}<br>
                </div>`
            );
        },
        templateSelection: function (data) {
            if (!data.id) {
                return data.text; // Placeholder
            }
            // Customize the selected item display
            return $(
                `<span>${data.text}</span>`
            );
        },
        escapeMarkup: function (markup) {
            return markup; // Allow HTML rendering
        }
    });
}

function initSelect2ItemList(modalName = null) {
    $('.item-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        dropdownParent: modalName,//For pop up modals
        ajax: {
            url: $('#base_url').val() + '/item/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term || '',
                    category_id: $('#item_category_id').val() || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.hasMore
                    }
                };
            }
        }
    });
}

function initSelect2PaymentType(options = {}) {
    $('.payment-type-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/payment-type/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
        ...options //its a valid code, which is called spread operator
    });
}





function initSelect2ItemBatchList() {
    $('.item-batch-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/item/batch/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                    item_id: getSelectedItemId(),
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2ItemSerialList() {
    $('.item-serial-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/item/serial/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                    item_id: getSelectedItemId(),
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2WarehouseList() {
    $('.warehouse-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/warehouse/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2ExpenseItemsList() {
    $('.expense-item-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/expense/expense-items-master/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2BrandList() {
    $('.brand-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/item/brand/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2ItemCategoryList() {
    $('.item-category-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        cache: true,
        ajax: {
            url: $('#base_url').val() + '/item/category/select2/ajax/get-list',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var query = {
                    search: params.term,
                };
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        },
    });
}

function initSelect2Cashiers() {
    $('.user-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        placeholder: 'Select Cashier',
        minimumInputLength: 0,
        ajax: {
            url: $('#base_url').val() + '/register/user/ajax/cashiers',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        }
    });
}

function initSelect2SessionUsers() {
    $('.user-ajax').select2({
        theme: 'bootstrap-5',
        allowClear: true,
        placeholder: 'Select User',
        minimumInputLength: 0,
        ajax: {
            url: $('#base_url').val() + '/session/user/ajax/all-users',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            }
        }
    });
}

$(document).ready(function ($) {
    //Party
    initSelect2Parties();

    //Invoice
    initSelect2Invoice();

    //Payment Types
    initSelect2PaymentType();

    //Item Batch Select2
    initSelect2ItemBatchList();

    //Item Serial Select2
    initSelect2ItemSerialList();

    //Item Serial Select2
    initSelect2WarehouseList();

    //Expnse Item Master
    initSelect2ExpenseItemsList();

    //Brand Master
    initSelect2BrandList();

    //Item Master
    initSelect2ItemList();

    //Item Category Master
    initSelect2ItemCategoryList();

    //Item Cashier Master
    initSelect2Cashiers();

    //Session Users Master (All users except superadmin)
    initSelect2SessionUsers();

});

function getSelectedItemId() {
    if ($("#item_id")) {
        if ($("#item_id").val() != '') {
            return $("#item_id").val();
        }
    }
    return '';
}
