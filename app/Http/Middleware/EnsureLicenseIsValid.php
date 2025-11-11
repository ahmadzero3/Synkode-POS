<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicenseIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        // always allowed routes
        $allowPatterns = [
            'login', 'logout', 'register', 'password/*',
            'license', 'license/*', 'assets/*', 'public/*', '_debugbar/*',
        ];
        
        // Check if current route is license page but license is now valid
        if ($request->is('license') || $request->is('license/*')) {
            $licenseValid = $this->checkLicenseStrict();
            if ($licenseValid) {
                // License is now valid - redirect to appropriate page
                return $this->redirectToAppropriatePage();
            }
        }
        
        foreach ($allowPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // Check license validity with STRICT validation
        $licenseValid = $this->checkLicenseStrict();

        if ($licenseValid) {
            return $next($request);
        }

        // License is invalid - redirect to activation
        Log::warning('License invalid or validation failed. Redirecting to license activation.');
        return redirect('/license')->with('error', 'License validation required. Please activate your license.');
    }

    protected function redirectToAppropriatePage()
    {
        // Check if user is authenticated
        if (auth()->check()) {
            // User is logged in - redirect to dashboard
            return redirect('/dashboard');
        } else {
            // User is not logged in - redirect to login
            return redirect('/login');
        }
    }

    protected function checkLicenseStrict()
    {
        // First, check if we have the basic license structure
        $hasLicenseStructure = $this->hasLicenseStructure();
        
        if (!$hasLicenseStructure) {
            Log::warning('No license structure found - redirecting to activation');
            return false;
        }

        // Check if license is within grace period (5 minutes for demo, adjust as needed)
        $isWithinGracePeriod = $this->checkGracePeriod(5); // 5 minutes grace period
        
        if ($isWithinGracePeriod) {
            return true;
        }

        // If grace period expired, do real-time API check
        return $this->performRealTimeLicenseCheck();
    }

    protected function hasLicenseStructure()
    {
        // Check .env for license structure
        $envHasLicense = env('LICENSE_KEY_ACTIVATION') === 'true' && 
                        env('LICENSE_KEY') === 'true' && 
                        env('LICENSE_STATUS') === 'active' &&
                        !empty(env('LICENSE_CODE'));

        // Check files for license structure
        $fileActive = false;
        $paths = [
            storage_path('app/license_status.txt'),
            'C:\\Users\\Public\\Documents\\license_status.txt',
        ];
        
        foreach ($paths as $p) {
            if (is_file($p)) {
                $txt = @file_get_contents($p);
                if ($txt && stripos($txt, 'status=active') !== false) {
                    $fileActive = true;
                    break;
                }
            }
        }

        return $envHasLicense || $fileActive;
    }

    protected function checkGracePeriod($graceMinutes = 5)
    {
        $lastChecked = $this->getLastCheckedTimestamp();
        
        if (!$lastChecked) {
            Log::warning('No last checked timestamp found');
            return false;
        }

        try {
            $lastCheckedDate = Carbon::parse($lastChecked);
            $gracePeriodEnd = $lastCheckedDate->addMinutes($graceMinutes);
            
            if (Carbon::now()->lte($gracePeriodEnd)) {
                Log::info('License within grace period. Last checked: ' . $lastChecked);
                return true;
            } else {
                Log::warning('License grace period expired. Last checked: ' . $lastChecked);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error parsing last checked date: ' . $e->getMessage());
            return false;
        }
    }

    protected function getLastCheckedTimestamp()
    {
        $paths = [
            storage_path('app/license_status.txt'),
            'C:\\Users\\Public\\Documents\\license_status.txt',
        ];
        
        foreach ($paths as $p) {
            if (is_file($p)) {
                $txt = @file_get_contents($p);
                if ($txt && preg_match('/last_checked=([^\n]+)/', $txt, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        return null;
    }

    protected function performRealTimeLicenseCheck()
    {
        $licenseCode = env('LICENSE_CODE');
        
        if (empty($licenseCode)) {
            Log::warning('No license code found for real-time check');
            return false;
        }

        try {
            $apiUrl = 'https://license-key.nexacore.store/api/license/all';
            $apiKey = 'ZR3xK2P8j9V4M1Lq8Wb6N7';

            $response = Http::timeout(10)->get($apiUrl, [
                'api_key' => $apiKey,
            ]);

            if ($response->ok()) {
                $data = $response->json();
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $licenseFound = collect($data['data'])->firstWhere('license_code', $licenseCode);
                    
                    if ($licenseFound) {
                        if (strtolower($licenseFound['status']) === 'active') {
                            // License is ACTIVE - update system and allow access
                            $this->reactivateLicense($licenseFound, $licenseCode);
                            Log::info('Real-time license check: License is active - reactivated');
                            return true;
                        } else {
                            // License is INACTIVE - block access
                            Log::warning('Real-time license check: License is inactive');
                            return false;
                        }
                    } else {
                        // License not found in API
                        Log::warning('Real-time license check: License not found in API');
                        return false;
                    }
                }
            }
            
            Log::warning('Real-time license check: Invalid API response');
            return false;
            
        } catch (\Throwable $e) {
            Log::error('Real-time license check failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function reactivateLicense($licenseFound, $licenseCode)
    {
        $customerName = $licenseFound['customer']['full_name'] ?? 'Unknown';
        $projectName = $licenseFound['project']['name'] ?? 'Unknown';
        $expiryDate = $licenseFound['expiry_date'] ?? '';

        // Update .env with active status
        $this->updateEnv([
            'LICENSE_KEY_ACTIVATION' => 'true',
            'LICENSE_KEY' => 'true',
            'LICENSE_STATUS' => 'active',
            'LICENSE_CODE' => $licenseCode,
            'CUSTOMER_NAME' => $customerName,
            'PROJECT_NAME' => $projectName,
            'LICENSE_EXPIRY' => $expiryDate,
        ]);

        // Update license files with active status and current timestamp
        $currentTime = Carbon::now()->toDateTimeString();
        $licenseText = "status=active\nlicense={$licenseCode}\ncustomer={$customerName}\nproject={$projectName}\nexpiry={$expiryDate}\nlast_checked={$currentTime}\n";

        $this->updateLicenseFiles($licenseText);

        // Clear config cache
        Artisan::call('config:clear');
        
        Log::info('License reactivated successfully', [
            'license' => $licenseCode,
            'customer' => $customerName,
            'project' => $projectName
        ]);
    }

    protected function updateEnv(array $values)
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
}