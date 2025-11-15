<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Database\Seeders\Updates\{
    Version132Seeder,
    Version133Seeder,
    Version141Seeder,
    Version142Seeder,
    Version144Seeder,
    Version145Seeder,
    Version147Seeder,
    Version148Seeder,
    Version21Seeder,
    Version23Seeder,
    Version231Seeder,
    Version232Seeder,
    Version233Seeder,
    Version235Seeder
};

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $newVersionArray = [
                '1.0', '1.1', '1.1.1',
                '1.2', '1.3', '1.3.1', '1.3.2', '1.3.3', '1.3.4',
                '1.4', '1.4.1', '1.4.2', '1.4.3', '1.4.4', '1.4.5',
                '1.4.6', '1.4.7', '1.4.8', '1.4.9',
                '1.5', '2.0', '2.1', '2.2',
                '2.3', '2.3.1', '2.3.2', '2.3.3', '2.3.4', '2.3.5', '2.3.6', '2.4',
            ];

            // include current app version dynamically if missing
            $currentVersion = env('APP_VERSION');
            if ($currentVersion && !in_array($currentVersion, $newVersionArray)) {
                $newVersionArray[] = $currentVersion;
            }

            $existingVersions = DB::table('versions')->pluck('version')->toArray();

            foreach ($newVersionArray as $version) {
                if (!in_array($version, $existingVersions)) {
                    DB::table('versions')->insert([
                        'version' => $version,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->runVersionSeeder($version);
                }
            }
        } catch (\Throwable $e) {
            Log::error('VersionSeeder failed: ' . $e->getMessage());
        }
    }

    /**
     * Runs the specific version seeder safely.
     */
    protected function runVersionSeeder(string $version): void
    {
        $seeders = [
            '1.3.2' => Version132Seeder::class,
            '1.3.3' => Version133Seeder::class,
            '1.4.1' => Version141Seeder::class,
            '1.4.2' => Version142Seeder::class,
            '1.4.4' => Version144Seeder::class,
            '1.4.5' => Version145Seeder::class,
            '1.4.7' => Version147Seeder::class,
            '1.4.8' => Version148Seeder::class,
            '2.1'   => Version21Seeder::class,
            '2.3'   => Version23Seeder::class,
            '2.3.1' => Version231Seeder::class,
            '2.3.2' => Version232Seeder::class,
            '2.3.3' => Version233Seeder::class,
            '2.3.5' => Version235Seeder::class,
        ];

        if (isset($seeders[$version])) {
            try {
                $seeder = new $seeders[$version]();
                $seeder->run();
            } catch (\Throwable $e) {
                Log::warning("Version seeder {$version} failed: " . $e->getMessage());
            }
        }
    }
}
