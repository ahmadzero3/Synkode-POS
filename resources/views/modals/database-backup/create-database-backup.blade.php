<div class="modal fade" id="createDatabaseBackupModal" tabindex="-1" aria-labelledby="createDatabaseBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDatabaseBackupModalLabel">{{ __('app.create_database_backup') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createBackupForm" action="{{ route('database.backup.create') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <!-- Backup Type Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-bold mb-2">{{ __('app.backup_type') }}</label>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="backup_type_full" value="full" checked>
                                        <label class="form-check-label" for="backup_type_full">
                                            {{ __('app.full_backup') }} <small class="text-muted">(ZIP with SQL, PGSQL + inner ZIP)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="backup_type_single_zip" value="single_zip">
                                        <label class="form-check-label" for="backup_type_single_zip">
                                            {{ __('app.single_zip_backup') }} <small class="text-muted">(One ZIP with SQL & PGSQL)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="backup_type_pgsql" value="pgsql">
                                        <label class="form-check-label" for="backup_type_pgsql">
                                            {{ __('app.pgsql_backup') }} <small class="text-muted">(PGSQL file only)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backup_type" id="backup_type_sql" value="sql">
                                        <label class="form-check-label" for="backup_type_sql">
                                            {{ __('app.sql_backup') }} <small class="text-muted">(SQL file only)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Type Section -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold mb-2">{{ __('app.schedule_type') }}</label>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_monthly" value="monthly">
                                        <label class="form-check-label" for="schedule_type_monthly">
                                            {{ __('app.monthly') }} <small class="text-muted">(End of month at 00:00)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_weekly" value="weekly">
                                        <label class="form-check-label" for="schedule_type_weekly">
                                            {{ __('app.weekly') }} <small class="text-muted">(End of week at 00:00)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_daily" value="daily">
                                        <label class="form-check-label" for="schedule_type_daily">
                                            {{ __('app.daily') }} <small class="text-muted">(End of day at 00:00)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="schedule_type_now" value="now" checked>
                                        <label class="form-check-label" for="schedule_type_now">
                                            {{ __('app.now') }} <small class="text-muted">(Generate immediately)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.close') }}</button>
                    <x-button type="submit" class="btn btn-primary" text="{{ __('app.submit') }}" />
                </div>
            </form>
        </div>
    </div>
</div>
