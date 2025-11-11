<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
    Version235Seeder,
};

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $newVersionArray = [
            '1.0',  //1.0, Date: 24-10-2024
            '1.1',  //1.0, Date: 28-11-2024
            '1.1.1',  //1.0, Date: 30-11-2024
            '1.2',  //1.2, Date: 07-12-2024
            '1.3',  //1.3, Date: 17-12-2024
            '1.3.1',  //1.3.1, Date: 22-12-2024
            '1.3.2',  //1.3.2, Date: 24-12-2024
            '1.3.3',  //1.3.3, Date: 28-12-2024
            '1.3.4',  //1.3.4, Date: 31-12-2024
            '1.4',  //1.4, Date: 01-01-2025
            '1.4.1',  //1.4.1, Date: 09-01-2025
            '1.4.2',  //1.4.2, Date: 13-01-2025
            '1.4.3',  //1.4.3, Date: 14-01-2025
            '1.4.4',  //1.4.4, Date: 18-01-2025
            '1.4.5',  //1.4.5, Date: 18-01-2025
            '1.4.6',  //1.4.6, Date: 31-01-2025
            '1.4.7',  //1.4.7, Date: 31-01-2025
            '1.4.8',  //1.4.8, Date: 03-02-2025
            '1.4.9',  //1.4.9, Date: 12-02-2025
            '1.5',  //1.5, Date: 14-02-2025
            '2.0',  //2.0, Date: 21-02-2025
            '2.1',  //2.1, Date: 25-02-2025
            '2.2',  //2.2, Date: 06-03-2025
            '2.3',  //2.3, Date: 08-03-2025
            '2.3.1',  //2.3.1, Date: 08-03-2025
            '2.3.2',  //2.3.2, Date: 08-03-2025
            '2.3.3',  //2.3.3, Date: 08-03-2025
            '2.3.4',  //2.3.4, Date: 08-03-2025
            '2.3.5',  //2.3.5, Date: 07-05-2025
            '2.3.6',  //2.3.6, Date: 26-05-2025
            '2.4',  //2.4, Date: 03-07-2025
            env('APP_VERSION'),  //2.5, Date: 03-08-2025
        ];

        $existingVersions = DB::table('versions')->pluck('version')->toArray();

        foreach ($newVersionArray as $version) {
            //validate is the version exist in it?
            if (!in_array($version, $existingVersions)) {
                DB::table('versions')->insert([
                    'version' => $version,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /**
                 * Version wise any seeder updates
                 * */
                $this->updateDatabaseTransaction($version);
            }
        }
    }

    public function updateDatabaseTransaction($version)
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
            '2.3.5' => Version235Seeder::class,

        ];

        if (isset($seeders[$version])) {
            $seeder = new $seeders[$version]();
            $seeder->run();
        }
    }
}