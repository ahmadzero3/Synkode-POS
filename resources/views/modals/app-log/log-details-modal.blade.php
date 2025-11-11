<!-- Modal for Log Details -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('app.log_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>{{ __('app.type') }}:</strong>
                        <span id="detail-type"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>{{ __('app.severity') }}:</strong>
                        <span id="detail-severity"></span>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>{{ __('app.message') }}:</strong>
                        <p id="detail-message" class="mt-1"></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>{{ __('app.created_at') }}:</strong>
                        <span id="detail-created-at"></span>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>{{ __('app.details') }}:</strong>
                        <pre id="detail-content" class="mt-2 p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('app.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
