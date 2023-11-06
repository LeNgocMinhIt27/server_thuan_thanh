<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDBangDiemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_bang_diems', function (Blueprint $table) {
            $table->id();
            $table->string('ma_hp');
            $table->string('ten_hp');
            $table->text('ghi_chu')->nullable();
            $table->string('ki_hoc');
            $table->string('duong_dan_tap_tin');
            $table->string('trang_thai_nhan_dien')->nullable();
            $table->date('ngay_cong_khai')->nullable();
            $table->date('ngay_ket_thuc_phuc_khao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_bang_diems');
    }
}
