<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNgayDongDiemDanhChoPHLanDiemDanhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_lan_diem_danhs', function (Blueprint $table) {
            $table->date('ngay_dong_diem_danh')->nullable();
            $table->date('ngay_mo_diem_danh')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_lan_diem_danhs', function (Blueprint $table) {
            $table->dropColumn('ngay_dong_diem_danh');
            $table->dropColumn('ngay_mo_diem_danh');
        });
    }
}
