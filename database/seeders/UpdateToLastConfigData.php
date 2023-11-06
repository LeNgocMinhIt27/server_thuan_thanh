<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class UpdateToLastConfigData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //seeder này để gọi những hàm thêm dữ liệu vào những bảng không thay đổi hoặc cần init dữ liệu mà ko phải dữ liệu để test lần đầu tiên, luôn luôn gọi mỗi lần build docker
        $this->call(SettingSeeder::class);
        $this->call(KiHocSeeder::class);
    }
}
