<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Helpers\HustHelper;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $ki_hien_tai = HustHelper::getKiHoc($now);
        Setting::firstOrCreate(
            [
                'section_name' => 'config',
                'setting_name' => 'day_start_week_1',
            ],
            [
                'setting_value' => '2023-09-04',
                'setting_type' => 'date',
            ]
        );
        Setting::firstOrCreate(
            [
                'section_name' => 'config',
                'setting_name' => 'day_start_week_1_ki_hoc',
            ],
            [
                'setting_value' => '2023-09-04',
                'setting_type' => 'date',
            ]
        );
        Setting::firstOrCreate(
            [
                'section_name' => 'config',
                'setting_name' => 'so_lan_diem_danh_toi_da',
            ],
            [
                'setting_value' => '4',
                'setting_type' => 'number',
            ]
        );
        Setting::firstOrCreate(
            [
                'section_name' => 'config',
                'setting_name' => 'ki_hoc',
            ],
            [
                'setting_value' => $ki_hien_tai,
                'setting_type' => 'string',
            ]
        );
        // Setting::firstOrCreate(
        //     [
        //         'section_name' => 'tuan_diem_danh',
        //         'setting_name' => '1',
        //     ],
        //     [
        //         'setting_value' => [3, 5],
        //         'setting_type' => 'json',
        //     ]
        // );
        // Setting::firstOrCreate(
        //     [
        //         'section_name' => 'tuan_diem_danh',
        //         'setting_name' => '2',
        //     ],
        //     [
        //         'setting_value' => [6, 9],
        //         'setting_type' => 'json',
        //     ]
        // );
        // Setting::firstOrCreate(
        //     [
        //         'section_name' => 'tuan_diem_danh',
        //         'setting_name' => '3',
        //     ],
        //     [
        //         'setting_value' => [10, 12],
        //         'setting_type' => 'json',
        //     ]
        // );
        // Setting::firstOrCreate(
        //     [
        //         'section_name' => 'tuan_diem_danh',
        //         'setting_name' => '4',
        //     ],
        //     [
        //         'setting_value' => [13, 15],
        //         'setting_type' => 'json',
        //     ]
        // );
    }
}
