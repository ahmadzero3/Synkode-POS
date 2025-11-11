@extends('layouts.app')
@section('title', __('app.customization'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumb :langArray="['app.settings', 'app.customization']" />
        <div class="row">
            <div class="container">
                <div class="card">
                    <div class="card-header px-4 py-3">
                        <h5 class="mb-0">{{ __('app.customization') }}</h5>
                    </div>
                    <div class="card-body">
                        <form class="row g-3 needs-validation" id="customizationForm" action="{{ route('customize.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="base_url" value="{{ url('/') }}">

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="py-3" for="color">{{ __('app.card_header_color') }}</label>
                                        <input type="color" id="color" name="color" value="{{ $color }}" class="form-control" style="width: 100px;">
                                    </div>
                                    <div class="form-group">
                                        <label class="py-3" for="border_color">{{ __('app.card_border_color') }}</label>
                                        <input type="color" id="border_color" name="border_color" value="{{ $borderColor }}" class="form-control" style="width: 100px;">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="py-3" for="heading_color">{{ __('app.heading_color') }}</label>
                                        <input type="color" id="heading_color" name="heading_color" value="{{ $headingColor }}" class="form-control" style="width: 100px;">
                                    </div>

                                    <div class="form-group mt-3">
                                        <label class="py-3" for="toggle_switch">{{ __('app.trending_items') }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="toggle_switch" name="toggle_switch"
                                                style="transform: scale(1.7); margin-left: -22px;"
                                                {{ old('toggle_switch', $toggle_switch === 'active') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload Fields -->
                            <div class="row mt-4">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="py-3" for="image_1">{{ __('app.image_1') }}</label>
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="position-relative me-3">
                                                @if($image1 && Storage::exists('public/images/customization/'.$image1))
                                                    <img id="image_1_preview" src="{{ asset('storage/images/customization/'.$image1) }}" 
                                                         alt="Image 1" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                    <button type="button" class="btn btn-outline-danger btn-xs delete-image position-absolute rounded-circle border-0 shadow-sm" 
                                                            data-image-key="image_1" data-image-name="{{ $image1 }}" 
                                                            style="top: -6px; right: -6px; width: 24px; height: 24px; padding: 0; background: rgba(255,255,255,0.9);">
                                                        <i class="bx bx-x" style="font-size: 14px; line-height: 1;"></i>
                                                    </button>
                                                @else
                                                    <img id="image_1_preview" src="{{ url('/noimage') }}" 
                                                         alt="Image 1" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="file" id="image_1" name="image_1" class="form-control" accept="image/*">
                                                <small class="text-muted" id="image_1_filename">
                                                    @if($image1)
                                                        {{ $image1 }}
                                                    @else
                                                        No image selected
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="py-3" for="image_2">{{ __('app.image_2') }}</label>
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="position-relative me-3">
                                                @if($image2 && Storage::exists('public/images/customization/'.$image2))
                                                    <img id="image_2_preview" src="{{ asset('storage/images/customization/'.$image2) }}" 
                                                         alt="Image 2" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                    <button type="button" class="btn btn-outline-danger btn-xs delete-image position-absolute rounded-circle border-0 shadow-sm" 
                                                            data-image-key="image_2" data-image-name="{{ $image2 }}" 
                                                            style="top: -6px; right: -6px; width: 24px; height: 24px; padding: 0; background: rgba(255,255,255,0.9);">
                                                        <i class="bx bx-x" style="font-size: 14px; line-height: 1;"></i>
                                                    </button>
                                                @else
                                                    <img id="image_2_preview" src="{{ url('/noimage') }}" 
                                                         alt="Image 2" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="file" id="image_2" name="image_2" class="form-control" accept="image/*">
                                                <small class="text-muted" id="image_2_filename">
                                                    @if($image2)
                                                        {{ $image2 }}
                                                    @else
                                                        No image selected
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="py-3" for="image_3">{{ __('app.image_3') }}</label>
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="position-relative me-3">
                                                @if($image3 && Storage::exists('public/images/customization/'.$image3))
                                                    <img id="image_3_preview" src="{{ asset('storage/images/customization/'.$image3) }}" 
                                                         alt="Image 3" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                    <button type="button" class="btn btn-outline-danger btn-xs delete-image position-absolute rounded-circle border-0 shadow-sm" 
                                                            data-image-key="image_3" data-image-name="{{ $image3 }}" 
                                                            style="top: -6px; right: -6px; width: 24px; height: 24px; padding: 0; background: rgba(255,255,255,0.9);">
                                                        <i class="bx bx-x" style="font-size: 14px; line-height: 1;"></i>
                                                    </button>
                                                @else
                                                    <img id="image_3_preview" src="{{ url('/noimage') }}" 
                                                         alt="Image 3" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="file" id="image_3" name="image_3" class="form-control" accept="image/*">
                                                <small class="text-muted" id="image_3_filename">
                                                    @if($image3)
                                                        {{ $image3 }}
                                                    @else
                                                        No image selected
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="d-md-flex d-grid align-items-center gap-3 justify-content-end">
                                    <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                    <x-anchor-tag href="{{ route('dashboard') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ versionedAsset('custom/js/customization/customization.js') }}"></script>
@endsection