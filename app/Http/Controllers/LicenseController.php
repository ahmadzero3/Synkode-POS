<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\AppSettings;

class LicenseController extends Controller
{
    protected $apiUrl = 'https://license-key.nexacore.store/api/license/all';
    protected $apiKey = 'ZR3xK2P8j9V4M1Lq8Wb6N7';

    public function show()
    {
        // First, check if license is now valid (auto-redirect if valid)
        if ($this->isLicenseValid()) {
            return $this->redirectToAppropriatePage();
        }

        // Fetch phone number from AppSettings and pass to the view
        $settings = AppSettings::first();
        $phone_number = $settings->phone_number ?? 'N/A';

        return view('license-key', compact('phone_number'));
    }

    public function verify(Request $request)
    {
        // Clean up any existing invalid license data first
        $this->cleanupInvalidLicense();
        
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $licenseInput = trim($request->input('license_key'));

        Log::info('License verification attempt', ['license' => $licenseInput]);

        try {
            $response = Http::timeout(15)->get($this->apiUrl, [
                'api_key' => $this->apiKey,
            ]);
        } catch (\Throwable $e) {
            Log::error('License API unreachable: ' . $e->getMessage());
            return redirect()->route('license.show')->withErrors(['license_key' => 'Cannot reach license server.']);
        }

        if (!$response->ok()) {
            Log::error('License API error: ' . $response->status());
            return redirect()->route('license.show')->withErrors(['license_key' => 'License server error.']);
        }

        $data = $response->json();
        if (!isset($data['data']) || !is_array($data['data']) || count($data['data']) === 0) {
            return redirect()->route('license.show')->withErrors(['license_key' => 'Invalid response from server.']);
        }

        $licenseFound = collect($data['data'])->firstWhere('license_code', $licenseInput);
        if (!$licenseFound) {
            Log::warning('License not found', ['license' => $licenseInput]);
            return redirect()->route('license.show')->withErrors(['license_key' => 'License not found.']);
        }

        if (strtolower($licenseFound['status']) !== 'active') {
            Log::warning('License not active', ['license' => $licenseInput, 'status' => $licenseFound['status']]);
            return redirect()->route('license.show')->withErrors(['license_key' => 'This license is not active.']);
        }

        $customerName = $licenseFound['customer']['full_name'] ?? 'Unknown';
        $projectName = $licenseFound['project']['name'] ?? 'Unknown';
        $licenseCode = $licenseFound['license_code'];
        $expiryDate = $licenseFound['expiry_date'] ?? '';

        Log::info('License activated successfully', [
            'license' => $licenseCode,
            'customer' => $customerName,
            'project' => $projectName,
            'expiry' => $expiryDate
        ]);

        // --- Update .env with ALL values in quotes ---
        $this->updateEnv([
            'LICENSE_KEY_ACTIVATION' => 'true',
            'LICENSE_KEY' => 'true',
            'LICENSE_STATUS' => 'active',
            'LICENSE_CODE' => $licenseCode,
            'CUSTOMER_NAME' => $customerName,
            'PROJECT_NAME' => $projectName,
            'LICENSE_EXPIRY' => $expiryDate,
        ]);

        // --- Create both files ---
        $localPath = storage_path('app/license_status.txt');
        $publicPath = 'C:\\Users\\Public\\Documents\\license_status.txt'; // PC-level file

        $currentTime = now()->toDateTimeString();
        $licenseText = "status=active\nlicense={$licenseCode}\ncustomer={$customerName}\nproject={$projectName}\nexpiry={$expiryDate}\nlast_checked={$currentTime}\n";

        try {
            File::put($localPath, $licenseText);
        } catch (\Throwable $e) {
            Log::error('Failed to write local license file: ' . $e->getMessage());
        }

        try {
            $dir = dirname($publicPath);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true);
            }
            File::put($publicPath, $licenseText);
        } catch (\Throwable $e) {
            Log::error('Failed to write PC license file: ' . $e->getMessage());
        }

        Artisan::call('config:clear');

        // Always redirect to appropriate page after successful verification
        return $this->redirectToAppropriatePage()->with('status', 'License activated successfully!');
    }

    protected function isLicenseValid()
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

        return $envHasLicense && $fileActive;
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

    protected function cleanupInvalidLicense()
    {
        // Remove any invalid license files
        $paths = [
            storage_path('app/license_status.txt'),
            'C:\\Users\\Public\\Documents\\license_status.txt',
        ];
        
        foreach ($paths as $path) {
            if (File::exists($path)) {
                try {
                    File::delete($path);
                } catch (\Throwable $e) {
                    Log::error('Failed to delete license file: ' . $e->getMessage());
                }
            }
        }
        
        // Clear any cached license config
        Artisan::call('config:clear');
    }

    /**
     * Update .env values safely (ALL values in quotes)
     */
    private function updateEnv(array $values)
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
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