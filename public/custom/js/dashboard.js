$(function () {
    "use strict";

    // Initialize charts
    initializeCharts();

    // Refresh Button Click Handler
    $('#refreshDashboard').on('click', function () {
        refreshDashboardData();
    });

    function initializeCharts() {
        // Chart 1 - Bar Chart
        var ctx1 = document.getElementById("chart1").getContext("2d");

        var gradientStroke1 = ctx1.createLinearGradient(0, 0, 0, 300);
        gradientStroke1.addColorStop(0, "#6078ea");
        gradientStroke1.addColorStop(1, "#17c5ea");

        var gradientStroke2 = ctx1.createLinearGradient(0, 0, 0, 300);
        gradientStroke2.addColorStop(0, "#ff8359");
        gradientStroke2.addColorStop(1, "#ffdf40");

        window.barChart = new Chart(ctx1, {
            type: "bar",
            data: {
                labels: window.dashboardData.chartMonths,
                datasets: [
                    {
                        label: "Purchase",
                        data: window.dashboardData.chartPurchases,
                        borderColor: gradientStroke2,
                        backgroundColor: gradientStroke2,
                        hoverBackgroundColor: gradientStroke2,
                        pointRadius: 0,
                        fill: false,
                        borderRadius: 20,
                        borderWidth: 0,
                    },
                    {
                        label: "Sale",
                        data: window.dashboardData.chartSales,
                        borderColor: gradientStroke1,
                        backgroundColor: gradientStroke1,
                        hoverBackgroundColor: gradientStroke1,
                        pointRadius: 0,
                        fill: false,
                        borderRadius: 20,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                barPercentage: 0.5,
                categoryPercentage: 0.8,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });

        // Chart 2 - Doughnut Chart
        var ctx2 = document.getElementById("chart2").getContext("2d");

        var gradientStroke1 = ctx2.createLinearGradient(0, 0, 0, 300);
        gradientStroke1.addColorStop(0, "#fc4a1a");
        gradientStroke1.addColorStop(1, "#f7b733");

        var gradientStroke2 = ctx2.createLinearGradient(0, 0, 0, 300);
        gradientStroke2.addColorStop(0, "#4776e6");
        gradientStroke2.addColorStop(1, "#8e54e9");

        var gradientStroke3 = ctx2.createLinearGradient(0, 0, 0, 300);
        gradientStroke3.addColorStop(0, "#ee0979");
        gradientStroke3.addColorStop(1, "#ff6a00");

        var gradientStroke4 = ctx2.createLinearGradient(0, 0, 0, 300);
        gradientStroke4.addColorStop(0, "#42e695");
        gradientStroke4.addColorStop(1, "#3bb2b8");

        window.doughnutChart = new Chart(ctx2, {
            type: "doughnut",
            data: {
                labels: window.dashboardData.serviceNames,
                datasets: [
                    {
                        backgroundColor: [
                            gradientStroke1,
                            gradientStroke2,
                            gradientStroke3,
                            gradientStroke4,
                        ],
                        hoverBackgroundColor: [
                            gradientStroke1,
                            gradientStroke2,
                            gradientStroke3,
                            gradientStroke4,
                        ],
                        data: window.dashboardData.serviceCounts,
                        borderWidth: [1, 1, 1, 1],
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                cutout: 82,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }

    function refreshDashboardData() {
        let $btn = $('#refreshDashboard');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading... ');

        $.ajax({
            url: window.dashboardData.refreshUrl,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    // Update card values
                    $('#pendingInvoices').text(response.pendingInvoices);
                    $('#totalCompletedSaleOrders').text(response.totalCompletedSaleOrders);
                    $('#totalPaymentReceivables').text(response.totalPaymentReceivables);
                    $('#totalSuppliers').text(response.totalSuppliers);
                    $('#pendingPurchaseOrders').text(response.pendingPurchaseOrders);
                    $('#totalPurchaseOrders').text(response.totalPurchaseOrders);
                    $('#totalCustomers').text(response.totalCustomers);
                    $('#totalExpense').text(response.totalExpense);

                    // Update trending items
                    updateTrendingItems(response.trendingItems);

                    // Update recent invoices table
                    updateRecentInvoices(response.recentInvoices);

                    // Update low stock items table
                    updateLowStockItems(response.lowStockItems);

                    // Update charts
                    updateCharts(response.saleVsPurchase, response.trendingItems);

                    showToast('success', window.dashboardData.translations.dashboard_refreshed_successfully);
                } else {
                    showToast('error', response.message || window.dashboardData.translations.failed_to_refresh_dashboard);
                }
            },
            error: function (xhr, status, error) {
                console.error('Dashboard refresh error:', error);
                showToast('error', window.dashboardData.translations.failed_to_refresh_dashboard);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bx bx-refresh me-1"></i> ' + window.dashboardData.translations.refresh);
            }
        });
    }

    function updateTrendingItems(trendingItems) {
        const trendingItemsList = $('#trendingItemsList');
        trendingItemsList.empty();

        trendingItems.forEach(item => {
            const listItem = `
                <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                    ${item.name}
                    <span class="badge bg-success rounded-pill">${item.total_quantity}</span>
                </li>
            `;
            trendingItemsList.append(listItem);
        });
    }

    function updateRecentInvoices(recentInvoices) {
        const recentInvoicesTable = $('#recentInvoicesTable');
        recentInvoicesTable.empty();

        recentInvoices.forEach(invoice => {
            const row = `
                <tr>
                    <td>${invoice.formatted_sale_date}</td>
                    <td>${invoice.sale_code}</td>
                    <td>${invoice.party_name}</td>
                    <td>${invoice.grand_total}</td>
                    <td>${invoice.balance}</td>
                    <td>
                        <div class="badge rounded-pill text-${invoice.status.class} bg-light-${invoice.status.class} p-2 text-uppercase px-3">
                            ${invoice.status.message}
                        </div>
                    </td>
                </tr>
            `;
            recentInvoicesTable.append(row);
        });
    }

    function updateLowStockItems(lowStockItems) {
        const lowStockItemsTable = $('#lowStockItemsTable');
        lowStockItemsTable.empty();

        lowStockItems.forEach((item, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.brand}</td>
                    <td>${item.category}</td>
                    <td>${item.min_stock}</td>
                    <td class="text-danger fw-bold">${item.current_stock}</td>
                    <td>${item.unit}</td>
                </tr>
            `;
            lowStockItemsTable.append(row);
        });
    }

    function updateCharts(saleVsPurchase, trendingItems) {
        // Update bar chart data
        window.barChart.data.labels = saleVsPurchase.map(record => record.label);
        window.barChart.data.datasets[0].data = saleVsPurchase.map(record => record.purchases);
        window.barChart.data.datasets[1].data = saleVsPurchase.map(record => record.sales);
        window.barChart.update();

        // Update doughnut chart data
        window.doughnutChart.data.labels = trendingItems.map(item => item.name);
        window.doughnutChart.data.datasets[0].data = trendingItems.map(item => item.total_quantity);
        window.doughnutChart.update();
    }

    function showToast(type, message) {
        const title = type === 'success' ? window.dashboardData.translations.success : window.dashboardData.translations.error;
        
        iziToast[type]({
            title: title,
            message: message,
            position: 'topRight'
        });
    }
});