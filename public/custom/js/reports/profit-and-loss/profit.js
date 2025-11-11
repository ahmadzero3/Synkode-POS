$(function () {
    "use strict";

    let originalButtonText;

    const tableId = $("#reportTable");
    const tableIdItemWise = $("#itemWiseReportTable");
    const tableIdInvoiceWise = $("#invoiceWiseReportTable");
    const tableIdBrandWise = $("#brandWiseReportTable");
    const tableIdCategoryWise = $("#categoryWiseReportTable");
    const tableIdCustomerWise = $("#customerWiseReportTable");

    /**
     * Language
     * */
    const _lang = {
        total: "Total",
        noRecordsFound: "No Records Found!!",
    };

    $(
        "#reportForm, #profitByItemReportForm, #profitByInvoiceReportForm, #profitByBrandReportForm, #profitByCategoryReportForm, #profitByCustomerReportForm"
    ).on("submit", function (e) {
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
        originalButtonText = form.find('button[type="submit"]').text();
        form.find('button[type="submit"]')
            .prop("disabled", true)
            .html(
                '  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...'
            );
    }

    function enableSubmitButton(form) {
        form.find('button[type="submit"]')
            .prop("disabled", false)
            .html(originalButtonText);
    }

    function beforeCallAjaxRequest(formObject) {
        disableSubmitButton(formObject);
        showSpinner();
    }
    function afterCallAjaxResponse(formObject) {
        enableSubmitButton(formObject);
        hideSpinner();
    }
    function afterSeccessOfAjaxRequest(formArray, response) {
        formAdjustIfSaveOperation(formArray, response);
    }
    function afterFailOfAjaxRequest(formObject) {
        showNoRecordsMessageOnTableBody();
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
        jqxhr.done(function (response) {
            // Actions to be performed after response from the AJAX request
            if (typeof afterSeccessOfAjaxRequest === "function") {
                afterSeccessOfAjaxRequest(formArray, response);
            }
        });
        jqxhr.fail(function (response) {
            var message = response.responseJSON.message;
            iziToast.error({ title: "Error", layout: 2, message: message });
            if (typeof afterFailOfAjaxRequest === "function") {
                afterFailOfAjaxRequest(formArray.formObject);
            }
        });
        jqxhr.always(function () {
            // Actions to be performed after the AJAX request is completed, regardless of success or failure
            if (typeof afterCallAjaxResponse === "function") {
                afterCallAjaxResponse(formArray.formObject);
            }
        });
    }

    function formAdjustIfSaveOperation(formArray, response) {
        if (formArray.formId === "reportForm") {
            //Summary Report
            showSummaryOfReport(response);
        } else if (formArray.formId === "profitByInvoiceReportForm") {
            //Invoice Wise Report
            showInvoiceWiseReport(response);
        } else if (formArray.formId === "profitByItemReportForm") {
            //Item Wise Report
            showItemWiseReport(response);
        } else if (formArray.formId === "profitByBrandReportForm") {
            //Brand Wise Report
            showBrandWiseReport(response);
        } else if (formArray.formId === "profitByCategoryReportForm") {
            //Category Wise Report
            showCategoryWiseReport(response);
        } else if (formArray.formId === "profitByCustomerReportForm") {
            //Customer Wise Report
            showCustomerWiseReport(response);
        } else {
            //Error
            iziToast.error({
                title: "Error",
                layout: 2,
                message: "Invalid form submission!",
            });
        }
    }

    /**
     * Show Invoice Wise Report
     */
    function showInvoiceWiseReport(response) {
        var tableBody = tableIdInvoiceWise.find("tbody");
        var id = 1;
        var tr = "";

        var totalSaleAmount = 0;
        var totalPurchaseCost = 0;
        var totalTaxAmount = 0;
        var totalGrossProfit = 0;
        var totalNetProfit = 0;

        if (response.data && response.data.length > 0) {
            $.each(response.data, function (index, item) {
                totalSaleAmount += parseFloat(item.sale_amount);
                totalPurchaseCost += parseFloat(item.purchase_cost);
                totalTaxAmount += parseFloat(item.tax_amount);
                totalGrossProfit += parseFloat(item.gross_profit);
                totalNetProfit += parseFloat(item.net_profit);

                tr += `
                    <tr>
                        <td>${id++}</td>
                        <td>${item.sale_date}</td>
                        <td>${item.sale_code}</td>
                        <td>${item.customer_name}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.sale_amount
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.purchase_cost
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.tax_amount
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.gross_profit
                        )}</td>
                        <td class="text-end ${
                            item.class_color
                        }" data-tableexport-celltype="number">${_formatNumber(
                    item.net_profit
                )}</td>
                    </tr>
                `;
            });

            tr += `
                <tr class="fw-bold">
                    <td colspan="4" class="text-end tfoot-first-td">${
                        _lang.total
                    }</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalSaleAmount
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalPurchaseCost
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalTaxAmount
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalGrossProfit
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalNetProfit
                    )}</td>
                </tr>
            `;
        } else {
            tr = `<tr><td colspan="9" class="text-center">${_lang.noRecordsFound}</td></tr>`;
        }

        tableBody.empty();
        tableBody.append(tr);
    }

    function showCustomerWiseReport(response) {
        var tableBody = tableIdCustomerWise.find("tbody");
        var id = 1;
        var tr = "";

        var totalSaleAmount = 0;
        var totalPurchaseCost = 0;
        var totalTaxAmount = 0;
        var totalGrossProfit = 0;
        var totalNetProfit = 0;

        if (response.data && response.data.length > 0) {
            $.each(response.data, function (index, item) {
                totalSaleAmount += parseFloat(item.sale_amount);
                totalPurchaseCost += parseFloat(item.purchase_cost);
                totalTaxAmount += parseFloat(item.tax_amount);
                totalGrossProfit += parseFloat(item.gross_profit);
                totalNetProfit += parseFloat(item.net_profit);

                tr += `
                    <tr>
                        <td>${id++}</td>
                        <td>${item.customer_name}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.sale_amount
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.purchase_cost
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.tax_amount
                        )}</td>
                        <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                            item.gross_profit
                        )}</td>
                        <td class="text-end ${
                            item.class_color
                        }" data-tableexport-celltype="number">${_formatNumber(
                    item.net_profit
                )}</td>
                    </tr>
                `;
            });

            tr += `
                <tr class="fw-bold">
                    <td colspan="2" class="text-end tfoot-first-td">${
                        _lang.total
                    }</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalSaleAmount
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalPurchaseCost
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalTaxAmount
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalGrossProfit
                    )}</td>
                    <td class="text-end" data-tableexport-celltype="number">${_formatNumber(
                        totalNetProfit
                    )}</td>
                </tr>
            `;
        } else {
            tr = `<tr><td colspan="7" class="text-center">${_lang.noRecordsFound}</td></tr>`;
        }

        tableBody.empty();
        tableBody.append(tr);
    }

    function showBrandWiseReport(response) {
        var tableBody = tableIdBrandWise.find("tbody");

        var id = 1;
        var tr = "";

        var totalQuantity = 0;
        var totalSaleSum = 0;
        var totalPurchaseSum = 0;
        var totalTaxAmount = 0;
        var totalGrossProfit = 0;
        var totalNetProfit = 0;

        if (response.data && response.data.length > 0) {
            $.each(response.data, function (index, item) {
                totalQuantity += parseFloat(item.total_quantity);
                totalSaleSum += parseFloat(item.total_sale_amount);
                totalPurchaseSum += parseFloat(item.total_purchase_cost);
                totalTaxAmount += parseFloat(item.total_tax_amount || 0);
                totalGrossProfit += parseFloat(item.total_gross_profit || 0);
                totalNetProfit += parseFloat(item.total_net_profit || 0);

                tr += `
                    <tr>
                        <td>${id++}</td>
                        <td>${item.brand_name}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_quantity
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_sale_amount
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_purchase_cost
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_tax_amount
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_gross_profit
                        )}</td>
                        <td class='text-end ${
                            item.class_color
                        }' data-tableexport-celltype="number">${_formatNumber(
                    item.total_net_profit
                )}</td>
                    </tr>
                `;
            });

            tr += `
                <tr class='fw-bold'>
                    <td colspan='2' class='text-end tfoot-first-td'>${
                        _lang.total
                    }</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalQuantity
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalSaleSum
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalPurchaseSum
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalTaxAmount
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalGrossProfit
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalNetProfit
                    )}</td>
                </tr>
            `;
        } else {
            tr = `<tr><td colspan="8" class="text-center">${_lang.noRecordsFound}</td></tr>`;
        }

        // Clear existing rows:
        tableBody.empty();
        tableBody.append(tr);
    }

    function showCategoryWiseReport(response) {
        var tableBody = tableIdCategoryWise.find("tbody");

        var id = 1;
        var tr = "";

        var totalQuantity = 0;
        var totalSaleSum = 0;
        var totalPurchaseSum = 0;
        var totalTaxAmount = 0;
        var totalGrossProfit = 0;
        var totalNetProfit = 0;

        if (response.data && response.data.length > 0) {
            $.each(response.data, function (index, item) {
                totalQuantity += parseFloat(item.total_quantity);
                totalSaleSum += parseFloat(item.total_sale_amount);
                totalPurchaseSum += parseFloat(item.total_purchase_cost);
                totalTaxAmount += parseFloat(item.total_tax_amount || 0);
                totalGrossProfit += parseFloat(item.total_gross_profit || 0);
                totalNetProfit += parseFloat(item.total_net_profit || 0);

                tr += `
                    <tr>
                        <td>${id++}</td>
                        <td>${item.category_name}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_quantity
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_sale_amount
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_purchase_cost
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_tax_amount
                        )}</td>
                        <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                            item.total_gross_profit
                        )}</td>
                        <td class='text-end ${
                            item.class_color
                        }' data-tableexport-celltype="number">${_formatNumber(
                    item.total_net_profit
                )}</td>
                    </tr>
                `;
            });

            tr += `
                <tr class='fw-bold'>
                    <td colspan='2' class='text-end tfoot-first-td'>${
                        _lang.total
                    }</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalQuantity
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalSaleSum
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalPurchaseSum
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalTaxAmount
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalGrossProfit
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                        totalNetProfit
                    )}</td>
                </tr>
            `;
        } else {
            tr = `<tr><td colspan="8" class="text-center">${_lang.noRecordsFound}</td></tr>`;
        }

        // Clear existing rows:
        tableBody.empty();
        tableBody.append(tr);
    }

    function showItemWiseReport(response) {
        var tableBody = tableIdItemWise.find("tbody");

        var id = 1;
        var tr = "";

        var totalQuantity = parseFloat(0);
        var totalDiscountAmount = parseFloat(0);
        var totalGrossSum = parseFloat(0);
        var totalNetSum = parseFloat(0);
        var totalSaleSum = parseFloat(0);
        var totalPurchaseSum = parseFloat(0);

        $.each(response.data, function (index, item) {
            totalQuantity += parseFloat(item.quantity);
            totalDiscountAmount += parseFloat(item.discount_amount);
            totalSaleSum += parseFloat(item.sale_total);
            totalPurchaseSum += parseFloat(item.purchase_total);
            totalGrossSum += parseFloat(item.gross_profit);
            totalNetSum += parseFloat(item.net_profit);

            tr += `
                <tr>
                    <td>${id++}</td>
                    <td>${item.item_name}</td>
                    <td>${item.brand_name}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.avg_sale_price
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.quantity
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.avg_purchase_price
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.sale_total
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.purchase_total
                    )}</td>
                    <td class='text-end' data-tableexport-celltype="number" >${_formatNumber(
                        item.gross_profit
                    )}</td>
                    <td class='text-end ${
                        item.class_color
                    }' data-tableexport-celltype="number" >${_formatNumber(
                item.net_profit
            )}</td>
                </tr>
            `;
        });

        tr += `
            <tr class='fw-bold'>
                <td colspan='4' class='text-end tfoot-first-td'>${
                    _lang.total
                }</td>
                <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                    totalQuantity
                )}</td>
                <td class='text-end' data-tableexport-celltype="number"></td>
                <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                    totalSaleSum
                )}</td>
                <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                    totalPurchaseSum
                )}</td>
                <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                    totalGrossSum
                )}</td>
                <td class='text-end' data-tableexport-celltype="number">${_formatNumber(
                    totalNetSum
                )}</td>
            </tr>
        `;

        // Clear existing rows:
        tableBody.empty();
        tableBody.append(tr);
    }

    function showSummaryOfReport(response) {
        var tableBody = tableId.find("tbody");
        var totalQuantity = parseFloat(0);
        let _values = response.data;

        $("#sale_without_tax").text(_formatNumber(_values.sale_without_tax));
        $("#sale_return_without_tax").text(
            _formatNumber(_values.sale_return_without_tax)
        );
        $("#purchase_without_tax").text(
            _formatNumber(_values.purchase_without_tax)
        );
        $("#purchase_return_without_tax").text(
            _formatNumber(_values.purchase_return_without_tax)
        );
        $("#gross_profit").text(_formatNumber(_values.gross_profit));
        $("#indirect_expense_without_tax").text(
            _formatNumber(_values.indirect_expense_without_tax)
        );
        $("#shipping_charge").text(_formatNumber(_values.shipping_charge));
        $("#net_profit").text(_formatNumber(_values.net_profit));

        //Gross Profit Calculation
        $("#sale_gross_profit").text(_formatNumber(_values.sale_gross_profit));

        //Net Profit Calculation
        $("#sale_net_profit").text(_formatNumber(_values.sale_net_profit));
    }

    function showNoRecordsMessageOnTableBody() {
        //
    }
    function columnCountWithoutDNoneClass(minusCount) {
        //
    }

    /**
     *
     * Table Exporter
     * PDF, SpreadSheet
     * */
    $(document).on("click", ".generate_pdf", function () {
        var tableId = $(this).data("table-id");
        $("#" + tableId).tableExport({
            type: "pdf",
            escape: "false",
            fileName: "Profit-and-Loss-Report",
        });
    });

    $(document).on("click", ".generate_excel", function () {
        var tableId = $(this).data("table-id");
        $("#" + tableId).tableExport({
            formats: ["xlsx"],
            fileName: "Profit-and-Loss-Report",
            xlsx: {
                onCellFormat: function (cell, e) {
                    if (typeof e.value === "string") {
                        var numValue = parseFloat(e.value.replace(/,/g, ""));
                        if (!isNaN(numValue)) {
                            return numValue;
                        }
                    }
                    return e.value;
                },
            },
        });
    });
}); //main function
