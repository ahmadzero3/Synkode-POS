<div id="sidebar-pos-overlay" class="sidebar-pos-overlay"></div>
<div id="sidebar-pos" class="sidebar-pos">
    <div class="sidebar-pos-header">
        <span id="sidebar-pos-close" class="sidebar-pos-close">&times;</span>
        <h4>{{ __('sale.pending_invoices') }}</h4>
    </div>
    <div class="sidebar-pos-content">
        @php
            $pendingSales = \App\Models\Sale\Sale::where('invoice_status', 'pending')->orderByDesc('created_at')->get();
        @endphp
        @if ($pendingSales->count())
            @foreach ($pendingSales as $sale)
                <div class="sidebar-pos-sale-item">
                    <div><strong>{{ __('sale.sale_code') }}:</strong> {{ $sale->sale_code }}</div>
                    <div><strong>{{ __('sale.sale_date') }}:</strong> {{ $sale->sale_date }}</div>
                    <div><strong>{{ __('sale.created_at') }}:</strong> {{ $sale->created_at }}</div>
                    <div class="sidebar-pos-invoice-actions">
                        <strong>{{ __('sale.invoice_status') }}:</strong>
                        <span class="sidebar-pos-invoice-status">
                            {{ ucfirst($sale->invoice_status) }}
                        </span>
                        <div class="sidebar-pos-buttons">
                            <button class="sidebar-pos-action-btn finish-invoice-btn" title="{{ __('sale.finish') }}"
                                data-sale-id="{{ $sale->id }}">
                                <i class='bx bx-check'></i>
                            </button>
                            <button class="sidebar-pos-action-btn" title="{{ __('sale.delete') }}"
                                data-sale-id="{{ $sale->id }}">
                                <i class='bx bxs-trash-alt'></i>
                            </button>
                            <button class="sidebar-pos-action-btn btn btn-sm" title="Return"
                                data-id="{{ $sale->id }}">
                                <i class='bx bx-redo bx-tada bx-flip-vertical'></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p>{{ __('sale.no_pending_invoices') }}</p>
        @endif
    </div>
</div>
