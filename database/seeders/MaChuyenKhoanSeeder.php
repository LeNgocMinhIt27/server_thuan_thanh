<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaChuyenKhoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $so_phan_tu = 1000000;
        $phan_tu_cat = 50000;

        for ($i = 0; $i < $so_phan_tu; $i += $phan_tu_cat) {
            $chunk = [];
            for ($j = $i; $j < min($i + $phan_tu_cat, $so_phan_tu); $j++) {
                $randomValue = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $chunk[] = ['ma' => $randomValue];
            }
            DB::table('pk_ma_thanh_toans')->insert($chunk);
        }
    }
}
