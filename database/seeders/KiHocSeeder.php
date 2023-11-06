<?php

namespace Database\Seeders;

use App\Models\KiHoc;
use Illuminate\Database\Seeder;

class KiHocSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kis = ['20233', '20232', '20231', '20223'];
        foreach ($kis as $ki) {
            KiHoc::updateOrCreate(['name' => $ki]);
        }
    }
}
