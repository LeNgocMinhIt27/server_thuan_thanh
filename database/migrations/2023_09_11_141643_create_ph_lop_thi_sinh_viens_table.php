<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhLopThiSinhViensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_lop_thi_sinh_viens', function (Blueprint $table) {
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->foreignId('sinh_vien_id')->constrained('u_sinh_viens')->onDelete('cascade');
            $table->integer('stt')->nullable();
            $table->float('diem')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ph_lop_thi_sinh_viens');
    }
}
