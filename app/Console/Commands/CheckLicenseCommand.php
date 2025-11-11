<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CheckLicenseCommand extends Command
{
    protected $signature = 'license:check';
    protected $description = 'Check license status against remote API every 5 minutes';

    protected $apiBase = 'https://license-key.nexacore.store/api/license/all';
    protected $apiKey = 'ZR3xK2P8j9V4M1Lq8Wb6N7';

    public function handle()
    {
        $this->info('Starting license check...');
        Log::info('License check started');

        $license = env('LICENSE_CODE', null);

        if (empty($license)) {
            $this->warn('No LICENSE_CODE found in env.');
            Log::warning('License check: no license code found');
            $this->markLicenseAsInvalid();
            return 1;
        }

        $this->info("Checking license: {$license}");

        try {
            $response = Http::timeout(15)->get($this->apiBase, [
                'api_key' => $this->apiKey,
            ]);
        } catch (\Exception $e) {
            Log::error('License check failed (offline): '.$e->getMessage());
            $this->error('License check failed - no internet connection.');
            
            // Update last_checked timestamp but don't mark as invalid immediately
            $this->updateLastCheckedTimestamp();
            return 2;
        }

        if (!$response->ok()) {
            Log::warning('License check returned non-200: '.$response->status());
            $this->error('License server error: '.$response->status());
            
            $this->updateLastCheckedTimestamp();
            return 3;
        }

        $data = $response->json();
        
        if (!isset($data['data']) || !is_array($data['data']) || count($data['data']) === 0) {
            Log::warning('License check: Invalid response from server');
            $this->error('Invalid response from license server.');
            
            $this->updateLastCheckedTimestamp();
            return 4;
        }

        $licenseFound = collect($data['data'] ?? [])->firstWhere('license_code', $license);
        
        if ($licenseFound) {
            if (strtolower($licenseFound['status']) === 'active') {
                $this->handleActiveLicense($licenseFound, $license);
                return 0;
            } else {
                $this->handleInactiveLicense($license);
                return 5;
            }
        } else {
            $this->handleLicenseNotFound($license);
            return 6;
        }
    }

    protected function handleActiveLicense($licenseFound, $licenseCode)
    {
        $customerName = $licenseFound['customer']['full_name'] ?? 'Unknown';
        $projectName  = $licenseFound['project']['name'] ?? 'Unknown';
        $expiryDate   = $licenseFound['expiry_date'] ?? '';

        // Update .env with all values
        $this->updateEnv([
            'LICENSE_KEY_ACTIVATION' => 'true',
            'LICENSE_KEY' => 'true',
            'LICENSE_STATUS' => 'active',
            'LICENSE_CODE' => $licenseCode,
            'CUSTOMER_NAME' => $customerName,
            'PROJECT_NAME' => $projectName,
            'LICENSE_EXPIRY' => $expiryDate,
        ]);

        // Update license files with last_checked timestamp
        $currentTime = Carbon::now()->toDateTimeString();
        $licenseText = "status=active\nlicense={$licenseCode}\ncustomer={$customerName}\nproject={$projectName}\nexpiry={$expiryDate}\nlast_checked={$currentTime}\n";

        $this->updateLicenseFiles($licenseText);

        \Artisan::call('config:clear');
        
        $this->info('✅ License is active and validated.');
        Log::info('License check: License active', [
            'license' => $licenseCode,
            'customer' => $customerName,
            'expiry' => $expiryDate,
            'last_checked' => $currentTime
        ]);
    }

    protected function handleInactiveLicense($licenseCode)
    {
        // License inactive -> mark as inactive
        $this->markLicenseAsInvalid();

        Log::warning('License check: License inactive', ['license' => $licenseCode]);
        $this->error('❌ License is inactive. Application access will be restricted.');
    }

    protected function handleLicenseNotFound($licenseCode)
    {
        // License not found in API -> mark as invalid
        $this->markLicenseAsInvalid();

        Log::warning('License check: License not found in API', ['license' => $licenseCode]);
        $this->error('❌ License not found in API. Application access will be restricted.');
    }

    protected function markLicenseAsInvalid()
    {
        $this->updateEnv([
            'LICENSE_KEY_ACTIVATION' => 'false',
            'LICENSE_KEY' => 'false',
            'LICENSE_STATUS' => 'inactive',
        ]);

        $currentTime = Carbon::now()->toDateTimeString();
        $licenseText = "status=inactive\nlicense=\ncustomer=\nproject=\nexpiry=\nlast_checked={$currentTime}\n";

        $this->updateLicenseFiles($licenseText);

        \Artisan::call('config:clear');
    }

    protected function updateLastCheckedTimestamp()
    {
        $currentTime = Carbon::now()->toDateTimeString();
        $licenseCode = env('LICENSE_CODE', '');
        $customerName = env('CUSTOMER_NAME', 'Unknown');
        $projectName = env('PROJECT_NAME', 'Unknown');
        $expiryDate = env('LICENSE_EXPIRY', '');
        $status = env('LICENSE_STATUS', 'active');
        
        $licenseText = "status={$status}\nlicense={$licenseCode}\ncustomer={$customerName}\nproject={$projectName}\nexpiry={$expiryDate}\nlast_checked={$currentTime}\n";

        $this->updateLicenseFiles($licenseText);
        
        $this->info('📅 Updated last checked timestamp');
        Log::info('Updated last checked timestamp', ['last_checked' => $currentTime]);
    }

    protected function updateLicenseFiles($licenseText)
    {
        $localPath = storage_path('app/license_status.txt');
        $publicPath = 'C:\\Users\\Public\\Documents\\license_status.txt';

        try {
            File::put($localPath, $licenseText);
        } catch (\Throwable $e) {
            Log::error('Failed to write local license file: '.$e->getMessage());
        }

        try {
            $dir = dirname($publicPath);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true);
            }
            File::put($publicPath, $licenseText);
        } catch (\Throwable $e) {
            Log::error('Failed to write PC license file: '.$e->getMessage());
        }
    }

    /**
     * Update .env values safely (ALL values in quotes)
     */
    private function updateEnv(array $values)
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            Log::error('ENV file not found at: ' . $envPath);
            return false;
        }

        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $value = '"' . str_replace('"', '\"', $value) . '"';
            $pattern = "/^{$key}=.*$/m";
            $line = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= "\n{$line}";
            }
        }

        File::put($envPath, trim($content) . "\n");
        return true;
    }
}