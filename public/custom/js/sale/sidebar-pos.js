document.addEventListener("DOMContentLoaded", function () {
    var sidebar = document.getElementById("sidebar-pos");
    var closeBtn = document.getElementById("sidebar-pos-close");
    var openBtn = document.querySelector(".btn.btn-primary.rounded-circle");
    var overlay = document.getElementById("sidebar-pos-overlay");

    if (openBtn) {
        openBtn.addEventListener("click", function () {
            sidebar.classList.add("open");
            if (overlay) overlay.classList.add("active");
            if (overlay) overlay.style.display = "block";
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            sidebar.classList.remove("open");
            if (overlay) overlay.classList.remove("active");
            if (overlay) overlay.style.display = "none";
        });
    }

    if (overlay) {
        overlay.addEventListener("click", function () {
            sidebar.classList.remove("open");
            overlay.classList.remove("active");
            overlay.style.display = "none";
        });
    }

    // Handle Finish button click
    document.querySelectorAll(".sidebar-pos-action-btn.finish-invoice-btn").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            var saleId = this.getAttribute("data-sale-id");
            if (!saleId) return;

            fetch("/sale/invoice/update-status", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
                body: JSON.stringify({ sale_id: saleId, status: "finished" }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        iziToast.success({
                            title: "Success",
                            message: "Invoice status updated successfully!",
                            position: "topRight",
                            timeout: 2000,
                            onClosed: function () {
                                location.reload();
                            },
                        });
                    } else {
                        iziToast.error({
                            title: "Error",
                            message: "Failed to update invoice status.",
                            position: "topRight",
                        });
                    }
                })
                .catch(() => {
                    iziToast.error({
                        title: "Error",
                        message: "Error updating invoice status.",
                        position: "topRight",
                    });
                });
        });
    });

    // Handle Delete button click
    document.querySelectorAll('.sidebar-pos-action-btn[title="Delete"]').forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            var saleId = this.getAttribute("data-sale-id");
            if (!saleId) return;

            iziToast.question({
                timeout: false,
                close: false,
                overlay: true,
                displayMode: "once",
                zindex: 10000,
                title: "Confirm Deletion",
                message: "Are you sure you want to delete this invoice permanently?",
                position: "center",
                buttons: [
                    [
                        "<button><b>Yes</b></button>",
                        function (instance, toast) {
                            instance.hide({ transitionOut: "fadeOut" }, toast, "button");
                            fetch("/sale/invoice/delete", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                                },
                                body: JSON.stringify({ sale_id: saleId }),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.success) {
                                        iziToast.success({
                                            title: "Deleted",
                                            message: "Invoice deleted successfully!",
                                            position: "topRight",
                                            timeout: 2000,
                                            onClosed: function () {
                                                location.reload();
                                            },
                                        });
                                    } else {
                                        iziToast.error({
                                            title: "Error",
                                            message: "Failed to delete invoice.",
                                            position: "topRight",
                                        });
                                    }
                                })
                                .catch(() => {
                                    iziToast.error({
                                        title: "Error",
                                        message: "Error deleting invoice.",
                                        position: "topRight",
                                    });
                                });
                        },
                        true,
                    ],
                    [
                        "<button>No</button>",
                        function (instance, toast) {
                            instance.hide({ transitionOut: "fadeOut" }, toast, "button");
                        },
                    ],
                ],
            });
        });
    });

    // Handle Return Invoice (previous inline script moved here)
    document.querySelectorAll('.sidebar-pos-action-btn[title="Return"]').forEach(function (button) {
        button.addEventListener("click", function () {
            const saleId = this.getAttribute("data-id");
            if (!saleId) return;

            fetch(`/api/sale/${saleId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data && data.success) {
                        const countInput = document.querySelector('input[name="count_id"]');
                        const prefixInput = document.querySelector('input[name="prefix_code"]');

                        if (countInput) countInput.value = data.sale.count_id ?? "";
                        if (prefixInput) prefixInput.value = data.sale.prefix_code ?? "";

                        // Lock inputs so POS script cannot reset them
                        window.isReopenFromSidebar = true;
                        if (countInput) countInput.setAttribute("data-locked", "true");
                        if (prefixInput) prefixInput.setAttribute("data-locked", "true");

                        iziToast.success({
                            title: "Loaded",
                            message: "Pending invoice returned to POS.",
                            position: "topRight",
                        });
                    } else {
                        alert("Unable to load sale details");
                    }
                })
                .catch(() => {
                    alert("Failed to load sale");
                });
        });
    });
});

(function ($) {
    "use strict";

    $(document).ready(function () {
        loadPendingInvoices();

        $(document).on("input", "#pending-search", function () {
            const q = $(this).val().toLowerCase();
            $("#pendingInvoicesList > li").each(function () {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(q) !== -1);
            });
        });

        $(document).on("click", '.sidebar-pos-action-btn[title="Return"]', function (e) {
            e.preventDefault();
            const id = $(this).data("id");
            if (!id) return;

            $.getJSON(`/pos/pending/${id}`, function (res) {
                if (!res || res.status !== true) {
                    iziToast.error({
                        title: "Error",
                        message: (res && res.message) ? res.message : "Failed to load invoice",
                    });
                    return;
                }

                $("#operation").val("update");

                if (!$("input[name='sale_id']").length) {
                    $("<input>").attr({ type: "hidden", id: "sale_id", name: "sale_id" }).appendTo("#invoiceForm");
                }
                $("#sale_id").val(res.sale.id);

                $("input[name='prefix_code']").val(res.sale.prefix_code || "").attr("data-locked", "true");
                $("input[name='count_id']").val(res.sale.count_id || "").attr("data-locked", "true");

                if ($("input[name='reference_no']").length) {
                    $("input[name='reference_no']").val(res.sale.reference_no || "");
                } else {
                    $("<input>")
                        .attr({
                            type: "hidden",
                            name: "reference_no",
                            value: res.sale.reference_no || "",
                        })
                        .appendTo("#invoiceForm");
                }

                $("#sale_date").val(res.sale.formatted_sale_date || "");
                if (res.sale.party_id) {
                    $("#party_id").val(res.sale.party_id).trigger("change");
                }

                $("#invoiceItemsTable tbody tr").not(".default-row").remove();

                if (Array.isArray(res.itemTransactions)) {
                    const globalTaxList =
                        (res.taxList && Object.keys(res.taxList).length)
                            ? res.taxList
                            : (typeof window.taxList !== "undefined" ? window.taxList : {});

                    res.itemTransactions.forEach(function (data) {
                        const quantity = Number(data.quantity ?? 1);
                        const unitPrice = Number(data.unit_price ?? 0);

                        const baseUnitId = data.item?.base_unit_id ?? null;
                        const secUnitId = data.item?.secondary_unit_id ?? null;
                        const selUnitId = data.unit_id ?? baseUnitId;
                        let convRate = Number(data.item?.conversion_rate ?? 1);
                        if (!isFinite(convRate) || convRate <= 0) convRate = 1;

                        let serialsArr = [];
                        if (Array.isArray(data.itemSerialTransactions)) {
                            serialsArr = data.itemSerialTransactions
                                .map((s) => s.serial_number ?? s.serial_code)
                                .filter(Boolean);
                        }

                        let batch_no = "", mfg = "", exp = "", mrp = data.mrp ?? "", model = "", color = "", size = "";
                        if (data.tracking_type === "batch" && data.batch?.item_batch_master) {
                            const bm = data.batch.item_batch_master;
                            batch_no = bm.batch_no ?? "";
                            mfg = bm.mfg_date ?? "";
                            exp = bm.exp_date ?? "";
                            mrp = bm.mrp ?? mrp;
                            model = bm.model_no ?? "";
                            color = bm.color ?? "";
                            size = bm.size ?? "";
                        }

                        const obj = {
                            id: data.item_id,
                            name: data.item?.name || "",
                            item_code: data.item?.item_code || "",
                            description: typeof data.description === "string" ? data.description : "",
                            tracking_type: data.tracking_type || "regular",
                            quantity: quantity,
                            sale_price: unitPrice,
                            total_price: unitPrice * quantity,
                            unitList: Array.isArray(data.unitList) || typeof data.unitList === "object" ? data.unitList : [],
                            base_unit_id: baseUnitId,
                            secondary_unit_id: secUnitId,
                            selected_unit_id: selUnitId,
                            conversion_rate: convRate,
                            sale_price_discount: Number(data.discount ?? 0),
                            discount_type: data.discount_type || "percentage",
                            discount_amount: Number(data.discount_amount ?? 0),
                            total_price_after_discount: 0,
                            tax_id: data.tax_id ?? null,
                            tax_amount: Number(data.tax_amount ?? 0),
                            taxList: globalTaxList,
                            is_sale_price_with_tax: data.tax_type === "inclusive" ? 1 : 0,
                            batch_no: batch_no,
                            mfg_date: mfg,
                            exp_date: exp,
                            mrp: mrp,
                            model_no: model,
                            color: color,
                            size: size,
                            serial_numbers: serialsArr.length ? JSON.stringify(serialsArr) : "",
                            itemSerialTransactions: data.itemSerialTransactions || [],
                        };

                        addRowToInvoiceItemsTable(obj, true);
                    });
                }

                $("input[name='round_off']").val(res.sale.round_off ?? 0);
                $("input[name='grand_total']").val(res.sale.grand_total ?? 0);

                try { if (typeof updateCustomerDisplay === "function") updateCustomerDisplay(); } catch (e) {}

                iziToast.success({ title: "Loaded", message: "Pending invoice returned to POS." });

                const sidebar = document.getElementById("sidebar-pos");
                const overlay = document.getElementById("sidebar-pos-overlay");
                if (sidebar && overlay) {
                    sidebar.classList.remove("open");
                    overlay.classList.remove("active");
                    overlay.style.display = "none";
                }
            }).fail(function (xhr) {
                const msg = xhr?.responseJSON?.message || "Failed to load invoice";
                iziToast.error({ title: "Error", message: msg });
            });
        });
    });

    function loadPendingInvoices() {
        $.getJSON("/pos/pending/list", function (res) {
            const list = $("#pendingInvoicesList").empty();
            if (typeof res.count === "number") $("#pending-count").text(res.count);
            if (!res || !Array.isArray(res.data) || !res.data.length) {
                list.append('<li class="list-group-item text-muted">{{ __("sale.no_pending_invoices") }}</li>');
                return;
            }
            res.data.forEach(function (inv) {
                const li = `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${escapeHtml(inv.sale_code || "")}</div>
                        <small class="text-muted">${escapeHtml(inv.party_name || "Walk-in")}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">${escapeHtml(inv.grand_total || "0.00")}</span>
                        <button class="sidebar-pos-action-btn btn btn-sm btn-outline-primary" data-id="${inv.id}" title="Return">
                            <i class='bx bx-redo bx-tada bx-flip-vertical'></i>
                        </button>
                    </div>
                </li>`;
                list.append(li);
            });
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"'`=\/]/g, function (c) {
            return {
                "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;",
                "'": "&#39;", "/": "&#x2F;", "`": "&#x60;", "=": "&#x3D;"
            }[c];
        });
    }
})(jQuery);
