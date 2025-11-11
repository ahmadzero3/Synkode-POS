<li class="nav-item dropdown dropdown-app">
                                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown" href="javascript:;"><i class='bx bx-grid-alt'></i></a>
                                <div class="dropdown-menu dropdown-menu-end p-0 cu-w-400-px">
                                    <div class="">
                                      <div class="row gx-0 gy-2 row-cols-2 justify-content-center p-2">
                                        @can('sale.invoice.create')
                                         <div class="col">
                                              <a href="{{ route('sale.invoice.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('sale.sale_invoice') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('purchase.bill.create')
                                         <div class="col">
                                              <a href="{{ route('purchase.bill.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('purchase.bill') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('sale.invoice.create')
                                         <div class="col">
                                              <a href="{{ route('sale.payment.in') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('payment.payment_in') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('purchase.bill.view')
                                         <div class="col">
                                              <a href="{{ route('purchase.payment.out') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('payment.payment_out') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('customer.create')
                                         <div class="col">
                                              <a href="{{ route('party.create', ['partyType' => 'customer']) }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('customer.customer') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('supplier.create')
                                         <div class="col">
                                              <a href="{{ route('party.create', ['partyType' => 'supplier']) }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('supplier.supplier') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan



                                        @can('purchase.order.create')
                                         <div class="col">
                                              <a href="{{ route('purchase.order.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('purchase.order.order') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan


                                        @can('sale.return.create')
                                         <div class="col">
                                              <a href="{{ route('sale.return.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('sale.sale_return') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('purchase.return.create')
                                         <div class="col">
                                              <a href="{{ route('purchase.return.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('purchase.purchase_return') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan
                                        @can('stock_transfer.create')
                                         <div class="col">
                                              <a href="{{ route('stock_transfer.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('warehouse.stock_transfer') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('item.create')
                                         <div class="col">
                                              <a href="{{ route('item.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('item.item') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('sale.quotation.create')
                                         <div class="col">
                                              <a href="{{ route('sale.quotation.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('sale.quotation.quotation') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        @can('expense.create')
                                         <div class="col">
                                              <a href="{{ route('expense.create') }}">
                                                <div class="app-box d-flex align-items-center">
                                                  <div class="app-icon me-2">
                                                    <i class='bx bxs-right-arrow' ></i>
                                                  </div>
                                                  <div class="app-name">
                                                    <p class="mb-0 fs-6">{{ __('expense.expense') }}</p>
                                                  </div>
                                                </div>
                                              </a>
                                        </div>
                                        @endcan

                                        <div class="col">
                                        </div>
                                        <div class="col">
                                        </div>


                                      </div><!--end row-->

                                    </div>
                                </div>
                            </li>
