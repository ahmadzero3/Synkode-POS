@extends('layouts.app')
@section('title', __('app.dashboard'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">

            @can('dashboard.can.view.widget.cards')
                <div class="d-flex justify-content-end mb-3">
                    <button id="refreshDashboard" class="btn btn-outline-primary">
                        <i class="bx bx-refresh me-1"></i> {{ __('app.refresh') }}
                    </button>
                </div>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('sale.order.pending') }}</p>
                                        <h4 class="my-1 text-info" id="pendingInvoices">{{ $pendingInvoices }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                        <i class='bx bxs-cart'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('sale.order.completed') }}</p>
                                        <h4 class="my-1 text-success" id="totalCompletedSaleOrders">{{ $totalCompletedSaleOrders }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                        <i class='bx bxs-check-circle'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('payment.payment_receivables') }}
                                            <i data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ __('customer.receivables_minus_payables') }}"
                                                class="bx bx-info-circle text-secondary align-middle ms-1"></i>
                                        </p>
                                        <h4 class="my-1 text-danger" id="totalPaymentReceivables">{{ number_format($totalPaidAmountForFinishedInvoices, 2) }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                        <i class='bx bxs-down-arrow-circle'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('payment.payment_paybles') }}
                                            <i data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ __('supplier.payables_minus_receivables') }}"
                                                class="bx bx-info-circle text-secondary align-middle ms-1"></i>
                                        </p>
                                        <h4 class="my-1 text-warning" id="totalSuppliers">{{ $totalSuppliers }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                                        <i class='bx bxs-cube'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('purchase.order.pending') }}</p>
                                        <h4 class="my-1 text-info" id="pendingPurchaseOrders">{{ $pendingPurchaseOrders }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                        <i class='bx bxs-purchase-tag'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('purchase.order.completed') }}</p>
                                        <h4 class="my-1 text-success" id="totalPurchaseOrders">{{ $totalPurchaseOrders }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                        <i class='bx bx-check-double'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('expense.total_expenses') }}</p>
                                        <h4 class="my-1 text-danger" id="totalExpense">{{ $totalExpense }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                        <i class='bx bxs-minus-circle'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card radius-10 border-start border-0 border-4 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 text-secondary">{{ __('customer.total') }}</p>
                                        <h4 class="my-1 text-warning" id="totalCustomers">{{ $totalCustomers }}</h4>
                                    </div>
                                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                                        <i class='bx bxs-group'></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->
            @endcan

            <div class="row">
                @can('dashboard.can.view.sale.vs.purchase.bar.chart')
                    <div class="col-12 col-lg-8 d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ __('sale.sale_vs_purchase') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center ms-auto font-13 gap-2 mb-3">
                                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                            style="color: #ffc107"></i>{{ __('purchase.purchase_bills') }}</span>
                                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                            style="color: #14abef"></i>{{ __('sale.sale_invoices') }}</span>
                                </div>
                                <div class="chart-container-1">
                                    <canvas id="chart1"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('dashboard.can.view.trending.items.pie.chart')
                    <div class="col-12 col-lg-4 d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ __('item.trending') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-2">
                                    <canvas id="chart2"></canvas>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush" id="trendingItemsList">
                                @foreach ($trendingItems as $item)
                                    <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center border-top">
                                        {{ $item['name'] }}
                                        <span class="badge bg-success rounded-pill">{{ $formatNumber->formatQuantity($item['total_quantity']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endcan
            </div><!--end row-->

            @can('dashboard.can.view.recent.invoices.table')
                <div class="card radius-10">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">{{ __('sale.recent_invoices') }}</h6>
                            </div>
                            @can('sale.invoice.view')
                                <div class="font-13 ms-auto">
                                    <a href="{{ route('sale.invoice.list') }}"
                                        class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }}</a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('sale.invoice_date') }}</th>
                                        <th>{{ __('sale.code') }}</th>
                                        <th>{{ __('customer.name') }}</th>
                                        <th>{{ __('app.grand_total') }}</th>
                                        <th>{{ __('app.balance') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="recentInvoicesTable">
                                    @foreach ($recentInvoices as $recent)
                                        <tr>
                                            <td>{{ $recent->formatted_sale_date }}</td>
                                            <td>{{ $recent->sale_code }}</td>
                                            <td>{{ $recent->party->getFullName() }}</td>
                                            <td class="">{{ $formatNumber->formatWithPrecision($recent->grand_total) }}</td>
                                            <td class="">{{ $formatNumber->formatWithPrecision($recent->grand_total - $recent->paid_amount) }}</td>
                                            @php
                                                if ($recent->grand_total == $recent->paid_amount) {
                                                    $class = 'success';
                                                    $message = 'Paid';
                                                } elseif ($recent->grand_total < $recent->paid_amount) {
                                                    $class = 'warning';
                                                    $message = 'Partial';
                                                } else {
                                                    $class = 'danger';
                                                    $message = 'Unpaid';
                                                }
                                            @endphp
                                            <td class="">
                                                <div class="badge rounded-pill text-{{ $class }} bg-light-{{ $class }} p-2 text-uppercase px-3">
                                                    {{ $message }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan

            @can('dashboard.can.view.low.stock.items.table')
                <div class="card radius-10">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">{{ __('item.low_stock_items') }}</h6>
                            </div>
                            @can('item.view')
                                <div class="font-13 ms-auto">
                                    <a href="{{ route('item.list') }}"
                                        class="btn btn-sm btn-outline-primary">{{ __('app.view_all') }}</a>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('item.item_name') }}</th>
                                        <th>{{ __('item.brand.brand') }}</th>
                                        <th>{{ __('item.category.category') }}</th>
                                        <th>{{ __('item.min_stock') }}</th>
                                        <th>{{ __('item.current_stock') }}</th>
                                        <th>{{ __('unit.unit') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="lowStockItemsTable">
                                    @php $i = 1; @endphp
                                    @foreach ($lowStockItems as $item)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->brand->name ?? '' }}</td>
                                            <td>{{ $item->category->name }}</td>
                                            <td class="">{{ $formatNumber->formatQuantity($item->min_stock) }}</td>
                                            <td class="text-danger fw-bold">{{ $formatNumber->formatQuantity($item->current_stock) }}</td>
                                            <td>{{ $item->baseUnit->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('js')
    <script src="{{ versionedAsset('custom/js/dashboard.js') }}"></script>
    <script>
        // Pass PHP variables to JavaScript
        window.dashboardData = {
            chartMonths: @json($saleVsPurchase).map(record => record.label),
            chartSales: @json($saleVsPurchase).map(record => record.sales),
            chartPurchases: @json($saleVsPurchase).map(record => record.purchases),
            serviceNames: @json($trendingItems).map(x => x.name),
            serviceCounts: @json($trendingItems).map(x => x.total_quantity),
            refreshUrl: "{{ route('dashboard.refresh') }}",
            translations: {
                refresh: "{{ __('app.refresh') }}",
                success: "{{ __('app.success') }}",
                error: "{{ __('app.error') }}",
                dashboard_refreshed_successfully: "{{ __('app.dashboard_refreshed_successfully') }}",
                failed_to_refresh_dashboard: "{{ __('app.failed_to_refresh_dashboard') }}"
            }
        };
    </script>
@endsection