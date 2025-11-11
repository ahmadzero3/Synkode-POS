@if($model->item->brand_id && app('company')['show_brand_on_invoice'])
                            <br>
                            <small class="fw-bold">
                                <i>{{ __('item.brand.brand') }}:</i>
                            </small>
                            <small>{{ $model->item->brand->name }}</small>
                        @endif
