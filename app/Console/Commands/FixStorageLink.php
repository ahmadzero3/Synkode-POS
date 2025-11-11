<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageLink extends Command
{
    protected $signature = 'storage:fix-link';
    protected $description = 'Automatically ensure that public/storage is linked to storage/app/public';

    public function handle()
    {
        $publicStorage = public_path('storage');
        $target = storage_path('app/public');

        // If link doesn't exist or is a broken directory, re-create it
        if (!File::exists($publicStorage) || !is_link($publicStorage)) {
            try {
                if (File::exists($publicStorage)) {
                    File::deleteDirectory($publicStorage);
                }
                $this->laravel->make('files')->link($target, $publicStorage);
                $this->info('✅ Storage link created successfully.');
            } catch (\Exception $e) {
                $this->error('❌ Failed to create storage link: ' . $e->getMessage());
            }
        } else {
            $this->info('✅ Storage link already exists.');
        }

        return Command::SUCCESS;
    }
}
