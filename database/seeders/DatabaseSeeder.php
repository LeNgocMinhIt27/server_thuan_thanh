<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AccessTableSeeder::class);
        $this->call(UpdateToLastConfigData::class);
        $this->call(MaChuyenKhoanSeeder::class);
    }
}
