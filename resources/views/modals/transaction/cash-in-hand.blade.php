<!-- Tax Modal: start -->
<div class="modal fade" id="cashAdjustmentModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title form-heading">{{ __('payment.cash_in_hand') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class=" needs-validation" id="cashAdjustmentForm" action="{{ route('cash.transaction.store') }}"
                enctype="multipart/form-data">
                {{-- CSRF Protection --}}
                @csrf
                @method('POST')
                <div class="modal-body row g-3">
                    <div class="col-md-12">
                        <x-label for="adjustment_type" name="{{ __('payment.adjustment_type') }}" />
                        <select class="form-select" id="adjustment_type" name="adjustment_type">
                            <option value="Cash Increase">Add Cash</option>
                            <option value="Cash Reduce">Reduce Cash</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <x-label for="adjustment_date" name="{{ __('app.date') }}" />
                        <div class="input-group mb-3">
                            <x-input type="text" additionalClasses="datepicker" name="adjustment_date"
                                :required="true" value="" />
                            <span class="input-group-text" id="input-near-focus" role="button"><i
                                    class="fadeIn animated bx bx-calendar-alt"></i></span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <x-label for="register_id" name="{{ __('register.registers') }}" />
                        <select
                            class="form-select register-ajax"
                            id="register_id"
                            name="register_id"
                            data-placeholder="{{ __('register.select_register') }}"
                            data-ajax-url="{{ route('register.ajax.index') }}">
                        </select>
                    </div>

                    <div class="col-md-12">
                        <x-label for="amount" name="{{ __('payment.amount') }}" />
                        <x-input type="text" additionalClasses="cu_numeric text-start" name="amount" value="" />
                    </div>
                    <div class="col-md-12">
                        <x-label for="note" name="{{ __('app.note') }}" />
                        <x-textarea name="note" value="" />
                    </div>
                    <!-- Hidden Fields -->
                    <x-input type="hidden" name="cash_adjustment_id" value="" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <x-button type="submit" class="btn btn-primary" text="{{ __('app.submit') }}" />
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Tax Modal: end -->
