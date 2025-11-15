<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\AppSettings;
use App\Models\Company;
use App\Models\SmtpSettings;
use App\Http\Requests\GeneralSettingsRequest;
use App\Http\Requests\LogoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\Twilio;
use App\Models\Vonage;
use App\Enums\App;
use Illuminate\Http\Request;
use App\Services\ImageService;


class AppSettingsController extends Controller
{
    protected $appSettingsRecordId;
    protected $smtpSettingsRecordId;
    protected $companyId;

    public function __construct()
    {
        $this->appSettingsRecordId = App::APP_SETTINGS_RECORD_ID->value;
        $this->smtpSettingsRecordId = App::APP_SETTINGS_RECORD_ID->value;
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
    }

    public function index()
    {
        $data = AppSettings::findOrNew($this->appSettingsRecordId);
        $company = Company::findOrNew($this->companyId);
        $smtp = SmtpSettings::findOrNew($this->smtpSettingsRecordId);
        $twilio = Twilio::findOrNew($this->smtpSettingsRecordId);
        $vonage = Vonage::findOrNew($this->smtpSettingsRecordId);
        // $data->fevicon = $data->fevicon;
        // $data->colored_logo = $data->colored_logo;
        // $data->light_logo = $data->light_logo;

        //echo "<pre>";print_r($data);exit;
        return view('app.settings', compact('data', 'company', 'smtp', 'twilio', 'vonage'));
    }

    public function store(GeneralSettingsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Save the application settings
        $settings = AppSettings::findOrNew($this->appSettingsRecordId);
        $settings->application_name = $validatedData['application_name'];
        $settings->footer_text = $validatedData['footer_text'];
        $settings->phone_number = $validatedData['phone_number'];
        $settings->language_id = $validatedData['language_id'];
        $settings->save();

        $company = Company::findOrNew($this->companyId);
        $company->timezone = $validatedData['timezone'];
        $company->date_format = $validatedData['date_format'];
        $company->time_format = $validatedData['time_format'];
        $company->save();

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function storeTwilio(Request $request): JsonResponse
    {

        // Save the application settings
        $twilio = Twilio::findOrNew($this->appSettingsRecordId);
        $twilio->sid = $request['sid'];
        $twilio->auth_token = $request['auth_token'];
        $twilio->twilio_number = $request['twilio_number'];
        $twilio->status = $request['twilio_status'];
        $twilio->save();

        if ($request['twilio_status'] == 1) {
            $this->updateActiveSMSAPI('Twilio');
        }

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function storeVonage(Request $request): JsonResponse
    {
        // Save the application settings
        $vonage = Vonage::findOrNew($this->appSettingsRecordId);
        $vonage->api_key = $request['api_key'];
        $vonage->api_secret = $request['api_secret'];
        $vonage->status = $request['vonage_status'];
        $vonage->save();

        if ($request['vonage_status'] == 1) {
            $this->updateActiveSMSAPI('Vonage');
        }

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }

    public function updateActiveSMSAPI($active_sms_api)
    {

        $twilioStatus = Twilio::find($this->appSettingsRecordId)?->status;
        $vonageStatus = Vonage::find($this->appSettingsRecordId)?->status;

        $company = Company::find($this->companyId);
        $company->active_sms_api = (is_null($vonageStatus) && is_null($twilioStatus)) ? null : $active_sms_api;
        $company->save();

        if ($active_sms_api == 'Twilio') {
            //Update Status of Vonage inactive
            $vonage = Vonage::find($this->appSettingsRecordId);
            if ($vonage) {
                $vonage->status = 0;
                $vonage->save(); // Save the updated record
            }
        } else {
            //Update Status of Twilio inactive
            $twilio = Twilio::find($this->appSettingsRecordId);
            if ($twilio) {
                $twilio->status = 0;
                $twilio->save(); // Save the updated record
            }
        }
    }

    public function storeLogo(LogoRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $settings = AppSettings::findOrNew($this->appSettingsRecordId);

        if ($request->hasFile('fevicon') && $request->file('fevicon')->isValid()) {
            $filename = $this->uploadImage($request->file('fevicon'), $externalPath = 'fevicon');
            $settings->fevicon = $filename;
        }

        if ($request->hasFile('colored_logo') && $request->file('colored_logo')->isValid()) {
            $filename = $this->uploadImage($request->file('colored_logo'), $externalPath = 'app-logo');
            $settings->colored_logo = $filename;
        }

        if ($request->hasFile('light_logo') && $request->file('light_logo')->isValid()) {
            $filename = $this->uploadImage($request->file('light_logo'), $externalPath = 'app-logo');
            $settings->light_logo = $filename;
        }
        $settings->save();

        return response()->json([
            'message' => __('app.record_saved_successfully'),
        ]);
    }


    private function uploadImage($image, $externalPath = null)
    {
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Save the image to the storage disk
        Storage::putFileAs('public/images/' . $externalPath, $image, $filename);

        return $filename;
    }
    public function clearCache(): JsonResponse
    {
        // Clear the application cache
        Artisan::call('cache:clear');

        // Clear the view cache
        Artisan::call('view:clear');

        // Clear the route cache
        Artisan::call('route:clear');

        // Clear the configuration cache
        Artisan::call('config:clear');

        // Clear and optimize all caches
        //Artisan::call('optimize:clear');

        // Clear and clear Compiled classes
        //Artisan::call('clear-compiled');

        Artisan::call('debugbar:clear');

        // Make the route cache
        Artisan::call('route:cache');

        // make the view cache
        Artisan::call('view:cache'); //New

        // make the configuration cache
        //Artisan::call('config:cache');

        return response()->json([
            'message' => __('app.app_cache_cleared'),
        ]);
    }

    public function migrate()
    {
        // Run the database migrations
        Artisan::call('migrate');

        //seed
        Artisan::call('db:seed');
        // Clear the cache after migration

        return $this->clearCache();
    }

    public function clearAppLog()
    {
        try {
            // Clear the default log channel
            //Log::channel('stack')->clear();

            // Optionally clear other specific channels
            //Log::channel('custom')->clear();

            return response()->json([
                'status' => true,
                'message' => 'Log files cleared successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Failed to clear log files: ' . $e->getMessage()
            ], 500);
        }
    }
    public function databaseBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0775, true);
        }

        $sqlFile = $backupDir . "/backup_{$timestamp}.sql";
        $customFile = $backupDir . "/backup_{$timestamp}.backup";

        // ✅ Run pg_dump for SQL
        $commandSql = "PGPASSWORD=postgres pg_dump -h db -U postgres laravel > \"$sqlFile\"";

        // ✅ Run pg_dump for custom format (-Fc)
        $commandCustom = "PGPASSWORD=postgres pg_dump -h db -U postgres -Fc laravel > \"$customFile\"";

        exec($commandSql, $outputSql, $returnCodeSql);
        exec($commandCustom, $outputCustom, $returnCodeCustom);

        if (
            $returnCodeSql === 0 && file_exists($sqlFile) &&
            $returnCodeCustom === 0 && file_exists($customFile)
        ) {

            // Zip both files before download
            $zipFile = $backupDir . "/backup_{$timestamp}.zip";
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
                $zip->addFile($sqlFile, basename($sqlFile));
                $zip->addFile($customFile, basename($customFile));
                $zip->close();
            }

            // Clean up individual files, keep only the zip
            @unlink($sqlFile);
            @unlink($customFile);

            return response()->download($zipFile)->deleteFileAfterSend(true);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Backup failed'
            ], 500);
        }
    }

}
