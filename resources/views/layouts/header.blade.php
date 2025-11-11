<!--start header -->
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    @can('purchase.bill.create')
                        <div class="d-flex">
                            <a href="{{ route('purchase.bill.create') }}"
                                class="btn btn-sm btn-outline-primary radius-30 px-4" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" title="{{ __('purchase.add') }}">
                                <i class="bx bx-plus-circle mr-1"></i>{{ __('purchase.purchase') }}
                            </a>
                        </div>
                    @endcan

                    @can('sale.bill.create')
                        <div class="d-flex">
                            <a href="{{ route('sale.invoice.create') }}"
                                class="btn btn-sm btn-outline-danger radius-30 px-4" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" title="{{ __('sale.add') }}">
                                <i class="bx bx-plus-circle mr-1"></i>{{ __('sale.sale') }}
                            </a>
                        </div>
                    @endcan

                    @can('sale.invoice.create')
                        <div class="d-flex">
                            <a href="{{ route('pos.create') }}" class="btn btn-sm btn-success radius-30 px-4">
                                <i class="bx bx-plus-circle mr-1"></i>{{ __('sale.pos') }}
                            </a>
                        </div>
                    @endcan

                    @can('notification.view')
                        {{-- Show notification dropdown only if minimum stock quantity is enabled --}}
                        @if (app('company')['enable_minimum_stock_qty'])
                            <li class="nav-item position-relative">
                                <a href="#" class="btn btn-sm btn-outline-secondary notification-circle"
                                    data-bs-toggle="dropdown">
                                    <i class='bx bx-bell bell-icon'></i>
                                    <span
                                        class="notification-badge position-absolute top-0 start-100 translate-middle badge">
                                        0
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-start notification-dropdown">
                                    <div id="low-stock-items"></div>
                                    <li class="dropdown-item text-end">
                                        <a href="{{ route('item.list') }}">{{ __('app.view_all') }} &gt;</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @endcan

                    <x-header-shortcut-menu />
                    <x-flag-toggle />

                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon theme-mode" data-base-url="{{ url('/') }}"
                            href="javascript:;">
                            <i class='bx bx-moon'></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="user-box dropdown px-3">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret"
                    href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('/users/getimage/' . auth()->user()->avatar) }}" class="user-img"
                        alt="user avatar">
                    <div class="user-info">
                        <p class="user-name mb-0">{{ auth()->user()->username }}</p>
                        <p class="designattion mb-0">{{ auth()->user()->role->name }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('user.profile') }}">
                            <i class="bx bx-user fs-5"></i><span>{{ __('user.profile') }}</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center">
                                <i class="bx bx-log-out-circle"></i>{{ __('auth.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<!--end header -->
