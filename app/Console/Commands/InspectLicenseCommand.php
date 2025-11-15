<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InspectLicenseCommand extends Command
{
    protected $signature = 'license:inspect {--pass=}';
    protected $description = 'Decrypt and show local encrypted license cache (superadmin only)';

    public function handle()
    {
        if ($this->option('pass') !== 'synkode@pos') {
            $this->error('❌ Invalid password.');
            return 1;
        }

        $path = storage_path('framework/.license_cache/license_status.enc');
        if (!File::exists($path)) {
            $this->error('No encrypted cache found.');
            return 2;
        }

        try {
            $data = json_decode(decrypt(File::get($path)), true);
            $this->info('🔐 License Cache Contents:');
            print_r($data);
        } catch (\Throwable $e) {
            $this->error('Failed to decrypt: '.$e->getMessage());
            return 3;
        }

        return 0;
    }
}
