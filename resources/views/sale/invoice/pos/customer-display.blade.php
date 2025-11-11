@extends('layouts.app-pos')

@section('title', __('sale.customer_display'))

@section('css')
    <link rel="stylesheet" href="{{ asset('custom/css/customer-display.css') }}">
@endsection

@section('content')
    <div class="customer-display-wrapper">
        <div class="customer-display-container">
            <div class="header">
                <h1>{{ app('site')['name'] }}</h1>
                <h4>
                    {{ __('sale.order_from') }} ({{ __('warehouse.branch') }}: <span id="warehouse-name"
                        class="warehouse-name">N/A</span>)
                    {{ __('app.of') }} {{ app('company')['name'] ?? '' }}
                </h4>
                <button id="fullscreen-btn" class="fullscreen-btn" title="Toggle Fullscreen">
                    <i class='bx bx-fullscreen'></i>
                </button>
            </div>

            <div class="news-ticker">
                <div class="ticker-content">
                    <span>
                        {{ app('company')['name'] ?? '' }} |
                        {{ app('company')['email'] ?? '' }} |
                        {{ app('company')['mobile'] ?? '' }} |
                        {{ app('company')['address'] ?? '' }}
                    </span>
                    <span>
                        {{ app('company')['name'] ?? '' }} |
                        {{ app('company')['email'] ?? '' }} |
                        {{ app('company')['mobile'] ?? '' }} |
                        {{ app('company')['address'] ?? '' }}
                    </span>
                    <span>
                        {{ app('company')['name'] ?? '' }} |
                        {{ app('company')['email'] ?? '' }} |
                        {{ app('company')['mobile'] ?? '' }} |
                        {{ app('company')['address'] ?? '' }}
                    </span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>{{ __('item.item') }}</th>
                            <th>{{ __('app.qty') }}</th>
                            <th>{{ __('app.price_per_unit') }}</th>
                            <th>{{ __('app.total') }}</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        <tr class="empty-row">
                            <td colspan="4" class="no-items">{{ __('message.no_items_added') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="totals">
                <div class="total-row final-total">
                    <span>{{ __('app.total') }}:</span>
                    <span id="display-total">0.00</span>
                </div>
            </div>
        </div>

        <div class="image-slider-container">
            <div class="slider-wrapper">
                <div class="slider" id="image-slider">
                    <!-- Images will be dynamically loaded here -->
                </div>
                <div class="slider-controls">
                    <button class="slider-btn prev-btn" id="prev-btn">
                        <i class='bx bx-chevron-left'></i>
                    </button>
                    <button class="slider-btn next-btn" id="next-btn">
                        <i class='bx bx-chevron-right'></i>
                    </button>
                </div>
                <div class="slider-dots" id="slider-dots">
                    <!-- Dots will be dynamically generated -->
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
@endsection

@section('js')
    <script src="{{ asset('custom/js/sale/customer-display.js') }}"></script>
@endsection