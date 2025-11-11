<!--sidebar wrapper -->
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src={{ url('/app/getimage/' . app('site')['colored_logo']) }} class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">{{ app('site')['name'] }}</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
        </div>
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i>
                </div>
                <div class="menu-title">{{ __('app.dashboard') }}</div>
            </a>
        </li>

        @canany(['item.create', 'item.view', 'item.category.create', 'item.category.view', 'item.brand.create',
            'item.brand.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-package"></i>
                    </div>
                    <div class="menu-title">{{ __('item.items') }}</div>
                </a>
                <ul>
                    @can('item.view')
                        <li
                            class="{{ request()->is('item/list', 'item/create', 'item/edit*', 'item/transaction*') ? 'mm-active' : '' }}">
                            <a href="{{ route('item.list') }}"><i class='bx bx-radio-circle'></i>{{ __('item.list') }}</a>
                        </li>
                    @endcan
                    @can('item.category.view')
                        <li class="{{ request()->is('item/category/*', 'item/category/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('item.category.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.category.list') }}</a>
                        </li>
                    @endcan
                    @can('item.brand.view')
                        <li class="{{ request()->is('item/brand/*', 'item/brand/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('item.brand.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.brand.list') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['purchase.bill.view', 'purchase.order.view', 'purchase.return.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-purchase-tag-alt"></i>
                    </div>
                    <div class="menu-title">{{ __('purchase.purchase') }}</div>
                </a>
                <ul>
                    @can('purchase.bill.view')
                        <li class="{{ request()->is('purchase/bill/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('purchase.bill.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('purchase.bills') }}</a>
                        </li>
                    @endcan

                    @can('purchase.bill.view')
                        <li class="{{ request()->is('payment/out') ? 'mm-active' : '' }}">
                            <a href="{{ route('purchase.payment.out') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.payment_out') }}</a>
                        </li>
                    @endcan

                    @can('purchase.order.view')
                        <li class="{{ request()->is('purchase/order/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('purchase.order.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('purchase.order.order') }}</a>
                        </li>
                    @endcan

                    @can('purchase.return.view')
                        <li class="{{ request()->is('purchase/return/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('purchase.return.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('purchase.return.return') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['sale.invoice.view', 'sale.order.view', 'sale.return.view', 'sale.quotation.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-cart"></i>
                    </div>
                    <div class="menu-title">{{ __('sale.sale') }}</div>
                </a>
                <ul>
                    @can('sale.invoice.create')
                        <li class="{{ request()->is('pos*') ? 'mm-active' : '' }}">
                            <a href="{{ route('pos.create') }}"><i class='bx bx-radio-circle'></i>{{ __('sale.pos') }}</a>
                        </li>
                    @endcan

                    @can('sale.bill.view')
                        <li class="{{ request()->is('sale/invoice/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('sale.invoice.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('sale.invoices') }}</a>
                        </li>
                    @endcan

                    @can('sale.quotation.view')
                        <li class="{{ request()->is('quotation/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('sale.quotation.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('sale.quotation.quotations') }}</a>
                        </li>
                    @endcan

                    @can('sale.invoice.view')
                        <li class="{{ request()->is('payment/in') ? 'mm-active' : '' }}">
                            <a href="{{ route('sale.payment.in') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.payment_in') }}</a>
                        </li>
                    @endcan

                    @can('sale.order.view')
                        <li class="{{ request()->is('sale/order/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('sale.order.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('sale.order.order') }}</a>
                        </li>
                    @endcan

                    @can('sale.return.view')
                        <li class="{{ request()->is('sale/return/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('sale.return.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('sale.return.return') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{--
				@canany(['customer.create', 'customer.view'])
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-group"></i>
						</div>
						<div class="menu-title">{{
						__('customer.customers') }}
</div>
</a>
<ul>
	@can('customer.create')
	<li class="{{ request()->is('customer/create') ? 'mm-active' : '' }}">
		<a href="{{ route('customer.create') }}"><i class='bx bx-radio-circle'></i>{{ __('customer.create_customer') }}</a>
	</li>
	@endcan
	@can('customer.view')
	<li class="{{ request()->is('customer/list', 'customer/edit*') ? 'mm-active' : '' }}">
		<a href="{{ route('customer.list') }}"><i class='bx bx-radio-circle'></i>{{ __('customer.list') }}</a>
	</li>
	@endcan
</ul>
</li>
@endcanany
--}}



        {{--
				@canany(['service.create', 'service.view'])
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-package"></i>
						</div>
						<div class="menu-title">{{
						__('service.services') }}</div>
</a>
<ul>
	@can('service.create')
	<li class="{{ request()->is('service/create') ? 'mm-active' : '' }}">
		<a href="{{ route('service.create') }}"><i class='bx bx-radio-circle'></i>{{ __('service.create') }}</a>
	</li>
	@endcan
	@can('service.view')
	<li class="{{ request()->is('service/list', 'service/edit*') ? 'mm-active' : '' }}">
		<a href="{{ route('service.list') }}"><i class='bx bx-radio-circle'></i>{{ __('service.list') }}</a>
	</li>
	@endcan
</ul>
</li>
@endcanany
--}}

        @canany(['expense.create', 'expense.view', 'expense.category.view', 'expense.subcategory.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-minus-circle"></i>
                    </div>
                    <div class="menu-title">{{ __('expense.expense') }}</div>
                </a>
                <ul>
                    @can('expense.view')
                        <li
                            class="{{ request()->is('expense/list', 'expense/create', 'expense/edit*', 'expense/print/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('expense.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('expense.list') }}</a>
                        </li>
                    @endcan
                    @can('expense.category.view')
                        <li class="{{ request()->is('expense/category/list', 'expense/category/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('expense.category.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('expense.category.list') }}</a>
                        </li>
                    @endcan
                    @can('expense.subcategory.view')
                        <li
                            class="{{ request()->is('expense/subcategory/list', 'expense/subcategory/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('expense.subcategory.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('expense.subcategory.list') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['customer.create', 'customer.view', 'supplier.create', 'supplier.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-group"></i>
                    </div>
                    <div class="menu-title">{{ __('party.contacts') }}</div>
                </a>
                <ul>

                    @can('customer.view')
                        <li class="{{ request()->is(['party/customer/*', 'party/payment/customer/*']) ? 'mm-active' : '' }}">
                            <a href="{{ route('party.list', ['partyType' => 'customer']) }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('customer.customers') }}</a>
                        </li>
                    @endcan

                    @can('supplier.view')
                        <li class="{{ request()->is(['party/supplier/*', 'party/payment/supplier/*']) ? 'mm-active' : '' }}">
                            <a href="{{ route('party.list', ['partyType' => 'supplier']) }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('supplier.suppliers') }}</a>
                        </li>
                    @endcan

                    @if (app('company')['is_enable_carrier'])
                        @can('carrier.view')
                            <li class="{{ request()->is(['carrier/*']) ? 'mm-active' : '' }}">
                                <a href="{{ route('carrier.list') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('carrier.carriers') }}</a>
                            </li>
                        @endcan
                    @endif

                </ul>
            </li>
        @endcanany

        @if (app('company')['is_enable_crm'])
            <li class="menu-label">CRM</li>
            @canany(['order.create', 'order.view'])
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-door-open"></i>
                        </div>
                        <div class="menu-title">{{ __('order.orders') }}</div>
                    </a>
                    <ul>
                        @can('order.create')
                            <li class="{{ request()->is('order/create') ? 'mm-active' : '' }}">
                                <a href="{{ route('order.create') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.create') }}</a>
                            </li>
                        @endcan
                        @can('order.view')
                            <li class="{{ request()->is('order/list', 'order/edit*', 'order/receipt*') ? 'mm-active' : '' }}">
                                <a href="{{ route('order.list') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.list') }}</a>
                            </li>
                        @endcan
                        @can('service.view')
                            <li class="{{ request()->is('service/list', 'service/edit*') ? 'mm-active' : '' }}">
                                <a href="{{ route('service.list') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('service.list') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['schedule.create', 'schedule.view', 'assigned_jobs.create', 'assigned_jobs.view'])
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-alarm-add"></i>
                        </div>
                        <div class="menu-title">{{ __('schedule.scheduling') }}</div>
                    </a>
                    <ul>
                        @can('schedule.view')
                            <li
                                class="{{ request()->is('schedule/list', 'schedule/edit*', 'schedule/receipt*', 'order/timeline/*') ? 'mm-active' : '' }}">
                                <a href="{{ route('schedule.list') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.list') }}</a>
                            </li>
                        @endcan

                        @can('assigned_jobs.view')
                            <li class="{{ request()->is('assigned-jobs/list', 'assigned-jobs/update*') ? 'mm-active' : '' }}">
                                <a href="{{ route('assigned_jobs.list') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('schedule.jobs') }}</a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcanany
        @endif

        @canany(['transaction.cash.view', 'warehouse.view', 'user.view', 'sms.create', 'email.create', 'report.sale',
            'app.settings.edit', 'transaction.cash.close', 'transaction.cash.close.list'])
            <li class="menu-label">{{ __('app.core') }}</li>
        @endcanany

        @canany(['transaction.cash.view', 'transaction.cheque.view', 'transaction.bank.view', 'transaction.cash.close',
            'transaction.cash.close.list'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-wallet-alt"></i>
                    </div>
                    <div class="menu-title">{{ __('payment.cash_and_bank') }}</div>
                </a>
                <ul>
                    @can('transaction.cash.view')
                        <li class="{{ request()->is('transaction/cash/list') ? 'mm-active' : '' }}">
                            <a href="{{ route('transaction.cash.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.cash_in_hand') }}</a>
                        </li>
                    @endcan

                    @can('transaction.cash.close')
                        <li class="{{ request()->is('transaction/cash/close') ? 'mm-active' : '' }}">
                            <a href="{{ route('transaction.close-cash') }}">
                                <i class='bx bx-radio-circle'></i>{{ __('app.close_cash') }}
                            </a>
                        </li>
                    @endcan

                    @can('transaction.cash.close.list')
                        <li class="{{ request()->routeIs('close.cash.list', 'close-cash.edit') ? 'mm-active' : '' }}">
                            <a href="{{ route('close.cash.list') }}">
                                <i class="bx bx-radio-circle"></i>
                                {{ __('app.close_cash_list') }}
                            </a>
                        </li>
                    @endcan

                    @can('transaction.cheque.view')
                        <li class="{{ request()->is('transaction/cheque/list') ? 'mm-active' : '' }}">
                            <a href="{{ route('transaction.cheque.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.cheques') }}</a>
                        </li>
                    @endcan

                    @can('transaction.bank.view')
                        <li class="{{ request()->is('transaction/bank/list') ? 'mm-active' : '' }}">
                            <a href="{{ route('transaction.bank.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.bank') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['warehouse.view', 'stock_transfer.view', 'stock_adjustment.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-building"></i>
                    </div>
                    <div class="menu-title">{{ __('warehouse.warehouse') }}</div>
                </a>
                <ul>
                    @can('warehouse.view')
                        <li class="{{ request()->is('warehouse*') ? 'mm-active' : '' }}">
                            <a href="{{ route('warehouse.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('warehouse.warehouses') }}</a>
                        </li>
                    @endcan

                    @can('stock_transfer.view')
                        <li class="{{ request()->is('stock-transfer/list') ? 'mm-active' : '' }}">
                            <a href="{{ route('stock_transfer.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('warehouse.stock_transfer') }}</a>
                        </li>
                    @endcan
                    @can('stock_adjustment.view')
                        <li class="{{ request()->is('stock-adjustment/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('stock_adjustment.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('warehouse.adjustment') }}</a>
                        </li>
                    @endcan

                </ul>
            </li>
        @endcanany

        @canany(['import.item', 'import.party', 'generate.barcode'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-wrench"></i>
                    </div>
                    <div class="menu-title">{{ __('app.utilities') }}</div>
                </a>
                <ul>
                    @can('import.item')
                        <li class="{{ request()->is('import/item') ? 'mm-active' : '' }}">
                            <a href="{{ route('import.items') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.import_items') }}</a>
                        </li>
                    @endcan
                    @can('import.party')
                        <li class="{{ request()->is('import/party') ? 'mm-active' : '' }}">
                            <a href="{{ route('import.party') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('party.import_contacts') }}</a>
                        </li>
                    @endcan
                    @can('generate.barcode')
                        <li class="{{ request()->is('item/generate/barcode') ? 'mm-active' : '' }}">
                            <a href="{{ route('generate.barcode') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.generate_barcode') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['account.create', 'account.view', 'account.group.create', 'account.group.view'])
            <li class="d-none">
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-money"></i>
                    </div>
                    <div class="menu-title">{{ __('account.accounts') }}</div>
                </a>
                <ul>
                    @can('account.view')
                        <li
                            class="{{ request()->is('account/list', 'account/create', 'account/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('account.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('account.list') }}</a>
                        </li>
                    @endcan
                    @can('account.group.view')
                        <li
                            class="{{ request()->is('account/group/list', 'account/group/create', 'account/group/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('account.group.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('account.group.list') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['profile.edit', 'user.view', 'role.view', 'permission.view', 'permission.group.view', 'register.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-group"></i>
                    </div>
                    <div class="menu-title">{{ __('user.users') }}</div>
                </a>
                <ul>
                    @can('profile.edit')
                        <li class="{{ request()->is('profile') ? 'mm-active' : '' }}">
                            <a href="{{ route('user.profile') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('user.profile') }}</a>
                        </li>
                    @endcan
                    @can('user.view')
                        <li class="{{ request()->is('users*') ? 'mm-active' : '' }}">
                            <a href="{{ route('users.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('user.users') }}</a>
                        </li>
                    @endcan
                    @can('role.view')
                        <li class="{{ request()->is('role-and-permission/role*') ? 'mm-active' : '' }}">
                            <a href="{{ route('roles.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('app.roles') }}</a>
                        </li>
                    @endcan

                    @can('session.view')
                        <li class="{{ request()->is('session/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('session.list') }}">
                                <i class='bx bx-radio-circle'></i>{{ __('app.session') }}
                            </a>
                        </li>
                    @endcan

                    @can('register.view')
                        <li class="{{ request()->is(['register/*']) ? 'mm-active' : '' }}">
                            <a href="{{ route('register.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('register.registers') }}</a>
                        </li>
                    @endcan
                </ul>
                @canany(['permission.view', 'permission.group.view'])
                    <ul>
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('app.permissions') }}</a>

                            <ul>
                                @can('permission.view')
                                    <li class="{{ request()->is('role-and-permission/permission*') ? 'mm-active' : '' }}">
                                        <a href="{{ route('permission.list') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.permission_list') }}</a>
                                    </li>
                                @endcan
                                @can('permission.group.view')
                                    <li class="{{ request()->is('role-and-permission/group*') ? 'mm-active' : '' }}">
                                        <a href="{{ route('permission.group.list') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.group_list') }}</a>
                                    </li>
                                @endcan
                            </ul>

                        </li>
                    </ul>
                @endcanany
            </li>
        @endcanany

        @canany(['sms.create', 'sms.template.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-message"></i>
                    </div>
                    <div class="menu-title">{{ __('message.sms') }}</div>
                </a>
                <ul>
                    @can('sms.create')
                        <li class="{{ request()->is('sms/create') ? 'mm-active' : '' }}">
                            <a href="{{ route('sms.create') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('message.create_sms') }}</a>
                        </li>
                    @endcan
                    @can('sms.template.view')
                        <li
                            class="{{ request()->is('sms/template/list', 'sms/template/create', 'sms/template/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('sms.template.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('message.templates') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['email.create', 'email.template.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-envelope"></i>
                    </div>
                    <div class="menu-title">{{ __('message.email') }}</div>
                </a>
                <ul>
                    @can('email.create')
                        <li class="{{ request()->is('email/create') ? 'mm-active' : '' }}">
                            <a href="{{ route('email.create') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('message.create_email') }}</a>
                        </li>
                    @endcan
                    @can('email.template.view')
                        <li
                            class="{{ request()->is('email/template/list', 'email/template/create', 'email/template/edit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('email.template.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('message.templates') }}</a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['report.*'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-bar-chart-square"></i>
                    </div>
                    <div class="menu-title">{{ __('app.reports') }}</div>
                </a>
                <ul>
                    @can('report.profit_and_loss')
                        <li class="{{ request()->is('report/profit-and-loss') ? 'mm-active' : '' }}">
                            <a href="{{ route('report.profit_and_loss') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('account.profit_and_loss') }}</a>
                        </li>
                    @endcan
                    {{--
                    	@canany(['report.balance_sheet', 'report.trial_balance'])
							<li> <a class="has-arrow" href="javascript:;"><i class='bx bx-radio-circle'></i>{{ __('account.accounting') }}</a>

		<ul>
			@can('report.balance_sheet')
			<li class="{{ request()->is('report/balance-sheet') ? 'mm-active' : '' }}">
				<a href="{{ route('report.balance_sheet') }}"><i class='bx bx-radio-circle'></i>{{ __('account.balance_sheet') }}</a>
			</li>
			@endcan
			@can('report.trial_balance')
			<li class="{{ request()->is('report/trial-balance') ? 'mm-active' : '' }}">
				<a href="{{ route('report.trial_balance') }}"><i class='bx bx-radio-circle'></i>{{ __('account.trial_balance') }}</a>
			</li>
			@endcan
		</ul>

</li>
@endcanany
--}}
                    @canany(['report.item.transaction.batch', 'report.item.transaction.serial',
                        'report.item.transaction.general'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.item_transaction') }}</a>
                            <ul>
                                @can('report.item.transaction.batch')
                                    <li class="{{ request()->is('report/item-transaction/batch') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.item.transaction.batch') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.batch_wise') }}</a>
                                    </li>
                                @endcan
                                @can('report.item.transaction.serial')
                                    <li class="{{ request()->is('report/item-transaction/serial') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.item.transaction.serial') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.serial_or_imei') }}</a>
                                    </li>
                                @endcan
                                @can('report.item.transaction.general')
                                    <li class="{{ request()->is('report/item-transaction/general') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.item.transaction.general') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.general') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @canany(['report.purchase', 'report.purchase.item'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('purchase.purchase') }}</a>
                            <ul>
                                @can('report.purchase')
                                    <li class="{{ request()->is('report/purchase') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.purchase') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('purchase.purchase') }}</a>
                                    </li>
                                @endcan
                                @can('report.purchase.item')
                                    <li class="{{ request()->is('report/purchase/item') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.purchase.item') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('purchase.item_purchase') }}</a>
                                    </li>
                                @endcan
                                @can('report.purchase.payment')
                                    <li class="{{ request()->is('report/purchase/payment') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.purchase.payment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.payment') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @canany(['report.sale', 'report.sale.item'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('sale.sale') }}</a>
                            <ul>
                                @can('report.sale')
                                    <li class="{{ request()->is('report/sale') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.sale') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('sale.sale') }}</a>
                                    </li>
                                @endcan
                                @can('report.sale.item')
                                    <li class="{{ request()->is('report/sale/item') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.sale.item') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('sale.item_sale') }}</a>
                                    </li>
                                @endcan
                                @can('report.sale.payment')
                                    <li class="{{ request()->is('report/sale/payment') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.sale.payment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.payment') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.customer.due.payment', 'report.supplier.due.payment'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.due_payments') }}&nbsp;</a>
                            <ul>
                                @can('report.customer.due.payment')
                                    <li class="{{ request()->is('report/customer/due') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.customer.due.payment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('customer.customer') }}</a>
                                    </li>
                                @endcan
                                @can('report.supplier.due.payment')
                                    <li class="{{ request()->is('report/supplier/due') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.supplier.due.payment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('supplier.supplier') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.expense', 'report.expense.item'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('expense.expense') }}</a>
                            <ul>
                                @can('report.expense')
                                    <li class="{{ request()->is('report/expense') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.expense') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('expense.expense') }}</a>
                                    </li>
                                @endcan
                                @can('report.expense.item')
                                    <li class="{{ request()->is('report/expense/item') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.expense.item') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('expense.item') }}</a>
                                    </li>
                                @endcan
                                @can('report.expense.payment')
                                    <li class="{{ request()->is('report/expense/payment') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.expense.payment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.payment') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.transaction.cashflow', 'report.transaction.bank-statement'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('app.transactions') }}</a>
                            <ul>
                                @can('report.transaction.cashflow')
                                    <li class="{{ request()->is('report/transaction/cashflow') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.transaction.cashflow') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('payment.cash_flow') }}</a>
                                    </li>
                                @endcan
                                @can('report.transaction.bank-statement')
                                    <li class="{{ request()->is('report/transaction/bank-statement') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.transaction.bank-statement') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('payment.bank_statement') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.gst*'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.gst') }}</a>
                            <ul>
                                @can('report.gstr1')
                                    <li class="{{ request()->is('report/gstr-1') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.gstr-1') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.gstr-1') }}</a>
                                    </li>
                                @endcan
                                @can('report.gstr2')
                                    <li class="{{ request()->is('report/gstr-2') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.gstr-2') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.gstr-2') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @canany(['report.stock_transfer', 'report.stock_transfer.item'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('warehouse.stock_transfer') }}</a>
                            <ul>
                                @can('report.stock_transfer')
                                    <li class="{{ request()->is('report/stock-transfer') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_transfer') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('warehouse.stock_transfer') }}</a>
                                    </li>
                                @endcan
                                @can('report.stock_transfer.item')
                                    <li class="{{ request()->is('report/stock-transfer/item') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_transfer.item') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.item_wise') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.stock_adjustment', 'report.stock_adjustment.item'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('warehouse.stock_adjustment') }}</a>
                            <ul>
                                @can('report.stock_adjustment')
                                    <li class="{{ request()->is('report/stock-adjustment') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_adjustment') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('warehouse.stock_adjustment') }}</a>
                                    </li>
                                @endcan
                                @can('report.stock_adjustment.item')
                                    <li class="{{ request()->is('report/stock-adjustment/item') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_adjustment.item') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.item_wise') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['report.stock_report.*'])
                        <li> <a class="has-arrow" href="javascript:;"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.stock_report') }}&nbsp;</a>
                            <ul>
                                @can('report.stock_report.item.batch')
                                    <li class="{{ request()->is('report/stock-report/batch') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_report.item.batch') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.batch_wise') }}</a>
                                    </li>
                                @endcan
                                @can('report.stock_report.item.serial')
                                    <li class="{{ request()->is('report/stock-report/serial') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_report.item.serial') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('item.serial_or_imei') }}</a>
                                    </li>
                                @endcan
                                @can('report.stock_report.item.general')
                                    <li class="{{ request()->is('report/stock-report/general') ? 'mm-active' : '' }}">
                                        <a href="{{ route('report.stock_report.item.general') }}"><i
                                                class='bx bx-radio-circle'></i>{{ __('app.general') }}</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @can('report.expired.item')
                        <li class="{{ request()->is('report/expired/item') ? 'mm-active' : '' }}">
                            <a href="{{ route('report.expired.item') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.expired_item_report') }}</a>
                        </li>
                    @endcan

                    @can('report.reorder.item')
                        <li class="{{ request()->is('report/reorder/item') ? 'mm-active' : '' }}">
                            <a href="{{ route('report.reorder.item') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('item.reorder_item_report') }}</a>
                        </li>
                    @endcan



                    @if (app('company')['is_enable_crm'])
                        @can('report.order')
                            <li class="{{ request()->is('report/order') ? 'mm-active' : '' }}">
                                <a href="{{ route('report.order') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.report') }}</a>
                            </li>
                        @endcan
                        @can('report.order.payment')
                            <li class="{{ request()->is('report/order/payment') ? 'mm-active' : '' }}">
                                <a href="{{ route('report.order.payment') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.payments') }}</a>
                            </li>
                        @endcan
                        @can('report.job.status')
                            <li class="{{ request()->is('report/job-status') ? 'mm-active' : '' }}">
                                <a href="{{ route('report.job.status') }}"><i
                                        class='bx bx-radio-circle'></i>{{ __('order.job-status') }}</a>
                            </li>
                        @endcan
                    @endif

                </ul>
            </li>
        @endcanany

        @canany(['tax.view', 'app.settings.edit', 'company.edit', 'payment.type.view', 'unit.view', 'language.view'])
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-cog"></i>
                    </div>
                    <div class="menu-title">{{ __('app.settings') }}</div>
                </a>
                @canany(['app.settings.edit'])
                    <ul>
                        <li class="{{ request()->is('settings/app') ? 'mm-active' : '' }}">
                            <a href="{{ route('settings.app') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('app.app_settings') }}</a>
                        </li>
                    </ul>
                @endcanany
                @canany(['company.edit'])
                    <ul>
                        <li class="{{ request()->is('company') ? 'mm-active' : '' }}">
                            <a href="{{ route('company') }}"><i class='bx bx-radio-circle'></i>{{ __('app.company') }}</a>
                        </li>
                    </ul>
                @endcanany
                @canany(['tax.view'])
                    <ul>
                        <li class="{{ request()->is('tax*') ? 'mm-active' : '' }}">
                            <a href="{{ route('tax.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('tax.tax_rates') }}</a>
                        </li>
                    </ul>
                @endcanany

                @canany(['payment.type.view'])
                    <ul>
                        <li class="{{ request()->is('payment*') ? 'mm-active' : '' }}">
                            <a href="{{ route('payment.types.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('payment.bank_accounts') }}</a>
                        </li>
                    </ul>
                @endcanany
                @canany(['currency.view'])
                    <ul>
                        <li class="{{ request()->is('currency*') ? 'mm-active' : '' }}">
                            <a href="{{ route('currency.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('currency.list') }}</a>
                        </li>
                    </ul>
                @endcanany
                @canany(['unit.view'])
                    <ul>
                        <li class="{{ request()->is('unit*') ? 'mm-active' : '' }}">
                            <a href="{{ route('unit.list') }}"><i class='bx bx-radio-circle'></i>{{ __('unit.list') }}</a>
                        </li>
                    </ul>
                @endcanany
                @canany(['language.view'])
                    <ul>
                        <li class="{{ request()->is('language*') ? 'mm-active' : '' }}">
                            <a href="{{ route('language.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('language.languages') }}</a>
                        </li>
                    </ul>
                @endcanany

                @can('customization.edit')
<ul>
    <li class="{{ request()->is('customize/color') ? 'mm-active' : '' }}">
        <a href="{{ route('customize.edit') }}">
            <i class='bx bx-radio-circle'></i>{{ __('Customization') }}
        </a>
    </li>
</ul>
@endcan

                @can('app.log.view')
                    <ul>
                        <li class="{{ request()->is('app-log/*') ? 'mm-active' : '' }}">
                            <a href="{{ route('app.log.list') }}"><i
                                    class='bx bx-radio-circle'></i>{{ __('app.app_log') }}</a>
                        </li>
                    </ul>
                @endcan

                @can('database.backup.view')
                    <ul>
                        <li class="{{ request()->is('database-backup/list') ? 'mm-active' : '' }}">
                            <a href="{{ route('database.backup.list') }}">
                                <i class='bx bx-radio-circle'></i>{{ __('app.backup_database') }}
                            </a>
                        </li>
                    </ul>
                @endcan


            </li>
        @endcanany

        @canany(['system.clear_cache', 'system.database_backup'])
            <li class="menu-label">{{ __('app.other') }}</li>
        @endcanany

        @can('system.clear_cache')
            <ul>
                <li class="bg-light">
                    <a href="javascript:void(0);" id="clearCache">
                        <div class="parent-icon text-primary"><i class='bx bx-refresh '></i>
                        </div>
                        <div class="menu-title">{{ __('app.clear_cache') }}</div>
                    </a>
                </li>
            </ul>
        @endcan

        @can('system.database_backup')
            {{-- hidden backup form --}}
            <form class="row g-3 needs-validation" id="databaseForm" action="{{ route('database.backup') }}"
                enctype="multipart/form-data" method="post" style="display: none;">
                {{-- CSRF Protection --}}
                @csrf

                <div class="col-md-12">
                    <div class="d-md-flex d-grid align-items-center gap-3">
                        <x-button type="submit" buttonId="databaseSubmit" class="primary px-4"
                            text="{{ __('app.backup_database') }}" />
                    </div>
                </div>
            </form>

            <ul>
                <li class="bg-light">
                    <a href="javascript:void(0);" id="databaseSubmit">
                        <div class="parent-icon text-primary">
                            <i class='bx bx-data'></i>
                        </div>
                        <div class="menu-title">
                            {{ __('app.backup_database') }}
                        </div>
                    </a>
                </li>
            </ul>

            {{-- JS glue to submit the hidden form when the menu item is clicked --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const downloadLink = document.querySelector('li.bg-light a#databaseSubmit');
                    const downloadForm = document.getElementById('databaseForm');

                    if (!downloadLink) {
                        console.error('Download link not found');
                        return;
                    }
                    if (!downloadForm) {
                        console.error('Form with id="databaseForm" not found');
                        return;
                    }

                    downloadLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        downloadForm.submit();
                    });
                });
            </script>
        @endcan

        <li>
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="parent-icon text-danger"><i class='bx bx-log-out-circle'></i></div>
                <div class="menu-title text-danger">{{ __('auth.logout') }}</div>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>

    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->
