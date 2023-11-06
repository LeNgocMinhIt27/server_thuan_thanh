<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePkPhucKhaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pk_phuc_khaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('u_sinh_viens')->onDelete('cascade');
            $table->string('ki_hoc');
            $table->foreignId('lop_id')->constrained('ph_lops')->onDelete('cascade');
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->string('trang_thai')->nullable();
            $table->string('ma_thanh_toan')->foreign('ma_thanh_toan')->references('ma')->on('pk_ma_thanh_toans')->onDelete('cascade');
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
        Schema::dropIfExists('pk_phuc_khaos');
    }
}
