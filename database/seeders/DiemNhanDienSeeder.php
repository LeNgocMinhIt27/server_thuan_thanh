<?php

namespace Database\Seeders;

use App\Models\Diem\DiemNhanDien;
use Illuminate\Database\Seeder;

class DiemNhanDienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $diems = [
            [
                "bang_diem_id" => "5",
                "page" => "1",
                "mssv" => "20223454",
                "stt" => "1",
                "diem" => "5",
            ],
            [
                "bang_diem_id" => "5",
                "page" => "1",
                "mssv" => "20214980",
                "stt" => "2",
                "diem" => "5",
            ],
            [
                "bang_diem_id" => "5",
                "page" => "2",
                "mssv" => "20222764",
                "stt" => "3",
                "diem" => "5",
            ],
            [
                "bang_diem_id" => "5",
                "page" => "2",
                "mssv" => "20222727",
                "stt" => "4",
                "diem" => "5",
            ],
            [
                "bang_diem_id" => "5",
                "page" => "3",
                "mssv" => "20195757",
                "stt" => "5",
                "diem" => "5",
            ]
        ];
        foreach ($diems as $diem) {
            DiemNhanDien::insert($diem);
        }
    }
}
