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
        $allowPatterns = [
            'login',
            'logout',
            'register',
            'password/*',
            'license',
            'license/*',
            'assets/*',
            'public/*',
            '_debugbar/*',
        ];

        // Allow license route if license already valid
        if ($request->is('license') || $request->is('license/*')) {
            if ($this->checkLicenseStrict()) {
                return $this->redirectToAppropriatePage();
            }
        }

        foreach ($allowPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if ($this->checkLicenseStrict()) {
            return $next($request);
        }

        Log::warning('License invalid or expired → redirecting to license activation.');
        return redirect('/license')->with('error', 'License validation required.');
    }

    protected function redirectToAppropriatePage()
    {
        return auth()->check() ? redirect('/dashboard') : redirect('/login');
    }

    // ----------------------------------------------------------
    // ✅ MAIN VALIDATION LOGIC
    // ----------------------------------------------------------
    protected function checkLicenseStrict()
    {
        $internetAvailable = $this->isInternetAvailable();
        $cache = $this->readEncryptedLicenseCache();

        if ($cache) {
            try {
                $expiry = isset($cache['expiry_date']) ? Carbon::parse($cache['expiry_date']) : null;
                $now = Carbon::now();

                // ❌ Expired by API date
                if ($expiry && $now->gt($expiry)) {
                    Log::warning('License expired on ' . $expiry->toDateTimeString());
                    return false;
                }

                // ✅ If online and cache fresh (≤ 1 day)
                $lastChecked = Carbon::parse($cache['last_checked']);
                if ($internetAvailable && $lastChecked->diffInDays($now) <= 1) {
                    Log::info('✅ License validated (online, within 1 day).');
                    // 🩵 FIX: Reset offline state on reconnect
                    $this->resetOfflineState();
                    return true;
                }

                // 📴 If offline, apply offline cache validation
                if (!$internetAvailable) {
                    $this->markOfflineStart();
                    if ($this->checkOfflineCacheValidity($cache)) {
                        session(['offline_mode' => true]);
                        Log::info('🛰️ Using offline grace period.');
                        return true;
                    }
                    Log::warning('❌ Offline grace expired.');
                    return false;
                }
            } catch (\Throwable $e) {
                Log::error('Cache parse error: ' . $e->getMessage());
            }
        }

        // fallback to structure/grace check
        if (!$this->hasLicenseStructure()) return false;
        if ($this->checkGracePeriod(5)) return true;

        // otherwise full online check
        return $this->performRealTimeLicenseCheck();
    }

    // ----------------------------------------------------------
    // 🔍 Helper methods
    // ----------------------------------------------------------
    protected function isInternetAvailable()
    {
        try {
            $ping = @fsockopen("8.8.8.8", 53, $errno, $errstr, 2);
            if ($ping) { fclose($ping); return true; }
        } catch (\Throwable $e) {}
        return false;
    }

    protected function hasLicenseStructure()
    {
        $envHasLicense = env('LICENSE_KEY_ACTIVATION') === 'true' &&
            env('LICENSE_STATUS') === 'active' &&
            !empty(env('LICENSE_CODE'));
        $fileActive = false;
        foreach ([storage_path('app/license_status.txt'), 'C:\\Users\\Public\\Documents\\license_status.txt'] as $p) {
            if (is_file($p) && stripos(@file_get_contents($p), 'status=active') !== false) $fileActive = true;
        }
        return $envHasLicense || $fileActive;
    }

    protected function checkGracePeriod($minutes = 5)
    {
        $last = $this->getLastCheckedTimestamp();
        if (!$last) return false;
        $end = Carbon::parse($last)->addMinutes($minutes);
        return Carbon::now()->lte($end);
    }

    protected function getLastCheckedTimestamp()
    {
        foreach ([storage_path('app/license_status.txt'), 'C:\\Users\\Public\\Documents\\license_status.txt'] as $p) {
            if (is_file($p) && preg_match('/last_checked=([^\n]+)/', file_get_contents($p), $m)) return $m[1];
        }
        return null;
    }

    // ----------------------------------------------------------
    // 🌐 Online verification
    // ----------------------------------------------------------
    protected function performRealTimeLicenseCheck()
    {
        $licenseCode = env('LICENSE_CODE');
        if (!$licenseCode) return false;

        try {
            $apiUrl = 'https://license-key.nexacore.store/api/license/all';
            $apiKey = 'ZR3xK2P8j9V4M1Lq8Wb6N7';
            $response = Http::timeout(10)->get($apiUrl, ['api_key' => $apiKey]);
            if (!$response->ok()) return false;

            $data = $response->json();
            $licenseFound = collect($data['data'] ?? [])->firstWhere('license_code', $licenseCode);
            if (!$licenseFound) return false;

            $status = strtolower($licenseFound['status']);
            if ($status !== 'active') return false;

            $this->reactivateLicense($licenseFound, $licenseCode);
            $this->setPersistentOfflineTimestamp();

            // 🩵 FIX: clear offline state when reconnected
            $this->resetOfflineState();

            return true;
        } catch (\Throwable $e) {
            Log::error('License check failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function reactivateLicense($info, $code)
    {
        $customer = $info['customer']['full_name'] ?? 'Unknown';
        $project  = $info['project']['name'] ?? 'Unknown';
        $expiry   = $info['expiry_date'] ?? '';

        $now = Carbon::now()->toDateTimeString();
        $txt = "status=active\nlicense={$code}\ncustomer={$customer}\nproject={$project}\nexpiry={$expiry}\nlast_checked={$now}\n";
        $this->updateLicenseFiles($txt);

        $this->saveEncryptedLicenseCache([
            'license_code' => $code,
            'expiry_date'  => $expiry,
            'last_checked' => $now,
        ]);

        Artisan::call('config:clear');
    }

    protected function updateEnv(array $values)
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) return false;
        $content = File::get($envPath);
        foreach ($values as $k => $v) {
            $v = '"' . str_replace('"', '\"', $v) . '"';
            $line = "{$k}={$v}";
            $pattern = "/^{$k}=.*$/m";
            $content = preg_match($pattern, $content) ? preg_replace($pattern, $line, $content) : $content . "\n{$line}";
        }
        File::put($envPath, trim($content) . "\n");
        return true;
    }

    protected function updateLicenseFiles($txt)
    {
        foreach ([storage_path('app/license_status.txt'), 'C:\\Users\\Public\\Documents\\license_status.txt'] as $p) {
            try {
                File::ensureDirectoryExists(dirname($p));
                File::put($p, $txt);
            } catch (\Throwable $e) { Log::error($e->getMessage()); }
        }
    }

    protected function saveEncryptedLicenseCache(array $data)
    {
        try {
            $cacheDir = storage_path('framework/.license_cache');
            $file = $cacheDir . '/license_status.enc';
            File::ensureDirectoryExists($cacheDir);

            $existing = $this->readEncryptedLicenseCache();
            if ($existing && isset($existing['offline_start_at'])) {
                $data['offline_start_at'] = $existing['offline_start_at'];
            }

            $data['fingerprint'] = hash('sha256', php_uname() . gethostname());
            $data['cache_validity_minutes'] = 10080; // 🎯 CHANGED: 7 days in minutes
            $data['signature'] = hash('sha256', $data['license_code'] . $data['fingerprint'] . env('APP_KEY'));
            if (!isset($data['offline_start_at'])) $data['offline_start_at'] = Carbon::now()->toDateTimeString();

            File::put($file, encrypt(json_encode($data)));
            chmod($file, 0600);
            Log::info('🔐 Encrypted cache saved.');
        } catch (\Throwable $e) {
            Log::error('Cache save failed: ' . $e->getMessage());
        }
    }

    protected function readEncryptedLicenseCache()
    {
        $file = storage_path('framework/.license_cache/license_status.enc');
        if (!File::exists($file)) return null;
        try {
            $data = json_decode(decrypt(File::get($file)), true);
            $expected = hash('sha256', $data['license_code'] . $data['fingerprint'] . env('APP_KEY'));
            if (!hash_equals($expected, $data['signature'])) {
                Log::error('Cache tampering detected!');
                return null;
            }
            return $data;
        } catch (\Throwable $e) {
            Log::error('Cache read error: ' . $e->getMessage());
            return null;
        }
    }

    // ✅ Persistent offline timer (fix for instant disconnect)
    protected function getPersistentOfflineTimestamp()
    {
        $file = storage_path('framework/.license_cache/offline_timer.txt');
        if (File::exists($file)) return trim(File::get($file));
        return null;
    }

    protected function setPersistentOfflineTimestamp($timestamp = null)
    {
        $file = storage_path('framework/.license_cache/offline_timer.txt');
        File::ensureDirectoryExists(dirname($file));
        $time = $timestamp ?: Carbon::now()->toDateTimeString();
        File::put($file, $time);
        Log::info('🕒 Persistent offline timer written: ' . $time);
    }

    // 🩵 FIX: Reset offline state cleanly on reconnect
    protected function resetOfflineState()
    {
        try {
            $timer = storage_path('framework/.license_cache/offline_timer.txt');
            if (File::exists($timer)) File::delete($timer);

            $cache = $this->readEncryptedLicenseCache();
            if ($cache && isset($cache['offline_start_at'])) {
                unset($cache['offline_start_at']);
                $this->saveEncryptedLicenseCache($cache);
            }

            Log::info('🔁 Offline state reset after reconnect.');
        } catch (\Throwable $e) {
            Log::error('Reset offline state error: ' . $e->getMessage());
        }
    }

    // ✅ offline timer logic - FIXED VERSION
    protected function markOfflineStart()
    {
        $cache = $this->readEncryptedLicenseCache();
        $existing = $cache['offline_start_at'] ?? null;
        $fileTime = $this->getPersistentOfflineTimestamp();

        // 🎯 FIX: Always set fresh offline start time when going offline
        // This ensures every disconnection gets a full 5 minutes
        if (!$fileTime) {
            $now = Carbon::now()->toDateTimeString();
            
            // Update cache with new offline start time
            if ($cache) {
                $cache['offline_start_at'] = $now;
                $this->saveEncryptedLicenseCache($cache);
            }
            
            // Always update persistent timer
            $this->setPersistentOfflineTimestamp($now);
            Log::info('🛰️ Fresh offline session start recorded at ' . $now);
        } else {
            Log::info('ℹ️ Offline session already tracking from ' . $fileTime);
        }
    }

    protected function checkOfflineCacheValidity($cache = null)
    {
        try {
            $cache = $cache ?? $this->readEncryptedLicenseCache();
            if (!$cache) {
                Log::warning('⚠️ No encrypted cache found.');
                return false;
            }

            $validMinutes = $cache['cache_validity_minutes'] ?? 5;
            $expiry = isset($cache['expiry_date']) ? Carbon::parse($cache['expiry_date']) : null;

            // 🎯 FIX: Always use persistent timestamp for accurate timing
            $offlineStart = $this->getPersistentOfflineTimestamp()
                ? Carbon::parse($this->getPersistentOfflineTimestamp())
                : Carbon::now();

            $cacheValidUntil = $offlineStart->copy()->addMinutes($validMinutes);

            // 🩵 FIX: 30s safety buffer to stop instant disconnect
            $safetyUntil = $offlineStart->copy()->addSeconds(30);
            if (Carbon::now()->lt($safetyUntil)) {
                Log::info('🕐 Safety buffer active — blocking early disconnect.');
                return true;
            }

            if ($expiry && Carbon::now()->gt($expiry)) {
                Log::warning('⚠️ License expired by API date.');
                return false;
            }

            if (Carbon::now()->lte($cacheValidUntil)) {
                $remain = Carbon::now()->diffInSeconds($cacheValidUntil);
                Log::info("✅ Offline valid — {$remain} seconds remaining.");
                return true;
            }

            Log::warning('❌ Offline grace expired.');
            return false;
        } catch (\Throwable $e) {
            Log::error('Offline check error: ' . $e->getMessage());
            return false;
        }
    }
}