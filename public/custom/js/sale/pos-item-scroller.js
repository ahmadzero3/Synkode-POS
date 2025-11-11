"use strict";

let currentPage = 0; // Initialize the page number
let isLoading = false; // Track loading state
let startFromFirst = 0;

// Infinite scroll event listener
/*window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight && !isLoading) {
        isLoading = true;
        loadMoreItems(); // Load more items when the user reaches the bottom
    }
});*/

$("#loadMoreBtn").on("click", function () {
    loadMoreItems();
});
$("#item_category_id, #item_brand_id, #warehouse_id, #party_id").on(
    "change",
    function () {
        currentPage = 0;
        startFromFirst = 0;
        loadMoreItems();
    }
);

function loadMoreItems() {
    currentPage++;
    $.ajax({
        url: baseURL + "/item/ajax/pos/item-grid/get-list",
        method: "GET",
        data: {
            search: $("#search_item").val(),
            page: currentPage,
            item_category_id: $("#item_category_id").val(),
            item_brand_id: $("#item_brand_id").val(),
            warehouse_id: $("#warehouse_id").val(),
            party_id: $("#party_id").val(),
            sort: $("#sorting_preference").val(),
        },
        beforeSend: function () {
            showLoadingMessage();
        },
        success: function (response) {
            if (response.length > 0) {
                var jsonObject = response;

                if (startFromFirst == 0) {
                    startFromFirst++;
                    $("#itemsGrid").html("");
                }

                jsonObject.forEach((item) => {
                    appendItemToGrid(item);
                });

                if ($("#sorting_preference").val() === "manual_sorting") {
                    applyManualOrderFromInput();
                }

                hideLoadingMessage();
            } else {
                if (startFromFirst == 0) {
                    $("#itemsGrid").html("");
                }
                noMoreData();
            }
            isLoading = false;
        },
        error: function () {
            isLoading = false;
        },
    });
}

// This helper function applies manual sorting order from the input
function applyManualOrderFromInput() {
    try {
        const inputValue = document.getElementById("manual_order_input").value;
        let manualOrder = JSON.parse(inputValue);

        // If still a string after first parse, parse again
        if (typeof manualOrder === "string") {
            manualOrder = JSON.parse(manualOrder);
        }

        if (Array.isArray(manualOrder)) {
            const itemsGrid = document.getElementById("itemsGrid");

            manualOrder.forEach((id) => {
                const el = itemsGrid.querySelector(`[data-id="${id}"]`);
                if (el) {
                    itemsGrid.appendChild(el); // re-append to reorder
                }
            });
        } else {
            console.warn(
                "manualOrder is not an array after double parse:",
                manualOrder
            );
        }
    } catch (e) {
        console.warn("Failed to apply manual order:", e);
    }
}

function noMoreData() {
    loadMoreBtn.textContent = "No More Data";
    loadMoreBtn.disabled = true;
    hideSpinner();
}

function showLoadingMessage() {
    const loadMoreBtn = document.getElementById("loadMoreBtn");
    loadMoreBtn.textContent = "Loading...";
    loadMoreBtn.disabled = true;
    showSpinner();
}

function hideLoadingMessage() {
    loadMoreBtn.textContent = "Load More";
    loadMoreBtn.disabled = false;
    hideSpinner();
}

function appendItemToGrid(item) {
    const image_path = `${baseURL}/item/getimage/thumbnail/${item.image_path}`;

    const allowNegative = window.allowNegativeStockBilling === true || window.allowNegativeStockBilling === "true";

    const isDisabled = (!allowNegative && item.current_stock <= 0) ? "disabled" : "";
    const emptyStockHtml = (!allowNegative && item.current_stock <= 0)
        ? `<div class="empty-stock-overlay">
               <i class="bx bx-x-circle"></i>
               <span>Empty Stock</span>
           </div>`
        : "";

    const showTag =
        window.toggleSwitchActive &&
        item.current_stock > 0 &&
        Array.isArray(window.trendingIds) &&
        window.trendingIds.includes(item.id);

    const tagHtml = showTag
        ? `<div class="item-tag position-absolute top-0 end-0 m-2 badge bg-primary">TOP</div>`
        : "";

    const itemHtml = `
        <div class="col" data-date="${item.created_at}" data-id="${item.id}">
            <div class="card h-100 item-card border ${isDisabled} position-relative">
                ${tagHtml}
                <div class="item-image">
                    <img src="${image_path}" class="card-img-top" alt="${item.name}">
                    <span class="item-quantity">Qty: ${_parseQuantity(item.current_stock)}</span>
                </div>
                ${emptyStockHtml}
                <div class="card-body">
                    <h6 class="card-title">${item.name}</h6>
                    <p class="card-text item-price">${_parseFix(item.sale_price)}</p>
                </div>
                <div class="add-item position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none justify-content-center align-items-center rounded">
                    <button class="btn btn-primary" type="button"
                            onclick='addItemToGrid(${JSON.stringify(item)})'
                            ${isDisabled}>+</button>
                </div>
            </div>
        </div>
    `;

    if (startFromFirst === 0) {
        startFromFirst++;
        $("#itemsGrid").html("");
    }
    $("#itemsGrid").append(itemHtml);
}

function addItemToGrid(item) {
    var dataObject = {
        warehouse_id: item.warehouse_id,
        id: item.id,
        name: item.name,
        tracking_type: item.tracking_type,
        description: item.description,
        sale_price: item.sale_price,
        is_sale_price_with_tax: item.is_sale_price_with_tax,
        tax_id: item.tax_id,
        quantity: _parseQuantity(item.quantity),
        taxList: item.taxList,
        unitList: item.unitList,
        base_unit_id: item.base_unit_id,
        secondary_unit_id: item.secondary_unit_id,
        selected_unit_id: item.selected_unit_id,
        conversion_rate: item.conversion_rate,
        sale_price_discount: item.sale_price_discount,
        discount_type: item.sale_price_discount_type,
        discount_amount: item.discount_amount,
        total_price_after_discount: item.total_price_after_discount,
        tax_amount: item.tax_amount,
        total_price: item.total_price,
        serial_numbers:
            item.tracking_type === "serial"
                ? JSON.stringify(item.serial_numbers)
                : "",
        batch_no: item.tracking_type === "batch" ? item.batch_no : "",
        mfg_date: item.tracking_type === "batch" ? item.mfg_date : "",
        exp_date: item.tracking_type === "batch" ? item.exp_date : "",
        mrp: item.mrp,

        model_no: item.tracking_type === "batch" ? item.model_no : "",
        color: item.tracking_type === "batch" ? item.color : "",
        size: item.tracking_type === "batch" ? item.size : "",
    };
    addRowToInvoiceItemsTable(dataObject, false);

    const posSound = document.getElementById("pos-sound");
    if (posSound) {
        posSound.currentTime = 0;
        posSound
            .play()
            .catch((error) => console.log("Audio play failed:", error));
    }
}

jQuery(document).ready(function ($) {
    loadMoreItems();
});

document.addEventListener("DOMContentLoaded", () => {
    const calc = document.getElementById("calculator-toggle");
    calc.classList.add("pulse");
    // Optionally remove after a few seconds:
    setTimeout(() => calc.classList.remove("pulse"), 10000);
});
