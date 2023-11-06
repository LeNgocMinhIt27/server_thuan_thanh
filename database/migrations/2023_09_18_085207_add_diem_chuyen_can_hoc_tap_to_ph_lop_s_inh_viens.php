<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiemChuyenCanHocTapToPhLopSInhViens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_lop_sinh_viens', function (Blueprint $table) {
            $table->float('diem')->nullable()->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_lop_sinh_viens', function (Blueprint $table) {
            $table->dropColumn('diem');
        });
    }
}
