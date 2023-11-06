<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiemNhanDienLopThisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_diem_nhan_dien_lop_this', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->foreignId('bang_diem_id')->constrained('d_bang_diems')->onDelete('cascade');
            $table->text('duong_dan_anh')->nullable();
            $table->text('page')->nullable();
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
        Schema::dropIfExists('d_diem_nhan_dien_lop_this');
    }
}
